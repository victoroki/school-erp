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
        Schema::table('exams', function (Blueprint $table) {
            $table->foreign(['exam_type_id'], 'exams_ibfk_1')->references(['exam_type_id'])->on('exam_types')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['academic_year_id'], 'exams_ibfk_2')->references(['academic_year_id'])->on('academic_years')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign('exams_ibfk_1');
            $table->dropForeign('exams_ibfk_2');
        });
    }
};
