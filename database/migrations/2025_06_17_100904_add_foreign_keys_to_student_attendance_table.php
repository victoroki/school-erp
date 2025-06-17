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
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->foreign(['student_id'], 'student_attendance_ibfk_1')->references(['student_id'])->on('students')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['class_section_id'], 'student_attendance_ibfk_2')->references(['class_section_id'])->on('class_sections')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['marked_by'], 'student_attendance_ibfk_3')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->dropForeign('student_attendance_ibfk_1');
            $table->dropForeign('student_attendance_ibfk_2');
            $table->dropForeign('student_attendance_ibfk_3');
        });
    }
};
