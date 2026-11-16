<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Approved migration A.
 *
 * NEMIS and UPI are national learner identifiers. Without uniqueness the same
 * learner can be admitted twice and then appear twice in every report, with
 * two fee ledgers and two attendance histories.
 *
 * Both columns are nullable varchar(50), and MySQL permits multiple NULLs in a
 * unique index, so learners without an identifier are unaffected. Pre-flight
 * check on the live database: 0 duplicate values and 0 empty strings in either
 * column, so nothing needs cleaning first.
 *
 * Additive and reversible: two indexes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unique('nemis_number', 'students_nemis_number_unique');
            $table->unique('upi_number', 'students_upi_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_nemis_number_unique');
            $table->dropUnique('students_upi_number_unique');
        });
    }
};
