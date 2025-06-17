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
        Schema::table('staff_leaves', function (Blueprint $table) {
            $table->foreign(['staff_id'], 'staff_leaves_ibfk_1')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['leave_type_id'], 'staff_leaves_ibfk_2')->references(['leave_type_id'])->on('leave_types')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['approved_by'], 'staff_leaves_ibfk_3')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_leaves', function (Blueprint $table) {
            $table->dropForeign('staff_leaves_ibfk_1');
            $table->dropForeign('staff_leaves_ibfk_2');
            $table->dropForeign('staff_leaves_ibfk_3');
        });
    }
};
