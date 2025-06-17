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
        Schema::table('staff', function (Blueprint $table) {
            $table->foreign(['user_id'], 'staff_ibfk_1')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['department_id'], 'staff_ibfk_2')->references(['department_id'])->on('departments')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign('staff_ibfk_1');
            $table->dropForeign('staff_ibfk_2');
        });
    }
};
