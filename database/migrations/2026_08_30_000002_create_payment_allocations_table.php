<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links a payment to the specific charge(s) it settles.
     *
     * A single payment may be allocated across multiple outstanding charges
     * (oldest-first, fee-priority, manual, or proportional). Every allocation
     * is recorded so a future statement can explain exactly how a payment
     * affected the account.
     */
    public function up(): void
    {
        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->integer('payment_id');       // matches fee_payments.payment_id (int)
            $table->unsignedBigInteger('student_fee_assignment_id');
            $table->decimal('amount', 15, 2)->default(0);

            // oldest_first, fee_priority, manual, proportional
            $table->string('allocation_strategy', 30)->default('manual');

            $table->integer('created_by')->nullable();
            $table->timestamp('allocated_at')->useCurrent();

            $table->foreign('payment_id')->references('payment_id')->on('fee_payments')->cascadeOnDelete();
            $table->foreign('student_fee_assignment_id')->references('id')->on('student_fee_assignments')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['payment_id']);
            $table->index(['student_fee_assignment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
    }
};
