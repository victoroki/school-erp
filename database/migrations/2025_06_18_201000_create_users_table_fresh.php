<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 50)->unique('username');
            $table->string('password');
            $table->string('email', 100)->unique('email');
            $table->enum('user_type', ['admin', 'teacher', 'student', 'parent', 'staff']);
            $table->boolean('is_active')->nullable()->default(true);
            $table->dateTime('email_verified_at')->nullable();
            $table->dateTime('last_login')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
