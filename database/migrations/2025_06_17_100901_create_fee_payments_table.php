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
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->integer('payment_id', true);
            $table->integer('student_fee_id')->nullable()->index('student_fee_id');
            $table->decimal('amount', 10);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'check', 'card', 'bank_transfer', 'online']);
            $table->string('transaction_id', 100)->nullable();
            $table->string('receipt_number', 50)->unique('receipt_number');
            $table->text('remarks')->nullable();
            $table->integer('collected_by')->nullable()->index('collected_by');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
