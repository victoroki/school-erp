<?php

namespace App\Services;

use App\Exceptions\InsufficientFundsException;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Support\Facades\DB;

/**
 * Every movement of school money through a bank account must leave two
 * traces: the new balance on bank_accounts and a row on bank_transactions.
 *
 * Before this class those two writes lived as separate snippets in each
 * controller, and ExpensesController::markAsPaid only had the balance half —
 * an expense paid from the bank quietly vanished from the bank statement
 * page while the balance dropped. Any new code that moves money should call
 * this service instead of touching current_balance directly, so the ledger
 * and the balance can never drift apart again.
 *
 * It is also the single place that decides whether an account may go down
 * that far: withdrawals and transfers refuse to push a balance below the
 * account's minimum_balance (see assertSufficientBalance()).
 */
class BankLedger
{
    /**
     * Money out of an account (expense paid, withdrawal). Decrements the
     * balance and records a withdrawal transaction in the same DB commit.
     */
    public static function recordWithdrawal(
        BankAccount $account,
        float $amount,
        string $date,
        ?string $description = null,
        ?string $reference = null,
        ?int $userId = null,
        string $status = 'unreconciled',
        ?string $sourceType = null,
        ?int $sourceId = null
    ): BankTransaction {
        return self::record($account, $amount, 'withdrawal', $date, $description, $reference, $userId, $status, $sourceType, $sourceId);
    }

    /**
     * Money into an account (income banked, deposit). Increments the balance
     * and records a deposit transaction in the same DB commit.
     */
    public static function recordDeposit(
        BankAccount $account,
        float $amount,
        string $date,
        ?string $description = null,
        ?string $reference = null,
        ?int $userId = null,
        string $status = 'unreconciled',
        ?string $sourceType = null,
        ?int $sourceId = null
    ): BankTransaction {
        return self::record($account, $amount, 'deposit', $date, $description, $reference, $userId, $status, $sourceType, $sourceId);
    }

    /**
     * Move money between two accounts. Records TWO transfer rows sharing one
     * reference (so each side of the move appears on its own account's
     * statement) and adjusts both balances atomically.
     */
    public static function recordTransfer(
        BankAccount $from,
        BankAccount $to,
        float $amount,
        string $date,
        ?string $description = null,
        ?string $reference = null,
        ?int $userId = null
    ): BankTransaction {
        return DB::transaction(function () use ($from, $to, $amount, $date, $description, $reference, $userId) {
            self::assertSufficientBalance($from, $amount);

            $from->decrement('current_balance', $amount);
            $to->increment('current_balance', $amount);

            $reference ??= 'TRANSFER-' . now()->format('YmdHis') . '-' . $from->account_id . '-' . $to->account_id;

            // One row per side: the source account sees the money leaving, the
            // destination account sees it arriving. Before this there was a
            // single row on the source only, so the destination's statement
            // never showed the transfer and reconciliation could never match.
            BankTransaction::create([
                'account_id'        => $from->account_id,
                'amount'            => $amount,
                'transaction_type'  => 'withdrawal',
                'transaction_date'  => $date,
                'description'       => $description,
                'reference_number'  => $reference,
                'source_account_id' => $from->account_id,
                'target_account_id' => $to->account_id,
                'created_by'        => $userId,
                'status'            => 'unreconciled',
            ]);

            return BankTransaction::create([
                'account_id'        => $to->account_id,
                'amount'            => $amount,
                'transaction_type'  => 'deposit',
                'transaction_date'  => $date,
                'description'       => $description,
                'reference_number'  => $reference,
                'source_account_id' => $from->account_id,
                'target_account_id' => $to->account_id,
                'created_by'        => $userId,
                'status'            => 'unreconciled',
            ]);
        });
    }

    /**
     * Undo a transaction when its source record is deleted (an expense or
     * income row removed after it was already banked). Reverses the balance
     * effect and marks the ledger row voided — the row stays visible so the
     * statement history is never silently rewritten.
     */
    public static function reverse(BankTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $account = BankAccount::find($transaction->account_id);

            if ($account) {
                if ($transaction->transaction_type === 'deposit') {
                    $account->decrement('current_balance', $transaction->amount);
                } elseif ($transaction->transaction_type === 'withdrawal') {
                    $account->increment('current_balance', $transaction->amount);
                }
            }

            $transaction->forceFill(['status' => 'voided'])->save();
        });
    }

    /**
     * Canonical ledger description for a transaction that originates from a
     * source record (an expense payment, a banking of income). Used when
     * writing the row and when looking it up again for reversal, so the link
     * never depends on string formatting drifting between call sites.
     */
    public static function describe(string $source, int $id, ?string $detail = null): string
    {
        return $source . ' #' . $id . ($detail ? ' — ' . $detail : '');
    }

    /**
     * Find the ledger row recorded for a source record, so deleting that
     * record can reverse it. Rows written since the source-link migration
     * carry source_type/source_id and match on those directly; older rows are
     * matched by the description prefix describe() produces. Returns null when
     * the money never moved through the ledger (e.g. a cash expense).
     */
    public static function findFor(string $source, int $id): ?BankTransaction
    {
        return BankTransaction::query()
            ->where('transaction_type', $source === 'Income' ? 'deposit' : 'withdrawal')
            ->where('status', '!=', 'voided')
            ->where(function ($query) use ($source, $id) {
                $query->where('source_type', $source)
                    ->where('source_id', $id)
                    ->orWhere(function ($legacy) use ($source, $id) {
                        $legacy->where('description', $source . ' #' . $id)
                            ->orWhere('description', 'like', $source . ' #' . $id . ' —%');
                    });
            })
            ->latest('transaction_id')
            ->first();
    }

    private static function record(
        BankAccount $account,
        float $amount,
        string $type,
        string $date,
        ?string $description,
        ?string $reference,
        ?int $userId,
        string $status,
        ?string $sourceType = null,
        ?int $sourceId = null
    ): BankTransaction {
        return DB::transaction(function () use ($account, $amount, $type, $date, $description, $reference, $userId, $status, $sourceType, $sourceId) {
            if ($type === 'deposit') {
                $account->increment('current_balance', $amount);
            } else {
                self::assertSufficientBalance($account, $amount);
                $account->decrement('current_balance', $amount);
            }

            return BankTransaction::create([
                'account_id'       => $account->account_id,
                'amount'           => $amount,
                'transaction_type' => $type,
                'transaction_date' => $date,
                'description'      => $description,
                'reference_number' => $reference,
                'source_type'      => $sourceType,
                'source_id'        => $sourceId,
                'created_by'       => $userId,
                'status'           => $status,
            ]);
        });
    }

    /**
     * Refuse to drive an account below its configured minimum balance.
     *
     * money_out is what the caller wants to withdraw; the account's
     * minimum_balance column (default 0) is the floor. Both are compared at
     * 2 decimals, the precision every money column in this schema carries, so
     * float residue cannot flip the answer.
     *
     * @throws InsufficientFundsException when the withdrawal would breach the floor.
     */
    private static function assertSufficientBalance(BankAccount $account, float $moneyOut): void
    {
        $balance = round((float) $account->current_balance, 2);
        $minimum = round((float) ($account->minimum_balance ?? 0), 2);

        if (round($balance - $moneyOut, 2) < $minimum) {
            throw new InsufficientFundsException($account, $moneyOut);
        }
    }
}
