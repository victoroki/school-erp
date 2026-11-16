<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fee payments can now name the account they were received into. Nullable:
 * cash payments never touch a bank account, and historical rows keep working
 * without one (the fee-to-bank reconciliation command reports them).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->integer('bank_account_id')->nullable()->after('payment_method');
            $table->foreign('bank_account_id')->references('account_id')->on('bank_accounts')->nullOnDelete();
            $table->index('bank_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropIndex(['bank_account_id']);
            $table->dropColumn('bank_account_id');
        });
    }
};
