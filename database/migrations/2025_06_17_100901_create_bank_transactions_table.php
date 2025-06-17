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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->integer('transaction_id', true);
            $table->integer('account_id')->nullable()->index('account_id');
            $table->decimal('amount', 10);
            $table->enum('transaction_type', ['deposit', 'withdrawal', 'transfer']);
            $table->date('transaction_date');
            $table->text('description')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->integer('source_account_id')->nullable()->index('source_account_id')->comment('For transfers between accounts');
            $table->integer('target_account_id')->nullable()->index('target_account_id')->comment('For transfers between accounts');
            $table->integer('created_by')->nullable()->index('created_by');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
