<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a vehicle's driver be typed in as free text.
 *
 * The create/edit form offered a dropdown of `staff` records with
 * `staff_type = 'driver'`, but that value does not exist in the staff_type
 * list, so the select was always empty and there was no way to record a driver
 * at all. `driver_name` holds whatever the user types.
 *
 * `driver_id` is deliberately left in place: it still links to a staff record
 * for any vehicle that was assigned a real driver, and it is backfilled into
 * `driver_name` here so no existing information is lost.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vehicles') || Schema::hasColumn('vehicles', 'driver_name')) {
            return;
        }

        Schema::table('vehicles', function ($table) {
            $table->string('driver_name')->nullable()->after('driver_id');
        });

        // Carry over the name of any vehicle already linked to a staff driver.
        if (Schema::hasTable('staff')) {
            DB::statement(
                "UPDATE vehicles v
                 JOIN staff s ON s.staff_id = v.driver_id
                 SET v.driver_name = TRIM(CONCAT(s.first_name, ' ', s.last_name))
                 WHERE v.driver_id IS NOT NULL AND v.driver_name IS NULL"
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('vehicles') || !Schema::hasColumn('vehicles', 'driver_name')) {
            return;
        }

        Schema::table('vehicles', function ($table) {
            $table->dropColumn('driver_name');
        });
    }
};
