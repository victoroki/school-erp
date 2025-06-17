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
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->foreign(['staff_id'], 'teacher_subjects_ibfk_1')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['subject_id'], 'teacher_subjects_ibfk_2')->references(['subject_id'])->on('subjects')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['class_section_id'], 'teacher_subjects_ibfk_3')->references(['class_section_id'])->on('class_sections')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['academic_year_id'], 'teacher_subjects_ibfk_4')->references(['academic_year_id'])->on('academic_years')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->dropForeign('teacher_subjects_ibfk_1');
            $table->dropForeign('teacher_subjects_ibfk_2');
            $table->dropForeign('teacher_subjects_ibfk_3');
            $table->dropForeign('teacher_subjects_ibfk_4');
        });
    }
};
