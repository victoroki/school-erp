<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add paid_amount column to student_fee_assignments
        if (!Schema::hasColumn('student_fee_assignments', 'paid_amount')) {
            Schema::table('student_fee_assignments', function (Blueprint $table) {
                $table->decimal('paid_amount', 10, 2)->default(0)->after('final_amount');
            });
        }

        // Drop legacy foreign keys first
        if (Schema::hasColumn('fee_payments', 'student_fee_id')) {
            // Get all indexes/foreign keys on fee_payments
            $keys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fee_payments' AND REFERENCED_TABLE_NAME = 'student_fees'");
            
            foreach ($keys as $key) {
                Schema::table('fee_payments', function (Blueprint $table) use ($key) {
                    $table->dropForeign($key->CONSTRAINT_NAME);
                });
            }
        }

        // Drop legacy tables
        Schema::dropIfExists('student_fee_discounts');
        Schema::dropIfExists('fee_discounts');
        Schema::dropIfExists('student_fees');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate legacy tables (empty)
        Schema::create('student_fees', function (Blueprint $table) {
            $table->increments('student_fee_id');
            $table->integer('student_id')->unsigned();
            $table->integer('fee_structure_id')->unsigned();
            $table->decimal('amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2);
            $table->date('due_date')->nullable();
            $table->enum('status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid');
            $table->timestamps();
        });

        Schema::dropIfExists('student_fee_discounts');
        Schema::dropIfExists('fee_discounts');

        // Drop paid_amount column
        if (Schema::hasColumn('student_fee_assignments', 'paid_amount')) {
            Schema::table('student_fee_assignments', function (Blueprint $table) {
                $table->dropColumn('paid_amount');
            });
        }
    }
};
