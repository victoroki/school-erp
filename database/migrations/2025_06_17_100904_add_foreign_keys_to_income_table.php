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
        Schema::table('income', function (Blueprint $table) {
            $table->foreign(['category_id'], 'income_ibfk_1')->references(['category_id'])->on('income_categories')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['received_by'], 'income_ibfk_2')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('income', function (Blueprint $table) {
            $table->dropForeign('income_ibfk_1');
            $table->dropForeign('income_ibfk_2');
        });
    }
};
