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
        Schema::table('transport_assignments', function (Blueprint $table) {
            $table->foreign(['route_id'], 'transport_assignments_ibfk_1')->references(['route_id'])->on('routes')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['vehicle_id'], 'transport_assignments_ibfk_2')->references(['vehicle_id'])->on('vehicles')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['driver_id'], 'transport_assignments_ibfk_3')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['assistant_id'], 'transport_assignments_ibfk_4')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transport_assignments', function (Blueprint $table) {
            $table->dropForeign('transport_assignments_ibfk_1');
            $table->dropForeign('transport_assignments_ibfk_2');
            $table->dropForeign('transport_assignments_ibfk_3');
            $table->dropForeign('transport_assignments_ibfk_4');
        });
    }
};
