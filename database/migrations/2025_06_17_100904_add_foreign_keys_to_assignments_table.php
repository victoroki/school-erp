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
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreign(['class_section_id'], 'assignments_ibfk_1')->references(['class_section_id'])->on('class_sections')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['subject_id'], 'assignments_ibfk_2')->references(['subject_id'])->on('subjects')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['teacher_id'], 'assignments_ibfk_3')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign('assignments_ibfk_1');
            $table->dropForeign('assignments_ibfk_2');
            $table->dropForeign('assignments_ibfk_3');
        });
    }
};
