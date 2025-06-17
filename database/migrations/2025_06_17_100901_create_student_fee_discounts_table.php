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
        Schema::create('student_fee_discounts', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('student_id')->nullable()->index('student_id');
            $table->integer('discount_id')->nullable()->index('discount_id');
            $table->integer('academic_year_id')->nullable()->index('academic_year_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fee_discounts');
    }
};
