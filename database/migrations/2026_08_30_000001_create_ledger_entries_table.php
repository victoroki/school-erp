<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chronological ledger — the financial source of truth for each student.
     *
     * Balance concept (per the Fee Management requirements doc):
     *   Closing = Opening + New Charges + Debit Adjustments - Payments - Credits/Waivers
     *
     * Entries are append-only. Corrections create new reversal/contra entries
     * rather than mutating history so the account stays auditable.
     */
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id');              // matches students.student_id (int)
            $table->integer('academic_year_id')->nullable(); // matches academic_years.academic_year_id (int)
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('student_fee_assignment_id')->nullable();

            $table->dateTime('entry_date');
            $table->string('description');

            // charge, payment, adjustment, credit, refund, reversal, opening_balance
            $table->enum('entry_type', [
                'charge', 'payment', 'adjustment', 'credit', 'refund', 'reversal', 'opening_balance',
            ])->default('charge');

            $table->decimal('debit', 15, 2)->default(0);   // increases what is owed
            $table->decimal('credit', 15, 2)->default(0);  // decreases what is owed
            $table->decimal('balance_after', 15, 2)->default(0);

            // Polymorphic reference to the originating record (payment, fee adjustment, refund...)
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->integer('created_by')->nullable();
            $table->string('source', 50)->default('manual');

            // A reversal that points back at the entry it reverses (null = not a reversal)
            $table->unsignedBigInteger('reverses_entry_id')->nullable();

            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('students')->cascadeOnDelete();
            $table->foreign('academic_year_id')->references('academic_year_id')->on('academic_years')->nullOnDelete();
            $table->foreign('term_id')->references('id')->on('terms')->nullOnDelete();
            $table->foreign('student_fee_assignment_id')->references('id')->on('student_fee_assignments')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reverses_entry_id')->references('id')->on('ledger_entries')->nullOnDelete();
            $table->index(['student_id', 'entry_date']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('entry_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
