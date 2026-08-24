<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('term_weeks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('term_id');
            $table->integer('academic_year_id');
            $table->unsignedTinyInteger('week_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('label', 50)->nullable();
            $table->boolean('is_exam_week')->default(false);
            $table->boolean('is_half_term')->default(false);
            $table->timestamps();

            $table->foreign('term_id')->references('id')->on('terms')->onDelete('cascade');
            $table->foreign('academic_year_id')->references('academic_year_id')->on('academic_years')->onDelete('cascade');
            $table->unique(['term_id', 'week_number']);
            $table->index(['academic_year_id', 'term_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_weeks');
    }
};
