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
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->foreign(['item_id'], 'inventory_transactions_ibfk_1')->references(['item_id'])->on('inventory_items')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['handled_by'], 'inventory_transactions_ibfk_2')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropForeign('inventory_transactions_ibfk_1');
            $table->dropForeign('inventory_transactions_ibfk_2');
        });
    }
};
