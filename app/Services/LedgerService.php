<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\FeePayment;
use App\Models\PaymentAllocation;
use App\Models\StudentFeeAssignment;
use App\Models\Refund;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * LedgerService is the financial source of truth layer for the Fee module.
 *
 * It implements the balance model described in the Fee Management requirements:
 *     Closing = Opening + Charges + Debit Adjustments - Payments - Credits/Waivers
 *
 * All money movements flow through append-only ledger entries. Running balances
 * are recomputed per entry so a student's balance is always explainable.
 */
class LedgerService
{
    /**
     * Recompute the running balance for every ledger entry of a student.
     * Returns the closing balance.
     */
    public function recomputeStudentBalance(int $studentId): float
    {
        $entries = LedgerEntry::where('student_id', $studentId)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $running = 0;
        foreach ($entries as $entry) {
            $running = round($running + (float) $entry->debit - (float) $entry->credit, 2);
            if (abs($running - (float) $entry->balance_after) > 0.009) {
                $entry->update(['balance_after' => $running]);
            } else {
                $entry->balance_after = $running;
            }
        }

        return $running;
    }

    /**
     * Append a single ledger entry and set its balance_after from the last entry.
     * Returns the created LedgerEntry.
     */
    public function addEntry(array $data): LedgerEntry
    {
        return DB::transaction(function () use ($data) {
            $studentId = $data['student_id'];
            $debit = (float) ($data['debit'] ?? 0);
            $credit = (float) ($data['credit'] ?? 0);

            // Determine starting balance from the most recent entry for this student.
            $last = LedgerEntry::where('student_id', $studentId)
                ->orderBy('entry_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $starting = $last ? (float) $last->balance_after : 0;
            $balanceAfter = round($starting + $debit - $credit, 2);

            return LedgerEntry::create(array_merge($data, [
                'balance_after' => $balanceAfter,
                'created_by' => $data['created_by'] ?? auth()->id(),
                'source' => $data['source'] ?? 'manual',
            ]));
        });
    }

    /**
     * Reverse (contra) a ledger entry by posting an opposite entry linked via
     * reverses_entry_id. The original is never deleted.
     */
    public function reverseEntry(LedgerEntry $entry, string $reason, ?int $byUserId = null): LedgerEntry
    {
        return $this->addEntry([
            'student_id' => $entry->student_id,
            'academic_year_id' => $entry->academic_year_id,
            'term_id' => $entry->term_id,
            'student_fee_assignment_id' => $entry->student_fee_assignment_id,
            'entry_date' => now(),
            'description' => 'Reversal: ' . $reason,
            'entry_type' => 'reversal',
            'debit' => (float) $entry->credit,
            'credit' => (float) $entry->debit,
            'reference_type' => $entry->reference_type,
            'reference_id' => $entry->reference_id,
            'reverses_entry_id' => $entry->id,
            'created_by' => $byUserId ?? auth()->id(),
            'source' => 'reversal',
        ]);
    }

    /**
     * Post one (or more) charges to a student's ledger. $amount is a debit.
     */
    public function postCharge(StudentFeeAssignment $assignment, float $amount, ?string $description = null): LedgerEntry
    {
        return $this->addEntry([
            'student_id' => $assignment->student_id,
            'academic_year_id' => $assignment->academic_year_id,
            'term_id' => $assignment->term_id,
            'student_fee_assignment_id' => $assignment->id,
            'entry_date' => $assignment->assigned_date ?? now(),
            'description' => $description ?? ($assignment->feeStructure?->category?->name ?? 'Fee charge'),
            'entry_type' => 'charge',
            'debit' => $amount,
            'credit' => 0,
            'reference_type' => StudentFeeAssignment::class,
            'reference_id' => $assignment->id,
            'source' => 'billing',
        ]);
    }

    /**
     * Compute how a payment amount should be allocated among outstanding
     * assignments for a student using "oldest first" by default.
     *
     * @return array<int, array{id:int, amount:float}> list of [assignment_id, amount] pairs
     */
    public function recommendAllocation($outstandingAssignments, float $amount, string $strategy = 'oldest_first'): array
    {
        $result = [];
        $remaining = $amount;

        // Ensure we have a plain array (Collection won't work with usort)
        $outstandingAssignments = $outstandingAssignments instanceof \Illuminate\Support\Collection
            ? $outstandingAssignments->all()
            : (array) $outstandingAssignments;

        usort($outstandingAssignments, function ($a, $b) {
            // Oldest assigned first
            return strcmp($a->assigned_date ?? $a->created_at, $b->assigned_date ?? $b->created_at);
        });

        foreach ($outstandingAssignments as $assignment) {
            if ($remaining <= 0) {
                break;
            }
            $balance = (float) $assignment->final_amount - (float) $assignment->paid_amount;
            if ($balance <= 0) {
                continue;
            }
            $take = min($remaining, $balance);
            $result[] = ['id' => $assignment->id, 'amount' => round($take, 2)];
            $remaining -= $take;
        }

        return $result;
    }

    /**
     * Allocate a recorded payment against one or more assignments.
     * Updates each assignment's paid_amount, writes payment_allocations rows,
     * and posts a single (or per-charge) credit ledger entry.
     */
    public function allocatePayment(FeePayment $payment, array $allocations, string $strategy = 'manual'): array
    {
        return DB::transaction(function () use ($payment, $allocations, $strategy) {
            $user = auth()->id();

            foreach ($allocations as $alloc) {
                $assignment = StudentFeeAssignment::findOrFail($alloc['id']);
                $amount = (float) $alloc['amount'];

                PaymentAllocation::create([
                    'payment_id' => $payment->payment_id,
                    'student_fee_assignment_id' => $assignment->id,
                    'amount' => $amount,
                    'allocation_strategy' => $strategy,
                    'created_by' => $user,
                    'allocated_at' => now(),
                ]);

                $this->addEntry([
                    'student_id' => $assignment->student_id,
                    'academic_year_id' => $assignment->academic_year_id,
                    'term_id' => $assignment->term_id,
                    'student_fee_assignment_id' => $assignment->id,
                    'entry_date' => $payment->payment_date ?? now(),
                    'description' => 'Payment ' . $payment->receipt_number . ' - ' . ($assignment->feeStructure?->category?->name ?? 'Fee'),
                    'entry_type' => 'payment',
                    'debit' => 0,
                    'credit' => $amount,
                    'reference_type' => FeePayment::class,
                    'reference_id' => $payment->payment_id,
                    'source' => 'payment',
                ]);
            }

            // Refresh paid_amount on all touched assignments.
            $assignmentIds = array_column($allocations, 'id');
            foreach (StudentFeeAssignment::whereIn('id', $assignmentIds)->get() as $assignment) {
                $paid = PaymentAllocation::where('student_fee_assignment_id', $assignment->id)->sum('amount');
                $assignment->update(['paid_amount' => $paid]);
            }

            return $assignments ?? [];
        });
    }

    /**
     * Reverse a payment: mark its allocations reversed via contra ledger entries
     * and restore the affected assignments' paid_amount by excluding the payment.
     */
    public function reversePayment(FeePayment $payment, string $reason, ?int $byUserId = null): void
    {
        DB::transaction(function () use ($payment, $reason, $byUserId) {
            $user = $byUserId ?? auth()->id();

            // Reverse each ledger entry created for this payment.
            $entries = LedgerEntry::where('reference_type', FeePayment::class)
                ->where('reference_id', $payment->payment_id)
                ->where('entry_type', 'payment')
                ->get();

            foreach ($entries as $entry) {
                $this->reverseEntry($entry, $reason, $user);
            }

            // Recompute paid_amount for affected assignments (exclude this payment).
            $assignmentIds = PaymentAllocation::where('payment_id', $payment->payment_id)
                ->pluck('student_fee_assignment_id')
                ->unique();

            foreach (StudentFeeAssignment::whereIn('id', $assignmentIds)->get() as $assignment) {
                $paid = PaymentAllocation::where('student_fee_assignment_id', $assignment->id)
                    ->where('payment_id', '!=', $payment->payment_id)
                    ->sum('amount');
                $assignment->update(['paid_amount' => $paid]);
            }
        });
    }

    /**
     * Post a debit/credit adjustment for a student (from an approved fee adjustment).
     */
    public function postAdjustment(StudentFeeAssignment $assignment, float $delta, string $description, string $type = 'adjustment'): LedgerEntry
    {
        $isIncrease = $delta > 0;
        return $this->addEntry([
            'student_id' => $assignment->student_id,
            'academic_year_id' => $assignment->academic_year_id,
            'term_id' => $assignment->term_id,
            'student_fee_assignment_id' => $assignment->id,
            'entry_date' => now(),
            'description' => $description,
            'entry_type' => 'adjustment',
            'debit' => $isIncrease ? abs($delta) : 0,
            'credit' => $isIncrease ? 0 : abs($delta),
            'reference_type' => StudentFeeAssignment::class,
            'reference_id' => $assignment->id,
            'source' => $type,
        ]);
    }

    /**
     * Post a completed refund as a credit to the student's ledger.
     */
    public function postRefund(Refund $refund): LedgerEntry
    {
        return $this->addEntry([
            'student_id' => $refund->student_id,
            'student_fee_assignment_id' => $refund->student_fee_assignment_id,
            'entry_date' => now(),
            'description' => 'Refund #' . $refund->id . ': ' . $refund->reason,
            'entry_type' => 'refund',
            'debit' => 0,
            'credit' => (float) $refund->amount,
            'reference_type' => Refund::class,
            'reference_id' => $refund->id,
            'source' => 'refund',
        ]);
    }

    /**
     * Get the chronological statement (ledger with running balance) for a student.
     * Adds an opening-balance pseudo entry if a start date is supplied.
     */
    public function getStudentStatement(int $studentId, ?string $from = null): array
    {
        $openBalance = 0;
        $query = LedgerEntry::where('student_id', $studentId);

        if ($from) {
            $before = (clone $query)->where('entry_date', '<', $from)->get();
            $openBalance = (float) $before->reduce(fn ($c, $e) => $c + (float) $e->debit - (float) $e->credit, 0);
            $query->where('entry_date', '>=', $from);
        }

        $entries = (clone $query)->orderBy('entry_date')->orderBy('id')->get();

        // Rebuild running balance from the opening balance.
        $running = $openBalance;
        foreach ($entries as $entry) {
            $running = round($running + (float) $entry->debit - (float) $entry->credit, 2);
            $entry->balance_after = $running;
        }

        $closing = $running;
        $totalCharges = (float) $entries->sum('debit');
        $totalCredits = (float) $entries->sum('credit');

        return compact('openBalance', 'entries', 'closing', 'totalCharges', 'totalCredits', 'from');
    }

    /**
     * Seed an opening ledger entry for a student from existing historical data
     * (used to bootstrap the ledger without altering current balances).
     */
    public function seedOpeningBalance(int $studentId): void
    {
        if (LedgerEntry::where('student_id', $studentId)->exists()) {
            return;
        }

        $student = Student::with('feeAssignments')->find($studentId);
        if (!$student) {
            return;
        }

        $totalCharged = (float) $student->feeAssignments->sum('final_amount');
        $totalPaid = (float) $student->payments()->sum('fee_payments.amount');

        if ($totalCharged == 0 && $totalPaid == 0) {
            return;
        }

        $this->addEntry([
            'student_id' => $studentId,
            'entry_date' => now(),
            'description' => 'Opening balance (carried forward)',
            'entry_type' => 'opening_balance',
            'debit' => max(0, $totalCharged - $totalPaid),
            'credit' => 0,
            'source' => 'bootstrap',
        ]);
    }
}
