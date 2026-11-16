<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Approved migration B.
 *
 * disciplinary_records, medical_incidents and emergency_contacts are the only
 * learner-owned tables with no referential integrity on student_id, so removing
 * a learner leaves orphaned disciplinary and medical history behind.
 *
 * Pre-flight checks on the live database: all three tables are empty (0 rows,
 * therefore 0 orphans), no foreign key already exists on any of the three
 * columns, and every column is `int` — matching `students.student_id`
 * (int, primary key) exactly, which FKs require.
 *
 * cascadeOnDelete matches the sibling and enrollment tables. Note it erases a
 * learner's incident history when the learner row is deleted. That is already
 * today's effective behaviour (nothing restrains the delete), but if learners
 * later become soft-deletable the cascade will never fire and the history is
 * preserved instead.
 *
 * Additive and reversible: three foreign keys.
 */
return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $tables = [
        'disciplinary_records',
        'medical_incidents',
        'emergency_contacts',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreign('student_id')
                    ->references('student_id')
                    ->on('students')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['student_id']);
            });
        }
    }
};
