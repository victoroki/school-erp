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
        Schema::table('hostel_rooms', function (Blueprint $table) {
            $table->text('maintenance_notes')->nullable()->after('status');
        });

        Schema::table('hostel_allocations', function (Blueprint $table) {
            $table->text('checkout_notes')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hostel_rooms', function (Blueprint $table) {
            $table->dropColumn('maintenance_notes');
        });

        Schema::table('hostel_allocations', function (Blueprint $table) {
            $table->dropColumn('checkout_notes');
        });
    }
};
