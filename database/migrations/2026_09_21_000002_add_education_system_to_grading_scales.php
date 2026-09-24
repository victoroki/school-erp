<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Give grading scales a curriculum discriminator.
 *
 * `grading_scales` is a single global table, and the KCSE (8-4-4) and CBC/CBE
 * bands overlap heavily on the same percentage — for example 55% matches both
 * KCSE "C+" and CBE "ME2". `ExamResult::saving` resolved a grade with
 *
 *     GradingScale::where('min_percentage','<=',$pct)->where('max_percentage','>=',$pct)->first();
 *
 * with no ordering and no system filter, so which grade a learner received
 * depended on row order. A school that pressed both "Load KCSE" and
 * "Load CBC" ended up with two contradictory scales in one unlabelled list.
 *
 * `education_system` is nullable on purpose:
 *   - NULL means "applies to every system" (custom scales, and every row that
 *     already exists — so this migration does not change existing behaviour);
 *   - '8-4-4' or 'CBC' means the row belongs to that curriculum only.
 *
 * Newly seeded national scales tag themselves. Existing rows are deliberately
 * NOT rewritten, because a pre-existing row's intended system cannot be known
 * reliably from its name alone.
 *
 * Additive and reversible: one nullable column plus an index.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grading_scales', function (Blueprint $table) {
            $table->string('education_system', 10)->nullable()->after('name');
            $table->index('education_system', 'grading_scales_education_system_index');
        });
    }

    public function down(): void
    {
        Schema::table('grading_scales', function (Blueprint $table) {
            $table->dropIndex('grading_scales_education_system_index');
            $table->dropColumn('education_system');
        });
    }
};
