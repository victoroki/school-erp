<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_overrides', function (Blueprint $table) {
            $table->id();
            $table->integer('timetable_id');
            $table->unsignedBigInteger('term_week_id');
            $table->enum('override_type', ['cancel', 'substitute', 'reschedule']);
            $table->integer('substitute_teacher_id')->nullable();
            $table->integer('substitute_classroom_id')->nullable();
            $table->enum('new_day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->nullable();
            $table->integer('new_period_id')->nullable();
            $table->integer('new_teacher_id')->nullable();
            $table->integer('new_classroom_id')->nullable();
            $table->date('effective_date');
            $table->string('reason', 255)->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->foreign('timetable_id')->references('timetable_id')->on('timetable')->onDelete('cascade');
            $table->foreign('term_week_id')->references('id')->on('term_weeks')->onDelete('cascade');
            $table->foreign('substitute_teacher_id')->references('staff_id')->on('staff')->onDelete('set null');
            $table->foreign('substitute_classroom_id')->references('classroom_id')->on('classrooms')->onDelete('set null');
            $table->foreign('new_period_id')->references('period_id')->on('periods')->onDelete('set null');
            $table->foreign('new_teacher_id')->references('staff_id')->on('staff')->onDelete('set null');
            $table->foreign('new_classroom_id')->references('classroom_id')->on('classrooms')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->unique(['timetable_id', 'term_week_id']);
            $table->index(['term_week_id', 'effective_date']);
            $table->index('effective_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_overrides');
    }
};
