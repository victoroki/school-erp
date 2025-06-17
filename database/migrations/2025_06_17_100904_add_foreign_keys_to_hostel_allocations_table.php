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
        Schema::table('hostel_allocations', function (Blueprint $table) {
            $table->foreign(['student_id'], 'hostel_allocations_ibfk_1')->references(['student_id'])->on('students')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['hostel_id'], 'hostel_allocations_ibfk_2')->references(['hostel_id'])->on('hostels')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['room_id'], 'hostel_allocations_ibfk_3')->references(['room_id'])->on('hostel_rooms')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['academic_year_id'], 'hostel_allocations_ibfk_4')->references(['academic_year_id'])->on('academic_years')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hostel_allocations', function (Blueprint $table) {
            $table->dropForeign('hostel_allocations_ibfk_1');
            $table->dropForeign('hostel_allocations_ibfk_2');
            $table->dropForeign('hostel_allocations_ibfk_3');
            $table->dropForeign('hostel_allocations_ibfk_4');
        });
    }
};
