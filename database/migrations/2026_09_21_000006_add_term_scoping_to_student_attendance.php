<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Approved migration D — term scoping on student_attendance.
 *
 * Two problems, fixed together (the user confirmed they must land in the same
 * batch, because the index alone would turn today's silent duplicate into a 500):
 *
 *  1. Attendance is not scoped to an academic year or term, so "this term's
 *     register" cannot be reconstructed.
 *  2. The two write paths key an entry differently — the web controller on
 *     (student_id, class_section_id, date), the mobile app on (student_id, date)
 *     — so marking a learner on both surfaces produces two rows for one day, and
 *     the mobile row carries a NULL class_section_id so it also disappears from
 *     class-filtered views. A learner can only have one status per day, so the
 *     database should enforce (student_id, date).
 *
 * Pre-flight on the live database: student_attendance is empty (0 rows, so 0
 * duplicate pairs), and the column types for the new FKs are confirmed —
 * academic_years.academic_year_id is `int`, terms.id is `bigint unsigned`.
 *
 * Additive and reversible. Both new columns are nullable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->integer('academic_year_id')->nullable()->after('class_section_id');
            $table->unsignedBigInteger('term_id')->nullable()->after('academic_year_id');

            $table->index('academic_year_id', 'student_attendance_academic_year_index');
            $table->index('term_id', 'student_attendance_term_index');

            $table->foreign('academic_year_id')
                ->references('academic_year_id')->on('academic_years')
                ->nullOnDelete();

            $table->foreign('term_id')
                ->references('id')->on('terms')
                ->nullOnDelete();

            // One attendance status per learner per day.
            $table->unique(['student_id', 'date'], 'student_attendance_student_date_unique');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $this->backfill();
        }
    }

    /**
     * Populate academic_year_id and term_id for existing rows.
     *
     * Set-based rather than per-row, and deliberately non-destructive: an
     * unresolvable row keeps NULL instead of being assigned a guessed term.
     * A no-op on the current database (0 rows), written to be correct elsewhere.
     */
    private function backfill(): void
    {
        // 1. The enrollment whose academic year covers the attendance date.
        DB::statement("
            UPDATE student_attendance a
            JOIN student_class_enrollments e ON e.student_id = a.student_id
            JOIN academic_years y ON y.academic_year_id = e.academic_year_id
            SET a.academic_year_id = e.academic_year_id
            WHERE a.academic_year_id IS NULL
              AND a.date BETWEEN y.start_date AND y.end_date
        ");

        // 2. Fall back to the learner's current enrollment.
        DB::statement("
            UPDATE student_attendance a
            JOIN student_class_enrollments e
              ON e.student_id = a.student_id AND e.is_current = 1
            SET a.academic_year_id = e.academic_year_id
            WHERE a.academic_year_id IS NULL
        ");

        // 3. The term within that year whose date range covers the attendance date.
        DB::statement("
            UPDATE student_attendance a
            JOIN terms t
              ON t.academic_year_id = a.academic_year_id
             AND a.date BETWEEN t.start_date AND t.end_date
            SET a.term_id = t.id
            WHERE a.academic_year_id IS NOT NULL
              AND a.term_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->dropUnique('student_attendance_student_date_unique');

            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['term_id']);

            $table->dropIndex('student_attendance_academic_year_index');
            $table->dropIndex('student_attendance_term_index');

            $table->dropColumn(['academic_year_id', 'term_id']);
        });
    }
};
