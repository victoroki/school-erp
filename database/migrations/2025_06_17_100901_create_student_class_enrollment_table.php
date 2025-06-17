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
        Schema::create('student_class_enrollment', function (Blueprint $table) {
            $table->integer('enrollment_id', true);
            $table->integer('student_id')->nullable()->index('student_id');
            $table->integer('class_section_id')->nullable()->index('class_section_id');
            $table->string('roll_number', 20)->nullable();
            $table->integer('academic_year_id')->nullable()->index('academic_year_id');
            $table->date('enrollment_date')->nullable();
            $table->enum('status', ['active', 'transferred', 'completed', 'dropped'])->nullable()->default('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_class_enrollment');
    }
};
