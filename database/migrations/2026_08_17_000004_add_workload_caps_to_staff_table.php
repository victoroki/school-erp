<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_periods_per_day')->nullable()->after('work_schedule');
            $table->unsignedTinyInteger('max_periods_per_week')->nullable()->after('max_periods_per_day');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['max_periods_per_day', 'max_periods_per_week']);
        });
    }
};
