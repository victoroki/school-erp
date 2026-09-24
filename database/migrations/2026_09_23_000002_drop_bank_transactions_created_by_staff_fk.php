<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * bank_transactions.created_by stores the authenticated USER id — every
 * writer (BankTransactionController, ExpensesController, IncomeController,
 * RefundController) passes auth()->id(), and BankTransaction::creator()
 * relates to App\Models\User. The 2025 FK to staff.staff_id contradicted
 * both, worked only while user ids and staff ids happened to coincide 1:1,
 * and hard-failed any transaction recorded by a user without a staff row
 * (SQLSTATE 23000, FK bank_transactions_ibfk_4).
 *
 * Drop the wrong constraint; the integer column itself is kept as-is.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bank_transactions', 'created_by')) {
            Schema::table('bank_transactions', function ($table) {
                $table->dropForeign('bank_transactions_ibfk_4');
            });
        }
    }

    public function down(): void
    {
        Schema::table('bank_transactions', function ($table) {
            $table->foreign(['created_by'], 'bank_transactions_ibfk_4')
                ->references(['staff_id'])
                ->on('staff')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }
};
