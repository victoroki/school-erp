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
        Schema::create('exams', function (Blueprint $table) {
            $table->integer('exam_id', true);
            $table->integer('exam_type_id')->nullable()->index('exam_type_id');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('academic_year_id')->nullable()->index('academic_year_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('publish_result')->nullable()->default(false);
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
