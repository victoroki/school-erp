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
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->foreign(['exam_id'], 'exam_schedules_ibfk_1')->references(['exam_id'])->on('exams')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['class_id'], 'exam_schedules_ibfk_2')->references(['class_id'])->on('classes')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['subject_id'], 'exam_schedules_ibfk_3')->references(['subject_id'])->on('subjects')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['room_id'], 'exam_schedules_ibfk_4')->references(['classroom_id'])->on('classrooms')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_schedules', function (Blueprint $table) {
            $table->dropForeign('exam_schedules_ibfk_1');
            $table->dropForeign('exam_schedules_ibfk_2');
            $table->dropForeign('exam_schedules_ibfk_3');
            $table->dropForeign('exam_schedules_ibfk_4');
        });
    }
};
