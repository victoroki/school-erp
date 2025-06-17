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
        Schema::create('hostel_fee', function (Blueprint $table) {
            $table->integer('fee_id', true);
            $table->integer('student_id')->nullable()->index('student_id');
            $table->integer('allocation_id')->nullable()->index('allocation_id');
            $table->decimal('amount', 10);
            $table->decimal('paid_amount', 10)->nullable()->default(0);
            $table->date('due_date');
            $table->enum('status', ['paid', 'unpaid', 'partially_paid'])->nullable()->default('unpaid');
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
        Schema::dropIfExists('hostel_fee');
    }
};
