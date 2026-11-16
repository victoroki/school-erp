<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the SHIF deduction alongside the retired NHIF one.
 *
 * The Social Health Insurance Act 2023 took effect on 1 October 2024: the
 * National Health Insurance Fund was wound up and the Social Health Authority
 * now administers the Social Health Insurance Fund.
 *
 * nhif_deduction is left in place. It is not populated by any code, but a school
 * that has run payroll under the old rules may hold historic figures there, and
 * dropping a column holding money is not something to do in a rename migration.
 * The statutory calculation now reports SHIF; the column is the older of the two
 * and can be retired once no report reads it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('payroll_details', 'sha_deduction')) {
            Schema::table('payroll_details', function (Blueprint $table) {
                $table->decimal('sha_deduction', 12, 2)->default(0)
                    ->after('nhif_deduction')
                    ->comment('SHIF employee contribution, Social Health Insurance Act 2023');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payroll_details', 'sha_deduction')) {
            Schema::table('payroll_details', function (Blueprint $table) {
                $table->dropColumn('sha_deduction');
            });
        }
    }
};
