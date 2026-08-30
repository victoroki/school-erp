<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Refund management.
     *
     * Refunds move money out of the school, so they follow a formal workflow:
     * requested -> reviewed (approved / rejected) -> completed.
     * On completion a refund ledger entry (credit) is posted and the payer
     * may be notified.
     */
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id');       // matches students.student_id (int)
            $table->integer('payment_id')->nullable(); // original payment being refunded (int)
            $table->unsignedBigInteger('student_fee_assignment_id')->nullable();
            $table->decimal('amount', 15, 2);

            $table->string('reason');
            $table->string('supporting_info')->nullable();

            $table->enum('status', ['requested', 'approved', 'rejected', 'completed'])->default('requested');

            $table->integer('requested_by')->nullable();
            $table->timestamp('requested_at')->useCurrent();

            $table->integer('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->string('refund_method')->nullable(); // mpesa, bank, cash, cheque...
            $table->string('refund_reference')->nullable();

            $table->unsignedBigInteger('ledger_entry_id')->nullable();
            $table->integer('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('students')->cascadeOnDelete();
            $table->foreign('payment_id')->references('payment_id')->on('fee_payments')->nullOnDelete();
            $table->foreign('student_fee_assignment_id')->references('id')->on('student_fee_assignments')->nullOnDelete();
            $table->foreign('requested_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('ledger_entry_id')->references('id')->on('ledger_entries')->nullOnDelete();
            $table->foreign('completed_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['student_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
