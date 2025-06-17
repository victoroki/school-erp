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
        Schema::create('staff_salary', function (Blueprint $table) {
            $table->integer('salary_id', true);
            $table->integer('staff_id')->nullable()->index('staff_id');
            $table->decimal('basic_salary', 10);
            $table->decimal('allowances', 10)->nullable()->default(0);
            $table->decimal('deductions', 10)->nullable()->default(0);
            $table->decimal('net_salary', 10);
            $table->date('effective_from');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_salary');
    }
};
