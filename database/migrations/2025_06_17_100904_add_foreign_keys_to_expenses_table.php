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
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreign(['category_id'], 'expenses_ibfk_1')->references(['category_id'])->on('expense_categories')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['approved_by'], 'expenses_ibfk_2')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['created_by'], 'expenses_ibfk_3')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign('expenses_ibfk_1');
            $table->dropForeign('expenses_ibfk_2');
            $table->dropForeign('expenses_ibfk_3');
        });
    }
};
