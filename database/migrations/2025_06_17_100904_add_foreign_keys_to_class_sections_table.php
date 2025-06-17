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
        Schema::table('class_sections', function (Blueprint $table) {
            $table->foreign(['academic_year_id'], 'class_sections_ibfk_1')->references(['academic_year_id'])->on('academic_years')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['class_id'], 'class_sections_ibfk_2')->references(['class_id'])->on('classes')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['section_id'], 'class_sections_ibfk_3')->references(['section_id'])->on('sections')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['classroom_id'], 'class_sections_ibfk_4')->references(['classroom_id'])->on('classrooms')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['class_teacher_id'], 'class_sections_ibfk_5')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_sections', function (Blueprint $table) {
            $table->dropForeign('class_sections_ibfk_1');
            $table->dropForeign('class_sections_ibfk_2');
            $table->dropForeign('class_sections_ibfk_3');
            $table->dropForeign('class_sections_ibfk_4');
            $table->dropForeign('class_sections_ibfk_5');
        });
    }
};
