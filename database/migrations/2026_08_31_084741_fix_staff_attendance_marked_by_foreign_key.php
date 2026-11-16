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
        Schema::table('staff_attendance', function (Blueprint $table) {
            // Drop the old FK that pointed marked_by to the staff table and
            // re-point it at the users table (the controller stores Auth::id()).
            try {
                $table->dropForeign('staff_attendance_ibfk_2');
            } catch (\Throwable $e) {
                // FK may not exist; ignore
            }
        });

        if (Schema::hasColumn('staff_attendance', 'marked_by')) {
            Schema::table('staff_attendance', function (Blueprint $table) {
                $table->foreign('marked_by', 'staff_attendance_ibfk_2')->references('id')->on('users')->onUpdate('restrict')->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_attendance', function (Blueprint $table) {
            try {
                $table->dropForeign('staff_attendance_ibfk_2');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
};
