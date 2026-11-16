<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_notices', function (Blueprint $table) {
            $table->id();
            // students.student_id / users.id are signed INT on this schema
            // (integer(col,true) on MariaDB) — foreignId()/unsignedInteger
            // fail the FK compatibility check.
            $table->integer('student_id');
            $table->foreign('student_id')->references('student_id')->on('students');
            $table->integer('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->string('title');
            $table->text('body')->nullable();
            $table->enum('notice_type', ['general', 'behavior', 'academic', 'medical', 'attendance', 'other'])->default('general');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_notices');
    }
};