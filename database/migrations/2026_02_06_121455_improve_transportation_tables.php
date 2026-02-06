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
        // 1. Improve Routes Table
        Schema::table('routes', function (Blueprint $table) {
            $table->string('route_code', 20)->nullable()->after('name');
            $table->string('vehicle_name', 100)->nullable();
            $table->string('vehicle_number', 50)->nullable();
            $table->integer('vehicle_capacity')->default(0);
            $table->string('driver_name', 100)->nullable();
            $table->string('driver_contact', 20)->nullable();
            $table->string('conductor_name', 100)->nullable();
            $table->string('conductor_contact', 20)->nullable();
            $table->time('morning_start_time')->nullable();
            $table->time('morning_end_time')->nullable();
            $table->time('evening_start_time')->nullable();
            $table->time('evening_end_time')->nullable();
            $table->decimal('route_fee', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->integer('academic_year_id')->nullable();
        });

        // 2. Improve Route Stops Table
        Schema::table('route_stops', function (Blueprint $table) {
            $table->string('landmark', 255)->nullable()->after('stop_name');
            $table->decimal('stop_fee', 10, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
        });

        // 3. Create Student Transport Assignment Table (NEW)
        Schema::create('student_transport_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->integer('student_id');
            $table->integer('route_id');
            $table->integer('pickup_stop_id')->nullable();
            $table->integer('drop_stop_id')->nullable();
            $table->integer('academic_year_id')->nullable();
            $table->date('assigned_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // Adding logical indexes if they didn't exist in registrations
            $table->index('student_id');
            $table->index('route_id');
            $table->index('pickup_stop_id');
            $table->index('drop_stop_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_transport_assignments');

        Schema::table('route_stops', function (Blueprint $table) {
            $table->dropColumn(['landmark', 'stop_fee', 'status']);
        });

        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn([
                'route_code', 'vehicle_name', 'vehicle_number', 'vehicle_capacity',
                'driver_name', 'driver_contact', 'conductor_name', 'conductor_contact',
                'morning_start_time', 'morning_end_time', 'evening_start_time', 'evening_end_time',
                'route_fee', 'status', 'academic_year_id'
            ]);
        });
    }
};
