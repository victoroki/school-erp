<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PHASE 6 — student homework submissions.
     *
     * One row per (homework, student); resubmission replaces the row until it
     * is reviewed. homeworks.id is bigint auto (unsigned), but homeworks
     * itself stores created_by as a SIGNED integer because users.id is a
     * signed INT on this MariaDB schema — foreignId()/unsignedInteger break
     * the FK check here too. students.student_id is also a signed integer,
     * so both FKs follow the same explicit signed-integer convention.
     */
    public function up(): void
    {
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            // homeworks.id is a bigint unsigned auto-increment; students.student_id
            // and users.id are signed integers on this MariaDB schema — use the
            // matching column type on each side or the FK check rejects the pair.
            $table->unsignedBigInteger('homework_id');
            $table->foreign('homework_id')->references('id')->on('homeworks');
            $table->integer('student_id');
            $table->foreign('student_id')->references('student_id')->on('students');
            $table->enum('status', ['submitted', 'late', 'reviewed', 'returned'])->default('submitted');
            $table->text('content')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('attachment_mime')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->integer('reviewed_by')->nullable();
            $table->foreign('reviewed_by')->references('id')->on('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['homework_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_submissions');
    }
};