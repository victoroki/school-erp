<?php

namespace App\Services;

use App\Models\StudentFee;
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
    public function assignFeeToStudent($studentId, $feeStructureId, $discountAmount = 0)
    {
        $feeStructure = FeeStructure::findOrFail($feeStructureId);
        
        return StudentFee::create([
            'student_id' => $studentId,
            'fee_structure_id' => $feeStructureId,
            'amount' => $feeStructure->amount,
            'discount_amount' => $discountAmount,
            'final_amount' => $feeStructure->amount - $discountAmount,
            'due_date' => $feeStructure->due_date,
            'status' => 'unpaid'
        ]);
    }

    /**
     * Batch assign fee structure to all students in a class
     */
    public function batchAssignFee($feeStructureId, $classId)
    {
        $feeStructure = FeeStructure::findOrFail($feeStructureId);
        $students = Student::whereHas('studentClassEnrollments.classSection.schoolClass', function($q) use ($classId) {
            $q->where('class_id', $classId);
        })->get();

        $count = 0;
        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                // Check if already assigned
                $exists = StudentFee::where('student_id', $student->student_id)
                    ->where('fee_structure_id', $feeStructureId)
                    ->exists();

                if (!$exists) {
                    $this->assignFeeToStudent($student->student_id, $feeStructureId);
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
            $studentFee = StudentFee::findOrFail($data['student_fee_id']);
            
            $payment = FeePayment::create([
                'student_fee_id' => $data['student_fee_id'],
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'] ?? now(),
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? null,
                'receipt_number' => $this->generateReceiptNumber(),
                'remarks' => $data['remarks'] ?? null,
                'collected_by' => auth()->id(),
            ]);

            $this->updateStudentFeeStatus($studentFee);

            DB::commit();
            return $payment;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update Student Fee status based on paid amount
     */
    public function updateStudentFeeStatus(StudentFee $studentFee)
    {
        $paidAmount = $studentFee->payments()->sum('amount');
        
        if ($paidAmount >= $studentFee->final_amount) {
            $studentFee->status = 'paid';
        } elseif ($paidAmount > 0) {
            $studentFee->status = 'partially_paid';
        } else {
            $studentFee->status = 'unpaid';
        }

        $studentFee->save();
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
            'total_receivable' => StudentFee::sum('final_amount'),
            'total_collected' => FeePayment::sum('amount'),
            'total_pending' => StudentFee::where('status', '!=', 'paid')->get()->sum('balance'),
            'collection_rate' => $this->getCollectionRate()
        ];
    }

    private function getCollectionRate()
    {
        $receivable = StudentFee::sum('final_amount');
        if ($receivable == 0) return 0;
        $collected = FeePayment::sum('amount');
        return round(($collected / $receivable) * 100, 2);
    }
}
