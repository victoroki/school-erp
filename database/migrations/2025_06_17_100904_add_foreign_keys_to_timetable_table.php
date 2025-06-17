<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('timetable', function (Blueprint $table) {
            $table->foreign(['class_section_id'], 'timetable_ibfk_1')->references(['class_section_id'])->on('class_sections')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['period_id'], 'timetable_ibfk_2')->references(['period_id'])->on('periods')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['subject_id'], 'timetable_ibfk_3')->references(['subject_id'])->on('subjects')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['teacher_id'], 'timetable_ibfk_4')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['classroom_id'], 'timetable_ibfk_5')->references(['classroom_id'])->on('classrooms')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['academic_year_id'], 'timetable_ibfk_6')->references(['academic_year_id'])->on('academic_years')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetable', function (Blueprint $table) {
            $table->dropForeign('timetable_ibfk_1');
            $table->dropForeign('timetable_ibfk_2');
            $table->dropForeign('timetable_ibfk_3');
            $table->dropForeign('timetable_ibfk_4');
            $table->dropForeign('timetable_ibfk_5');
            $table->dropForeign('timetable_ibfk_6');
        });
    }
};
