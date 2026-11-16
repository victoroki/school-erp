<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widen grade_point so national grading systems fit — Kenya's KCSE
     * uses points from 1 (E) up to 12 (A), which overflowed decimal(3,2).
     */
    public function up(): void
    {
        Schema::table('grading_scales', function (Blueprint $table) {
            $table->decimal('grade_point', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('grading_scales', function (Blueprint $table) {
            $table->decimal('grade_point', 3, 2)->nullable()->change();
        });
    }
};
