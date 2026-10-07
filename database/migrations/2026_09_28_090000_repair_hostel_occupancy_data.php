<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repairs existing hostel data in place. No schema change: the enum values are
 * correct, the code that wrote them was not.
 *
 *  1. Any room status outside available|full|under_maintenance (for example the
 *     "partial" value the old transfer/room code tried to write) is normalised
 *     from its occupancy, and a room with free beds is set back to available.
 *  2. occupied is recomputed from the active allocation rows so the counter
 *     cannot drift from the beds that are actually held.
 *  3. Active allocations without a bed number are given the lowest free bed in
 *     their room, so the bed column stops reading "N/A" and stops colliding.
 *
 * down() is a no-op: the repair only restores values the database already
 * allowed, and rolling it back would reintroduce the inconsistency.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('hostel_rooms') || !Schema::hasTable('hostel_allocations')) {
            return;
        }

        DB::transaction(function () {
            $rooms = DB::table('hostel_rooms')->get(['room_id', 'capacity', 'occupied', 'status']);

            foreach ($rooms as $room) {
                $activeCount = DB::table('hostel_allocations')
                    ->where('room_id', $room->room_id)
                    ->where('status', 'active')
                    ->count();

                $status = match ($room->status) {
                    'under_maintenance' => 'under_maintenance',
                    default => (int) $room->capacity > 0 && $activeCount >= (int) $room->capacity
                        ? 'full'
                        : 'available',
                };

                $needsBedRepair = (int) $room->occupied !== $activeCount
                    || !in_array($room->status, ['available', 'full', 'under_maintenance'], true);

                if ($needsBedRepair) {
                    DB::table('hostel_rooms')
                        ->where('room_id', $room->room_id)
                        ->update(['occupied' => $activeCount, 'status' => $status]);
                }

                $this->backfillBeds($room->room_id, (int) $room->capacity);
            }
        });
    }

    /**
     * Give every active allocation without a bed number the lowest free bed.
     */
    private function backfillBeds(int $roomId, int $capacity): void
    {
        $allocations = DB::table('hostel_allocations')
            ->where('room_id', $roomId)
            ->where('status', 'active')
            ->orderBy('allocation_id')
            ->get(['allocation_id', 'bed_number']);

        $used = $allocations
            ->filter(fn ($allocation) => $allocation->bed_number !== null)
            ->map(fn ($allocation) => (int) $allocation->bed_number)
            ->all();

        foreach ($allocations as $allocation) {
            if ($allocation->bed_number !== null) {
                continue;
            }

            $bed = null;
            for ($candidate = 1; $candidate <= $capacity; $candidate++) {
                if (!in_array($candidate, $used, true)) {
                    $bed = $candidate;
                    break;
                }
            }

            if ($bed === null) {
                // More residents than beds: leave the bed unnumbered rather than
                // inventing a duplicate.
                continue;
            }

            DB::table('hostel_allocations')
                ->where('allocation_id', $allocation->allocation_id)
                ->update(['bed_number' => $bed]);

            $used[] = $bed;
        }
    }

    public function down(): void
    {
        // Intentionally empty: see the class docblock.
    }
};
