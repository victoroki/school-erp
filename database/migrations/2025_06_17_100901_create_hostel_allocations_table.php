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
        Schema::create('hostel_allocations', function (Blueprint $table) {
            $table->integer('allocation_id', true);
            $table->integer('student_id')->nullable()->index('student_id');
            $table->integer('hostel_id')->nullable()->index('hostel_id');
            $table->integer('room_id')->nullable()->index('room_id');
            $table->integer('bed_number')->nullable();
            $table->date('allocation_date');
            $table->date('vacating_date')->nullable();
            $table->enum('status', ['active', 'vacated', 'pending'])->nullable()->default('active');
            $table->integer('academic_year_id')->nullable()->index('academic_year_id');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_allocations');
    }
};
