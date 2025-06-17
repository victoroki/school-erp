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
        Schema::create('class_sections', function (Blueprint $table) {
            $table->integer('class_section_id', true);
            $table->integer('academic_year_id')->nullable()->index('academic_year_id');
            $table->integer('class_id')->nullable()->index('class_id');
            $table->integer('section_id')->nullable()->index('section_id');
            $table->integer('classroom_id')->nullable()->index('classroom_id');
            $table->integer('class_teacher_id')->nullable()->index('class_teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_sections');
    }
};
