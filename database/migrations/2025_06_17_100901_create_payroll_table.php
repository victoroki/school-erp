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
        Schema::create('payroll', function (Blueprint $table) {
            $table->integer('payroll_id', true);
            $table->integer('staff_id')->nullable()->index('staff_id');
            $table->integer('salary_id')->nullable()->index('salary_id');
            $table->integer('month');
            $table->integer('year');
            $table->integer('working_days');
            $table->integer('paid_days');
            $table->integer('absent_days');
            $table->integer('leave_days');
            $table->decimal('basic_salary', 10);
            $table->decimal('allowances', 10)->nullable()->default(0);
            $table->decimal('overtime', 10)->nullable()->default(0);
            $table->decimal('gross_salary', 10);
            $table->decimal('deductions', 10)->nullable()->default(0);
            $table->decimal('net_salary', 10);
            $table->date('payment_date')->nullable();
            $table->enum('payment_method', ['cash', 'check', 'bank_transfer']);
            $table->string('reference_number', 100)->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'paid', 'canceled'])->nullable()->default('pending');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll');
    }
};
