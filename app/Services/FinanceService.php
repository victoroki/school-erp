<?php

namespace App\Services;

use App\Models\StudentFeeAssignment;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\FeeStructure;
use App\Models\Term;
use Illuminate\Support\Facades\DB;
use Exception;

class FinanceService
{
    /**
     * Assign a fee structure to a student
     */
    public function assignFeeToStudent(int $studentId, int $feeStructureId, float $discountAmount = 0, ?int $academicYearId = null, ?string $term = null)
    {
        $feeStructure = FeeStructure::findOrFail($feeStructureId);

        $yearId = $academicYearId ?? $feeStructure->academic_year_id;
        $termValue = $term ?? $feeStructure->term;
        $termId = $termValue
            ? Term::where('academic_year_id', $yearId)->where('code', $termValue)->value('id')
            : null;
        $finalAmount = max(0, $feeStructure->amount - $discountAmount);

        return StudentFeeAssignment::create([
            'student_id' => $studentId,
            'fee_structure_id' => $feeStructureId,
            'academic_year_id' => $yearId,
            'term' => $termValue,
            'term_id' => $termId,
            'amount' => $feeStructure->amount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
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
        // Idempotency: an identical client_reference must never produce a second
        // payment. The web collection form has no disable-on-submit guard, so a
        // double-click previously created two payments and two receipts.
        if (! empty($data['client_reference'])) {
            $existing = FeePayment::where('client_reference', $data['client_reference'])->first();

            if ($existing) {
                return $existing;
            }
        }

        DB::beginTransaction();
        try {
            $assignment = StudentFeeAssignment::findOrFail($data['student_fee_assignment_id']);
            
            $payment = FeePayment::create([
                'student_fee_assignment_id' => $data['student_fee_assignment_id'],
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'] ?? now(),
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? null,
                'client_reference' => $data['client_reference'] ?? null,
                'receipt_number' => $this->generateReceiptNumber(),
                'remarks' => $data['remarks'] ?? null,
                'collected_by' => auth()->user()?->staff?->staff_id,
            ]);

            // Allocate the full payment to the single assignment and post ledger entries.
            app(\App\Services\LedgerService::class)->allocatePayment(
                $payment,
                [['id' => $assignment->id, 'amount' => (float) $data['amount']]],
                $data['allocation_strategy'] ?? 'manual'
            );

            $this->updateAssignmentPaymentStatus($assignment);

            DB::commit();
            return $payment;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Record a payment against a student and split it across one or more
     * assignments using the recommended (oldest-first by default) allocation.
     * Used by the "pay total balance" flow.
     */
    public function recordTotalPayment(array $data, $assignments)
    {
        if (! empty($data['client_reference'])) {
            $existing = FeePayment::where('client_reference', $data['client_reference'])->first();

            if ($existing) {
                return $existing;
            }
        }

        DB::beginTransaction();
        try {
            // Recommend allocation across the provided outstanding assignments.
            $recommended = app(\App\Services\LedgerService::class)
                ->recommendAllocation($assignments, (float) $data['amount'], $data['allocation_strategy'] ?? 'oldest_first');

            if (empty($recommended)) {
                throw new Exception('There is no outstanding balance to allocate this payment against.');
            }

            $payment = FeePayment::create([
                'student_fee_assignment_id' => $recommended[0]['id'],
                'amount' => (float) $data['amount'],
                'payment_date' => $data['payment_date'] ?? now(),
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? null,
                'client_reference' => $data['client_reference'] ?? null,
                'receipt_number' => $this->generateReceiptNumber(),
                'remarks' => ($data['remarks'] ?? null) . ' (Part of total payment)',
                'collected_by' => auth()->user()?->staff?->staff_id,
            ]);

            app(\App\Services\LedgerService::class)->allocatePayment(
                $payment,
                $recommended,
                $data['allocation_strategy'] ?? 'oldest_first'
            );

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
        // Summing $assignment->payments() included reversed payments. Route the
        // figure through the shared balance service instead.
        return app(\App\Services\FeeBalanceService::class)->recomputeAssignment($assignment);
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
        $balances = app(\App\Services\FeeBalanceService::class);

        $activeAssignmentIds = StudentFeeAssignment::where('status', 'active')->pluck('id')->all();

        // Money paid back out to parents/students. Only completed refunds have
        // actually left the school; requested/approved ones are still pending.
        $totalRefunded = (float) \App\Models\Refund::where('status', 'completed')->sum('amount');

        return [
            'total_receivable' => $balances->totalAssigned(),
            // Reversed payments no longer inflate "collected".
            'total_collected' => $balances->totalCollected(),
            'total_pending' => $balances->outstandingForAssignments($activeAssignmentIds),
            'collection_rate' => $this->getCollectionRate(),
            'total_refunded' => round($totalRefunded, 2),
        ];
    }

    private function getCollectionRate()
    {
        return app(\App\Services\FeeBalanceService::class)->collectionRate();
    }
}
