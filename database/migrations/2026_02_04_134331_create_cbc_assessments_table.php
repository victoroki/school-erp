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
        Schema::create('cbc_assessments', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id'); // Match type in students table
            $table->unsignedBigInteger('learning_area_id');
            $table->unsignedBigInteger('strand_id')->nullable();
            $table->unsignedBigInteger('sub_strand_id')->nullable();
            $table->tinyInteger('rating'); // 1=BE, 2=AE, 3=ME, 4=EE
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('assessed_by')->nullable();
            $table->date('assessment_date');
            $table->timestamps();

            // Indexes for speed if constraints fail
            $table->index('student_id');
            $table->index('learning_area_id');
            
            // Re-attempting constraints with matched types
            // If it still fails, I'll comment them out
            // $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbc_assessments');
    }
};
