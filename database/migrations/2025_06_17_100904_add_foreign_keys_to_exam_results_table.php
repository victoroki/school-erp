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
        Schema::table('exam_results', function (Blueprint $table) {
            $table->foreign(['exam_id'], 'exam_results_ibfk_1')->references(['exam_id'])->on('exams')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['student_id'], 'exam_results_ibfk_2')->references(['student_id'])->on('students')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['class_section_id'], 'exam_results_ibfk_3')->references(['class_section_id'])->on('class_sections')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['subject_id'], 'exam_results_ibfk_4')->references(['subject_id'])->on('subjects')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['grade_id'], 'exam_results_ibfk_5')->references(['grade_id'])->on('grading_scales')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['created_by'], 'exam_results_ibfk_6')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropForeign('exam_results_ibfk_1');
            $table->dropForeign('exam_results_ibfk_2');
            $table->dropForeign('exam_results_ibfk_3');
            $table->dropForeign('exam_results_ibfk_4');
            $table->dropForeign('exam_results_ibfk_5');
            $table->dropForeign('exam_results_ibfk_6');
        });
    }
};
