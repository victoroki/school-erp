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
        Schema::create('student_attendance', function (Blueprint $table) {
            $table->integer('attendance_id', true);
            $table->integer('student_id')->nullable()->index('student_id');
            $table->integer('class_section_id')->nullable()->index('class_section_id');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'excused']);
            $table->text('remarks')->nullable();
            $table->integer('marked_by')->nullable()->index('marked_by');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendance');
    }
};
