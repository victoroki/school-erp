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
        Schema::create('assignments', function (Blueprint $table) {
            $table->integer('assignment_id', true);
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('class_section_id')->nullable()->index('class_section_id');
            $table->integer('subject_id')->nullable()->index('subject_id');
            $table->integer('teacher_id')->nullable()->index('teacher_id');
            $table->date('due_date');
            $table->enum('submission_type', ['online', 'offline', 'both'])->nullable()->default('offline');
            $table->decimal('max_marks', 5)->nullable();
            $table->string('attachment_url')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
