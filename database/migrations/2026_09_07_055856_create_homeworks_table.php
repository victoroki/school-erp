<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homeworks', function (Blueprint $table) {
            $table->id();
            // users.id is signed INT on this schema (integer(id,true) on
            // MariaDB) — foreignId()/unsignedInteger break the FK check.
            $table->integer('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('subject')->nullable();
            $table->string('class_name')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homeworks');
    }
};