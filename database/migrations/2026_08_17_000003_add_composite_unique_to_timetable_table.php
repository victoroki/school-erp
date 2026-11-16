<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable', function (Blueprint $table) {
            $table->unique(
                ['teacher_id', 'day_of_week', 'period_id', 'academic_year_id'],
                'uniq_teacher_slot'
            );
            $table->unique(
                ['classroom_id', 'day_of_week', 'period_id', 'academic_year_id'],
                'uniq_classroom_slot'
            );
            $table->unique(
                ['class_section_id', 'day_of_week', 'period_id', 'academic_year_id'],
                'uniq_class_section_slot'
            );
        });
    }

    public function down(): void
    {
        Schema::table('timetable', function (Blueprint $table) {
            $table->dropUnique('uniq_teacher_slot');
            $table->dropUnique('uniq_classroom_slot');
            $table->dropUnique('uniq_class_section_slot');
        });
    }
};
