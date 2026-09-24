<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Refunds pay money OUT of a real account (bank or cash office), so the
 * payout must be traceable: which account paid, and the matching
 * bank_transactions withdrawal. BankLedger::findFor() looks the row up by
 * the canonical description ("Refund #id"), so the withdrawal reverses
 * automatically if the refund row is ever removed.
 *
 * Column type matches bank_accounts.account_id (signed int) — MySQL rejects
 * an unsignedBigInteger FK against it — and follows the same pattern as
 * expenses.bank_account_id / income.bank_account_id (indexed, no FK).
 *
 * Historical completed refunds predate this column and never wrote a bank
 * transaction either (complete() only touched the student ledger), so their
 * bank_account_id stays null and the UI shows them as "Not recorded".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            if (!Schema::hasColumn('refunds', 'bank_account_id')) {
                $table->integer('bank_account_id')->nullable()->index()->after('refund_reference');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('refunds', 'bank_account_id')) {
            // Dropped via DDL because the column can exist without its index
            // (e.g. after a partially applied FK attempt), and MySQL removes
            // column indexes together with the column anyway.
            DB::statement('ALTER TABLE `refunds` DROP COLUMN `bank_account_id`');
        }
    }
};
