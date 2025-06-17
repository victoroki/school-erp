<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->foreign(['account_id'], 'bank_transactions_ibfk_1')->references(['account_id'])->on('bank_accounts')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['source_account_id'], 'bank_transactions_ibfk_2')->references(['account_id'])->on('bank_accounts')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['target_account_id'], 'bank_transactions_ibfk_3')->references(['account_id'])->on('bank_accounts')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['created_by'], 'bank_transactions_ibfk_4')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->dropForeign('bank_transactions_ibfk_1');
            $table->dropForeign('bank_transactions_ibfk_2');
            $table->dropForeign('bank_transactions_ibfk_3');
            $table->dropForeign('bank_transactions_ibfk_4');
        });
    }
};
