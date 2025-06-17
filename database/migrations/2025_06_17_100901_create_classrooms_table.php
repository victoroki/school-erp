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
        Schema::create('classrooms', function (Blueprint $table) {
            $table->integer('classroom_id', true);
            $table->string('room_number', 20)->unique('room_number');
            $table->string('building', 50)->nullable();
            $table->integer('floor')->nullable();
            $table->integer('capacity');
            $table->boolean('has_sockets')->nullable()->default(false);
            $table->boolean('has_whiteboard')->nullable()->default(false);
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
