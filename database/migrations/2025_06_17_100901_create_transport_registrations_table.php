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
        Schema::create('transport_registrations', function (Blueprint $table) {
            $table->integer('registration_id', true);
            $table->integer('student_id')->nullable()->index('student_id');
            $table->integer('route_id')->nullable()->index('route_id');
            $table->integer('stop_id')->nullable()->index('stop_id');
            $table->decimal('fee_amount', 10);
            $table->enum('payment_status', ['paid', 'unpaid', 'partially_paid'])->nullable()->default('unpaid');
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
        Schema::dropIfExists('transport_registrations');
    }
};
