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
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->integer('room_id', true);
            $table->integer('hostel_id')->nullable()->index('hostel_id');
            $table->string('room_number', 20);
            $table->enum('room_type', ['single', 'double', 'triple', 'dormitory']);
            $table->integer('capacity');
            $table->integer('occupied')->nullable()->default(0);
            $table->string('floor', 20)->nullable();
            $table->enum('status', ['available', 'full', 'under_maintenance'])->nullable()->default('available');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hostel_rooms');
    }
};
