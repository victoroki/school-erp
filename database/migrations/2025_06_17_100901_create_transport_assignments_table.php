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
        Schema::create('transport_assignments', function (Blueprint $table) {
            $table->integer('assignment_id', true);
            $table->integer('route_id')->nullable()->index('route_id');
            $table->integer('vehicle_id')->nullable()->index('vehicle_id');
            $table->integer('driver_id')->nullable()->index('driver_id');
            $table->integer('assistant_id')->nullable()->index('assistant_id');
            $table->time('departure_time');
            $table->time('return_time')->nullable();
            $table->enum('status', ['active', 'inactive'])->nullable()->default('active');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_assignments');
    }
};
