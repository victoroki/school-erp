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
        Schema::create('staff', function (Blueprint $table) {
            $table->integer('staff_id', true);
            $table->integer('user_id')->nullable()->unique('user_id');
            $table->string('employee_id', 20)->unique('employee_id');
            $table->string('first_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('joining_date');
            $table->integer('department_id')->nullable()->index('department_id');
            $table->string('designation', 100);
            $table->string('qualification')->nullable();
            $table->float('experience', null, 0)->nullable();
            $table->string('email', 100)->unique('email');
            $table->string('phone', 20);
            $table->text('address');
            $table->string('city', 50);
            $table->string('country', 50);
            $table->string('photo_url')->nullable();
            $table->enum('staff_type', ['teaching', 'non-teaching', 'administration']);
            $table->enum('status', ['active', 'inactive', 'on_leave'])->nullable()->default('active');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
