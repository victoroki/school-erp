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
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->foreign(['class_id'], 'class_subjects_ibfk_1')->references(['class_id'])->on('classes')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['subject_id'], 'class_subjects_ibfk_2')->references(['subject_id'])->on('subjects')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['academic_year_id'], 'class_subjects_ibfk_3')->references(['academic_year_id'])->on('academic_years')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_subjects', function (Blueprint $table) {
            $table->dropForeign('class_subjects_ibfk_1');
            $table->dropForeign('class_subjects_ibfk_2');
            $table->dropForeign('class_subjects_ibfk_3');
        });
    }
};
