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
        Schema::create('student_fees', function (Blueprint $table) {
            $table->integer('student_fee_id', true);
            $table->integer('student_id')->nullable()->index('student_id');
            $table->integer('fee_structure_id')->nullable()->index('fee_structure_id');
            $table->decimal('amount', 10);
            $table->decimal('discount_amount', 10)->nullable()->default(0);
            $table->decimal('final_amount', 10);
            $table->date('due_date');
            $table->enum('status', ['paid', 'partially_paid', 'unpaid', 'waived'])->nullable()->default('unpaid');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};
