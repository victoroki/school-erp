<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Subjects that carry academic history cannot be deleted — six tables
 * (class_subjects, teacher_subjects, assignments, exam_results,
 * exam_schedules and timetable) reference subjects with ON DELETE RESTRICT.
 * Rather than dropping those constraints, a subject with history is
 * *archived*: the row and all of its historical references stay intact, but
 * the subject stops being offered for new class allocations and report cards.
 *
 * Existing rows are all treated as active, so the column is purely additive
 * and the rollback is a plain drop.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('subjects', 'is_active')) {
            return;
        }

        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('is_elective');
        });

        // Backfill defensively: any pre-existing row that predates the column
        // must stay selectable.
        DB::table('subjects')->whereNull('is_active')->update(['is_active' => true]);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('subjects', 'is_active')) {
            return;
        }

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
