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
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->integer('teacher_subject_id', true);
            $table->integer('staff_id')->nullable()->index('staff_id');
            $table->integer('subject_id')->nullable()->index('subject_id');
            $table->integer('class_section_id')->nullable()->index('class_section_id');
            $table->integer('academic_year_id')->nullable()->index('academic_year_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_subjects');
    }
};
