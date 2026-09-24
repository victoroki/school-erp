<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Give fee payments a first-class reversal state.
 *
 * Previously a reversal only posted contra ledger entries and recomputed
 * `student_fee_assignments.paid_amount`. The payment row itself was untouched:
 * it kept its amount, its receipt number and a normal-looking receipt, and
 * every "SUM(fee_payments.amount)" figure kept counting the reversed money.
 * That is why a student's balance could differ between the fee screen
 * (assignment paid_amount) and the student profile / arrears reports (raw sum).
 *
 * Additive and reversible: no existing column is dropped or retyped downwards.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->timestamp('reversed_at')->nullable()->after('receipt_number');
            $table->string('reversal_reason', 255)->nullable()->after('reversed_at');

            // Intentionally a plain signed integer rather than a foreign key:
            // users.id is a signed INT in this schema, and the sibling
            // `collected_by` column follows the same convention. foreignId()
            // emits bigint unsigned and failed DDL with
            // "Referencing column 'reversed_by' and referenced column 'id' ...
            // are incompatible".
            $table->integer('reversed_by')->nullable()->after('reversal_reason');

            // Every balance query filters on this, so keep it cheap.
            $table->index('reversed_at', 'fee_payments_reversed_at_index');
        });

        // Money precision: the original create migration declared decimal(10),
        // which MySQL treats as decimal(10,0) - whole shillings. The live
        // database already carries (10,2), but an install built purely from
        // migrations would silently truncate cents. Converge both.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE fee_payments MODIFY amount DECIMAL(10,2) NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropIndex('fee_payments_reversed_at_index');
            $table->dropColumn(['reversed_at', 'reversal_reason', 'reversed_by']);
        });
    }
};
