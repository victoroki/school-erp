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
        Schema::create('students', function (Blueprint $table) {
            $table->integer('student_id', true);
            $table->integer('user_id')->nullable()->unique('user_id');
            $table->string('admission_no', 20)->unique('admission_no');
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('city', 50);
            $table->string('country', 50);
            $table->string('phone', 20)->nullable();
            $table->string('emergency_contact', 20);
            $table->date('admission_date');
            $table->string('photo_url')->nullable();
            $table->enum('status', ['active', 'inactive', 'alumni', 'transferred'])->nullable()->default('active');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
