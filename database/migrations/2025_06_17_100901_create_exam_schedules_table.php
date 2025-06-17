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
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->integer('schedule_id', true);
            $table->integer('exam_id')->nullable()->index('exam_id');
            $table->integer('class_id')->nullable()->index('class_id');
            $table->integer('subject_id')->nullable()->index('subject_id');
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('room_id')->nullable()->index('room_id');
            $table->decimal('max_marks', 5);
            $table->decimal('passing_marks', 5);
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
