<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Classrooms referenced by class_sections, timetable and exam_schedules carry
 * ON DELETE RESTRICT constraints, so a room in academic use cannot be deleted
 * — and must not be. Such rooms are archived instead: the row and every
 * reference stay intact, while the room stops being offered for new sections,
 * timetabling and exam allocation.
 *
 * Existing rows are all treated as active, so the column is purely additive
 * and the rollback is a plain drop.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('classrooms', 'is_active')) {
            return;
        }

        Schema::table('classrooms', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('capacity');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('classrooms', 'is_active')) {
            return;
        }

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
