<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('exam_schedules', 'duration_minutes')) {
            Schema::table('exam_schedules', function (Blueprint $table) {
                $table->unsignedInteger('duration_minutes')->default(120)->after('end_time');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('exam_schedules', 'duration_minutes')) {
            Schema::table('exam_schedules', function (Blueprint $table) {
                $table->dropColumn('duration_minutes');
            });
        }
    }
};
