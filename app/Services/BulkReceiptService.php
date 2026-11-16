<?php

namespace App\Services;

use App\Models\AuditTrail;
use App\Models\FeeBulkReceipt;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Distributes a sponsor/bursary bulk receipt across students.
 *
 * Reuses the entire existing fee payment chain: each allocation is an
 * ordinary FeePayment (allocated to the student's outstanding assignments
 * oldest-first through LedgerService), so balances, statements, receipts and
 * reversals all keep working untouched. The receipt is the ONLY cash event —
 * allocating money to students must never post income or bank movements
 * again.
 *
 * Concurrency: allocation runs inside a transaction that locks the receipt
 * row, so two requests cannot both spend the same remaining balance.
 */
class BulkReceiptService
{
    public function __construct(private \App\Services\LedgerService $ledgerService)
    {
    }

    /**
     * Record a new bulk receipt. Optionally posts a single bank deposit when
     * the money was banked (finance-domain destination), exactly once.
     *
     * @param  array{sponsor_name:string,sponsor_type?:string,reference_number?:string|null,amount:float,payment_method:string,bank_account_id?:int|null,transaction_id?:string|null,received_date:string,academic_year_id?:int|null,term_id?:int|null,remarks?:string|null}  $data
     */
    public function createReceipt(array $data): FeeBulkReceipt
    {
        $amount = round((float) $data['amount'], 2);

        if ($amount <= 0) {
            throw new RuntimeException('The receipt amount must be greater than zero.');
        }

        $receipt = null;

        DB::transaction(function () use ($data, $amount, &$receipt) {
            $receipt = FeeBulkReceipt::create([
                'sponsor_name' => trim($data['sponsor_name']),
                'sponsor_type' => $data['sponsor_type'] ?? 'other',
                'reference_number' => $data['reference_number'] ?? null,
                'amount' => $amount,
                'payment_method' => $data['payment_method'] ?? 'bank_transfer',
                'bank_account_id' => $data['bank_account_id'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'received_date' => $data['received_date'] ?? now()->toDateString(),
                'academic_year_id' => $data['academic_year_id'] ?? null,
                'term_id' => $data['term_id'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // The one cash posting for the whole receipt: the money arrived
            // once, and distributing it later must not re-post it.
            if (! empty($data['bank_account_id'])) {
                $account = \App\Models\BankAccount::lockForUpdate()->find($data['bank_account_id']);

                if (! $account) {
                    throw new RuntimeException('The selected bank account no longer exists.');
                }

                BankLedger::recordDeposit(
                    $account,
                    $amount,
                    $receipt->received_date->toDateString(),
                    BankLedger::describe('FeeBulkReceipt', $receipt->id,
                        $receipt->sponsor_name . ($receipt->reference_number ? ' ref ' . $receipt->reference_number : '')),
                    $receipt->transaction_id,
                    auth()->id(),
                    'unreconciled',
                    'FeeBulkReceipt',
                    $receipt->id
                );
            }

            AuditTrail::log('Fee Bulk Receipt', 'CREATE', $receipt->id, null, $receipt->toArray());
        });

        return $receipt;
    }

    /**
     * Allocate part of the receipt to students.
     *
     * @param  array<int, array{student_id:int, amount:float}>  $allocations  per-student amounts
     */
    public function allocate(FeeBulkReceipt $receipt, array $allocations): array
    {
        if ($receipt->isReversed()) {
            throw new RuntimeException('This bulk receipt has been reversed and can no longer be allocated.');
        }

        $allocations = collect($allocations)
            ->map(fn ($row) => [
                'student_id' => (int) ($row['student_id'] ?? 0),
                'amount' => round((float) ($row['amount'] ?? 0), 2),
            ])
            ->filter(fn ($row) => $row['student_id'] > 0 && $row['amount'] > 0)
            ->values();

        if ($allocations->isEmpty()) {
            throw new RuntimeException('Provide at least one student with a positive amount.');
        }

        $created = [];

        DB::transaction(function () use ($receipt, $allocations, &$created) {
            // Lock the receipt so two concurrent allocations cannot both pass
            // the remaining-balance test against the same figure.
            $receipt = FeeBulkReceipt::where('id', $receipt->id)->lockForUpdate()->firstOrFail();

            $totalRequested = round($allocations->sum('amount'), 2);
            $remaining = $receipt->remainingAmount();

            if ($totalRequested > $remaining) {
                throw new RuntimeException(sprintf(
                    'Allocation of %s exceeds the remaining %s on this receipt.',
                    number_format($totalRequested, 2),
                    number_format($remaining, 2)
                ));
            }

            foreach ($allocations as $row) {
                $created[] = $this->allocateToStudent($receipt, $row['student_id'], $row['amount']);
            }

            AuditTrail::log('Fee Bulk Receipt', 'ALLOCATE', $receipt->id, null, [
                'students' => $allocations->pluck('student_id')->all(),
                'total' => $totalRequested,
                'remaining_after' => $receipt->remainingAmount(),
            ]);
        });

        return $created;
    }

    /**
     * Give one student part of the receipt as an ordinary fee payment,
     * allocated to their outstanding charges oldest-first.
     */
    private function allocateToStudent(FeeBulkReceipt $receipt, int $studentId, float $amount): FeePayment
    {
        $student = Student::where('student_id', $studentId)->first();

        if (! $student) {
            throw new RuntimeException(sprintf('Student #%d no longer exists.', $studentId));
        }

        $outstanding = StudentFeeAssignment::where('student_id', $studentId)
            ->where('status', 'active')
            ->whereRaw('COALESCE(paid_amount, 0) < final_amount')
            ->orderBy('assigned_date')
            ->orderBy('id')
            ->get();

        if ($outstanding->isEmpty()) {
            throw new RuntimeException(sprintf(
                '%s has no outstanding fee balance to allocate against.',
                trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? '')) ?: "Student #{$studentId}"
            ));
        }

        $payment = FeePayment::create([
            'student_fee_assignment_id' => $outstanding->first()->id,
            'bulk_receipt_id' => $receipt->id,
            'amount' => $amount,
            'payment_date' => $receipt->received_date->toDateString(),
            'payment_method' => $receipt->payment_method,
            'transaction_id' => $receipt->transaction_id,
            'receipt_number' => 'BCR-' . strtoupper(uniqid()),
            'remarks' => sprintf(
                'Bulk receipt: %s%s',
                $receipt->sponsor_name,
                $receipt->reference_number ? ' (' . $receipt->reference_number . ')' : ''
            ),
            'collected_by' => auth()->user()?->staff?->staff_id,
        ]);

        // The existing allocation engine does the per-charge breakdown and the
        // ledger posting: an amount larger than the oldest charge spills to the
        // next-oldest, exactly like a counter payment. No bank or income
        // movement happens here by design — the receipt already carried cash.
        $recommended = $this->ledgerService->recommendAllocation($outstanding, $amount, 'oldest_first');

        $this->ledgerService->allocatePayment($payment, $recommended, 'oldest_first');

        return $payment;
    }

    /**
     * Fully reverse one student allocation from this receipt: the payment's
     * own reversal flow restores the student's balance AND returns the amount
     * to the receipt's remaining balance (because a reversed payment no
     * longer counts towards allocatedAmount()).
     */
    public function reverseAllocation(FeePayment $payment, string $reason): void
    {
        if ($payment->bulk_receipt_id === null) {
            throw new RuntimeException('This payment does not belong to a bulk receipt.');
        }

        $receipt = FeeBulkReceipt::lockForUpdate()->find($payment->bulk_receipt_id);

        if (! $receipt) {
            throw new RuntimeException('The parent bulk receipt no longer exists.');
        }

        if ($payment->isReversed()) {
            throw new RuntimeException('This allocation has already been reversed.');
        }

        // The standard reversal: contra ledger entries, reversed_at flag,
        // balance recompute. It is also the only mutation needed here.
        $this->ledgerService->reversePayment($payment, $reason, auth()->id());

        AuditTrail::log('Fee Bulk Receipt', 'REVERSE ALLOCATION', $receipt->id, null, [
            'payment_id' => $payment->payment_id,
            'student_id' => $payment->studentFeeAssignment?->student_id,
            'amount' => (float) $payment->amount,
            'reason' => $reason,
            'remaining_after' => $receipt->remainingAmount(),
        ]);
    }

    /**
     * Reverse the parent receipt once no allocation is active.
     */
    public function reverseReceipt(FeeBulkReceipt $receipt, string $reason): void
    {
        DB::transaction(function () use ($receipt, $reason) {
            $receipt = FeeBulkReceipt::where('id', $receipt->id)->lockForUpdate()->firstOrFail();

            $receipt->reverse($reason);

            // If the receipt was banked, undo that single deposit.
            if ($receipt->bank_account_id) {
                $transaction = BankLedger::findFor('FeeBulkReceipt', $receipt->id, 'deposit');

                if ($transaction) {
                    BankLedger::reverse($transaction);
                }
            }

            AuditTrail::log('Fee Bulk Receipt', 'REVERSE', $receipt->id, null, [
                'reason' => $reason,
                'amount' => (float) $receipt->amount,
            ]);
        });
    }
}
