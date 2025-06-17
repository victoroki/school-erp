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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->integer('submission_id', true);
            $table->integer('assignment_id')->nullable()->index('assignment_id');
            $table->integer('student_id')->nullable()->index('student_id');
            $table->dateTime('submission_date')->nullable();
            $table->text('submission_text')->nullable();
            $table->string('attachment_url')->nullable();
            $table->decimal('marks_obtained', 5)->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['submitted', 'late', 'evaluated', 'returned'])->nullable()->default('submitted');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
