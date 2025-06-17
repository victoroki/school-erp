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
        Schema::table('transport_registrations', function (Blueprint $table) {
            $table->foreign(['student_id'], 'transport_registrations_ibfk_1')->references(['student_id'])->on('students')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['route_id'], 'transport_registrations_ibfk_2')->references(['route_id'])->on('routes')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['stop_id'], 'transport_registrations_ibfk_3')->references(['stop_id'])->on('route_stops')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['academic_year_id'], 'transport_registrations_ibfk_4')->references(['academic_year_id'])->on('academic_years')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_registrations', function (Blueprint $table) {
            $table->dropForeign('transport_registrations_ibfk_1');
            $table->dropForeign('transport_registrations_ibfk_2');
            $table->dropForeign('transport_registrations_ibfk_3');
            $table->dropForeign('transport_registrations_ibfk_4');
        });
    }
};
