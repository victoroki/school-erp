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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->integer('vehicle_id', true);
            $table->string('vehicle_number', 20)->unique('vehicle_number');
            $table->string('vehicle_type', 50);
            $table->string('model', 50)->nullable();
            $table->string('make', 50)->nullable();
            $table->integer('year')->nullable();
            $table->integer('seating_capacity');
            $table->integer('driver_id')->nullable()->index('driver_id');
            $table->enum('status', ['active', 'maintenance', 'inactive'])->nullable()->default('active');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
