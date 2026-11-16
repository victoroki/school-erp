<?php

namespace App\Console\Commands;

use App\Models\BankTransaction;
use App\Models\FeePayment;
use App\Services\BankLedger;
use Illuminate\Console\Command;

/**
 * Reports which fee payments are reflected in the bank ledger and which are
 * not. READ-ONLY: this command never writes. Historical payments predate the
 * fee-to-bank posting rule, so most will be unmatched — deciding whether to
 * migrate them is an administrative decision, not an automated one.
 *
 * Usage:
 *   php artisan fee:bank-reconciliation             # summary only
 *   php artisan fee:bank-reconciliation --details   # list every unmatched payment
 */
class FeeBankReconciliation extends Command
{
    protected $signature = 'fee:bank-reconciliation {--details : List every unmatched payment}';

    protected $description = 'Report fee payments that have no matching bank ledger deposit (read-only audit)';

    public function handle(): int
    {
        $valid = FeePayment::notReversed()->orderBy('payment_date')->get(['payment_id', 'amount', 'payment_date', 'payment_method', 'receipt_number', 'bulk_receipt_id']);

        $posted = 0;
        $unposted = 0;
        $unpostedTotal = 0.0;
        $unpostedRows = [];

        foreach ($valid as $payment) {
            $hasLedgerRow = BankTransaction::query()
                ->where('source_type', 'FeePayment')
                ->where('source_id', $payment->payment_id)
                ->where('status', '!=', 'voided')
                ->exists();

            if ($hasLedgerRow) {
                $posted++;
            } else {
                $unposted++;
                $unpostedTotal += (float) $payment->amount;

                if ($this->option('details')) {
                    $unpostedRows[] = [
                        $payment->payment_id,
                        $payment->receipt_number,
                        $payment->payment_date->toDateString(),
                        $payment->payment_method,
                        number_format((float) $payment->amount, 2),
                        $payment->bulk_receipt_id ? 'bulk' : 'counter',
                    ];
                }
            }
        }

        $this->info('Fee-to-bank reconciliation (read-only)');
        $this->line('--------------------------------------');
        $this->line("Valid (non-reversed) fee payments: {$valid->count()}");
        $this->line("Posted to the bank ledger:         {$posted}");
        $this->line("Not posted:                        {$unposted} (KES " . number_format($unpostedTotal, 2) . ')');

        if ($unposted > 0) {
            $this->newLine();
            $this->warn('Historical payments predate fee-to-bank posting. They are NOT migrated automatically:');
            $this->warn('decide per-payment whether the cash was banked, then migrate explicitly if needed.');
        }

        if ($this->option('details') && $unpostedRows !== []) {
            $this->table(
                ['Payment ID', 'Receipt', 'Date', 'Method', 'Amount', 'Source'],
                $unpostedRows
            );
        }

        return self::SUCCESS;
    }
}
