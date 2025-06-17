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
        Schema::table('hostel_fee', function (Blueprint $table) {
            $table->foreign(['student_id'], 'hostel_fee_ibfk_1')->references(['student_id'])->on('students')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['allocation_id'], 'hostel_fee_ibfk_2')->references(['allocation_id'])->on('hostel_allocations')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['academic_year_id'], 'hostel_fee_ibfk_3')->references(['academic_year_id'])->on('academic_years')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hostel_fee', function (Blueprint $table) {
            $table->dropForeign('hostel_fee_ibfk_1');
            $table->dropForeign('hostel_fee_ibfk_2');
            $table->dropForeign('hostel_fee_ibfk_3');
        });
    }
};
