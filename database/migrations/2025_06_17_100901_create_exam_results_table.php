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
        Schema::create('exam_results', function (Blueprint $table) {
            $table->integer('result_id', true);
            $table->integer('exam_id')->nullable()->index('exam_id');
            $table->integer('student_id')->nullable()->index('student_id');
            $table->integer('class_section_id')->nullable()->index('class_section_id');
            $table->integer('subject_id')->nullable()->index('subject_id');
            $table->decimal('marks_obtained', 5);
            $table->integer('grade_id')->nullable()->index('grade_id');
            $table->text('remarks')->nullable();
            $table->integer('created_by')->nullable()->index('created_by');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
