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
        Schema::create('timetable', function (Blueprint $table) {
            $table->integer('timetable_id', true);
            $table->integer('class_section_id')->nullable()->index('class_section_id');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->integer('period_id')->nullable()->index('period_id');
            $table->integer('subject_id')->nullable()->index('subject_id');
            $table->integer('teacher_id')->nullable()->index('teacher_id');
            $table->integer('classroom_id')->nullable()->index('classroom_id');
            $table->integer('academic_year_id')->nullable()->index('academic_year_id');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetable');
    }
};
