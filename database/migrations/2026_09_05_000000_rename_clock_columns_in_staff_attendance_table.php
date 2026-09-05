<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The HR revamp migration (2026_02_07_000000_revamp_hr_module) left the
     * clock_in/clock_out rename commented out, so staff_attendance columns
     * never matched the StaffAttendance model (time_in/time_out) and every
     * save failed with "Column not found: time_in".
     */
    public function up(): void
    {
        Schema::table('staff_attendance', function (Blueprint $table) {
            if (Schema::hasColumn('staff_attendance', 'clock_in')) {
                $table->renameColumn('clock_in', 'time_in');
            }
            if (Schema::hasColumn('staff_attendance', 'clock_out')) {
                $table->renameColumn('clock_out', 'time_out');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_attendance', function (Blueprint $table) {
            if (Schema::hasColumn('staff_attendance', 'time_in')) {
                $table->renameColumn('time_in', 'clock_in');
            }
            if (Schema::hasColumn('staff_attendance', 'time_out')) {
                $table->renameColumn('time_out', 'clock_out');
            }
        });
    }
};