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
        Schema::create('student_siblings', function (Blueprint $table) {
            $table->id('sibling_id');
            $table->integer('student_id');
            $table->integer('sibling_student_id');
            $table->enum('relationship_type', ['brother', 'sister', 'half_brother', 'half_sister', 'step_brother', 'step_sister'])->default('brother');
            $table->boolean('is_twin')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
            $table->foreign('sibling_student_id')->references('student_id')->on('students')->onDelete('cascade');
            
            // Prevent duplicate sibling relationships
            $table->unique(['student_id', 'sibling_student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_siblings');
    }
};
