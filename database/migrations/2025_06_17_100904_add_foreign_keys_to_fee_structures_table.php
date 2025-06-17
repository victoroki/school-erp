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
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->foreign(['academic_year_id'], 'fee_structures_ibfk_1')->references(['academic_year_id'])->on('academic_years')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['class_id'], 'fee_structures_ibfk_2')->references(['class_id'])->on('classes')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['category_id'], 'fee_structures_ibfk_3')->references(['category_id'])->on('fee_categories')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropForeign('fee_structures_ibfk_1');
            $table->dropForeign('fee_structures_ibfk_2');
            $table->dropForeign('fee_structures_ibfk_3');
        });
    }
};
