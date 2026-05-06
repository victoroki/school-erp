<?php

namespace App\Services;

use App\Models\StudentFeeAssignment;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\FeeStructure;
use Illuminate\Support\Facades\DB;
use Exception;

class FinanceService
{
    /**
     * Assign a fee structure to a student
     */
    public function assignFeeToStudent($studentId, $feeStructureId, $discountAmount = 0, $academicYearId = null, $term = null)
    {
        $feeStructure = FeeStructure::findOrFail($feeStructureId);

        return StudentFeeAssignment::create([
            'student_id' => $studentId,
            'fee_structure_id' => $feeStructureId,
            'academic_year_id' => $academicYearId ?? $feeStructure->academic_year_id,
            'term' => $term ?? $feeStructure->term,
            'amount' => $feeStructure->amount,
            'discount_amount' => $discountAmount,
            'final_amount' => $feeStructure->amount - $discountAmount,
            'assigned_by' => auth()->id(),
            'assigned_date' => now(),
            'status' => 'active'
        ]);
    }

    /**
     * Batch assign fee structure to all students in a class
     */
    public function batchAssignFee($feeStructureId, $classId, $academicYearId = null, $term = null)
    {
        $feeStructure = FeeStructure::findOrFail($feeStructureId);

        // Get all class_section_ids for this class
        $classSectionIds = \App\Models\ClassSection::where('class_id', $classId)->pluck('class_section_id');

        if ($classSectionIds->isEmpty()) {
            return 0;
        }

        // Get students enrolled in ANY section of this class
        $students = Student::whereHas('studentClassEnrollments', function($q) use ($classSectionIds) {
            $q->whereIn('class_section_id', $classSectionIds)
              ->where('is_current', true);
        })->get();

        if ($students->isEmpty()) {
            return 0;
        }

        $count = 0;
        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                // Check if already assigned
                $exists = StudentFeeAssignment::where('student_id', $student->student_id)
                    ->where('fee_structure_id', $feeStructureId)
                    ->exists();

                if (!$exists) {
                    $this->assignFeeToStudent($student->student_id, $feeStructureId, 0, $academicYearId, $term);
                    $count++;
                }
            }
            DB::commit();
            return $count;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Record a payment
     */
    public function recordPayment(array $data)
    {
        DB::beginTransaction();
        try {
            $assignment = StudentFeeAssignment::findOrFail($data['student_fee_assignment_id']);
            
            $payment = FeePayment::create([
                'student_fee_assignment_id' => $data['student_fee_assignment_id'],
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'] ?? now(),
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? null,
                'receipt_number' => $this->generateReceiptNumber(),
                'remarks' => $data['remarks'] ?? null,
                'collected_by' => auth()->id(),
            ]);

            $this->updateAssignmentPaymentStatus($assignment);

            DB::commit();
            return $payment;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update Student Fee Assignment payment status based on paid amount
     */
    public function updateAssignmentPaymentStatus(StudentFeeAssignment $assignment)
    {
        $paidAmount = $assignment->payments()->sum('amount');
        
        $assignment->update([
            'paid_amount' => $paidAmount,
        ]);
    }

    private function generateReceiptNumber()
    {
        return 'RCP-' . strtoupper(uniqid());
    }

    /**
     * Get Financial Metrics
     */
    public function getMetrics()
    {
        return [
            'total_receivable' => StudentFeeAssignment::where('status', 'active')->sum('final_amount'),
            'total_collected' => FeePayment::sum('amount'),
            'total_pending' => StudentFeeAssignment::where('status', 'active')
                ->whereRaw('COALESCE(paid_amount, 0) < final_amount')
                ->get()
                ->sum(function ($assignment) {
                    return $assignment->final_amount - $assignment->paid_amount;
                }),
            'collection_rate' => $this->getCollectionRate()
        ];
    }

    private function getCollectionRate()
    {
        $receivable = StudentFeeAssignment::where('status', 'active')->sum('final_amount');
        if ($receivable == 0) return 0;
        $collected = FeePayment::sum('amount');
        return round(($collected / $receivable) * 100, 2);
    }
}
