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
        if (!Schema::hasTable('academic_events')) {
            Schema::create('academic_events', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('event_type', ['term_start', 'term_end', 'holiday', 'exam', 'sport', 'parent_meeting', 'other'])->default('other');
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->string('event_color')->default('#17a2b8');
                $table->boolean('is_public')->default(true);
                $table->unsignedInteger('academic_year_id');
                $table->foreign('academic_year_id')->references('academic_year_id')->on('academic_years')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_events');
    }
};
