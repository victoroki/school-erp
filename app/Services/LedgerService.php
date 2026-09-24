<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\FeePayment;
use App\Models\PaymentAllocation;
use App\Models\StudentFeeAssignment;
use App\Models\Refund;
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
            $created = [];
            $touched = [];

            foreach ($allocations as $alloc) {
                $assignment = StudentFeeAssignment::findOrFail($alloc['id']);
                $amount = round((float) $alloc['amount'], 2);

                if ($amount <= 0) {
                    continue;
                }

                $touched[$assignment->id] = true;

                $created[] = PaymentAllocation::create([
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

            // Refresh paid_amount from the single source of truth. Summing
            // payment_allocations directly (as this used to) re-counted money
            // that had already been reversed, because reversed allocations are
            // left in place for audit.
            $balances = app(FeeBalanceService::class);

            foreach (array_keys($touched) as $assignmentId) {
                $balances->recomputeAssignment($assignmentId);
            }

            return $created;
        });
    }

    /**
     * Reverse a payment: mark its allocations reversed via contra ledger entries
     * and restore the affected assignments' paid_amount by excluding the payment.
     */
    public function reversePayment(FeePayment $payment, string $reason, ?int $byUserId = null): void
    {
        if ($payment->isReversed()) {
            throw new Exception('This payment has already been reversed.');
        }

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

            // Mark the payment itself as reversed. Until this flag existed the
            // reversal was invisible to every SUM(fee_payments.amount) figure,
            // to the receipt, and to the receipt register.
            $payment->update([
                'reversed_at' => now(),
                'reversal_reason' => $reason,
                'reversed_by' => $user,
            ]);

            // Rebuild paid_amount from the single source of truth.
            $balances = app(FeeBalanceService::class);

            $assignmentIds = PaymentAllocation::where('payment_id', $payment->payment_id)
                ->pluck('student_fee_assignment_id');

            if ($payment->student_fee_assignment_id) {
                $assignmentIds->push($payment->student_fee_assignment_id);
            }

            foreach ($assignmentIds->unique() as $assignmentId) {
                $balances->recomputeAssignment((int) $assignmentId);
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
     * Post a completed refund to the student's ledger as a DEBIT.
     *
     * A refund is cash leaving the school to hand back money the student had
     * already paid, so afterwards the student owes that amount again: their
     * account moves the same way a charge moves, not the way a payment moves.
     * Posting it as a credit said the opposite — that giving money back settles
     * a fee — and it disagreed with every balance in the application, none of
     * which counted refunds at all. The disagreement stayed invisible only
     * because the statement derives its opening line from the balance service,
     * which silently absorbed it.
     *
     * Historical entries posted the old way are NOT rewritten. They are detected
     * as legacy by FeeIntegrityService and raised for administrative review,
     * because re-pointing a posted movement is an accounting decision, not a
     * code change.
     *
     * The charge the money came out of is resolved exactly as FeeBalanceService
     * attributes it: the refund's own assignment, else the assignment of the
     * payment it refunds.
     */
    public function postRefund(Refund $refund): LedgerEntry
    {
        $assignmentId = $refund->student_fee_assignment_id
            ?: $refund->payment?->student_fee_assignment_id;

        return $this->addEntry([
            'student_id' => $refund->student_id,
            'student_fee_assignment_id' => $assignmentId,
            'entry_date' => now(),
            'description' => 'Refund #' . $refund->id . ': ' . $refund->reason,
            'entry_type' => 'refund',
            'debit' => (float) $refund->amount,
            'credit' => 0,
            'reference_type' => Refund::class,
            'reference_id' => $refund->id,
            'source' => 'refund',
        ]);
    }

    /**
     * The chronological statement (ledger with running balance) for a student.
     *
     * THE OPENING LINE IS DERIVED, NOT STORED. It is computed as
     *
     *     opening = FeeBalanceService::balanceForStudent() − net(ledger movements)
     *
     * and the closing figure is therefore, by construction, exactly the balance
     * every other screen shows. That is the whole point: the statement used to
     * build its closing figure purely from ledger_entries, while the profile,
     * arrears, dashboards and reports built theirs from assignments and
     * payments. Because the ledger is only written by payments, adjustments and
     * refunds — and never by a charge — a student whose fees predate the ledger
     * had statements that disagreed with their own balance, sometimes by the
     * entire amount billed.
     *
     * The opening line is what the statement honestly cannot itemise: fees
     * charged before the ledger existed. It is labelled "carried forward" and
     * deliberately NOT persisted, so no historical transaction is invented and
     * nothing has to be backfilled. Movements written from now on (payments,
     * charge postings, adjustments, refunds, reversals) appear as real lines
     * beneath it.
     *
     * Legacy `opening_balance`/`bootstrap` rows from the old lazy seeding are
     * folded into the derived opening rather than listed, so a student can never
     * carry the same position twice.
     *
     * $from narrows which movements are listed. The closing figure remains the
     * student's current balance either way, and the opening line adjusts to the
     * window so the arithmetic still adds up.
     */
    public function getStudentStatement(int $studentId, ?string $from = null): array
    {
        $entries = LedgerEntry::where('student_id', $studentId)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        // A bootstrap row is a carried-forward balancing figure, not a movement.
        $movements = $entries
            ->reject(fn ($entry) => $entry->entry_type === 'opening_balance' && $entry->source === 'bootstrap')
            ->values();

        $net = fn ($rows) => round($rows->reduce(
            fn ($carry, $entry) => $carry + (float) $entry->debit - (float) $entry->credit,
            0.0
        ), 2);

        $balanceNow = app(FeeBalanceService::class)->balanceForStudent($studentId);
        $openingBeforeLedger = round($balanceNow - $net($movements), 2);

        if ($from) {
            $cutoff = \Carbon\Carbon::parse($from);
            $shown = $movements->filter(fn ($entry) => $entry->entry_date && $entry->entry_date->gte($cutoff))->values();
            $earlier = $movements->filter(fn ($entry) => ! $entry->entry_date || $entry->entry_date->lt($cutoff))->values();
        } else {
            $shown = $movements;
            $earlier = $movements->take(0);
        }

        $openBalance = round($openingBeforeLedger + $net($earlier), 2);

        // Rebuild the running balance from the opening line.
        $running = $openBalance;
        foreach ($shown as $entry) {
            $running = round($running + (float) $entry->debit - (float) $entry->credit, 2);
            $entry->balance_after = $running;
        }

        $closing = $running;
        $totalCharges = (float) $shown->sum('debit');
        $totalCredits = (float) $shown->sum('credit');

        return compact('openBalance', 'closing', 'totalCharges', 'totalCredits', 'from')
            + ['entries' => $shown, 'balanceNow' => $balanceNow];
    }
}
