<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class HostelRoom extends Model
{
    public $table = 'hostel_rooms';
    protected $primaryKey = 'room_id';

    /**
     * The complete set of values the hostel_rooms.status enum accepts.
     *
     * A room with free beds is `available`; the bed counts carry the
     * "partially occupied" detail. `under_maintenance` is the one manual state.
     */
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_FULL = 'full';
    public const STATUS_UNDER_MAINTENANCE = 'under_maintenance';

    public const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_FULL,
        self::STATUS_UNDER_MAINTENANCE,
    ];

    /**
     * `occupied` is intentionally absent: it is recalculated from the active
     * allocation rows by HostelAllocationService and must never be set from
     * form input, otherwise the counter drifts away from reality.
     */
    public $fillable = [
        'hostel_id',
        'room_number',
        'room_type',
        'capacity',
        'floor',
        'status',
        'maintenance_notes'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'occupied' => 'integer',
        'room_number' => 'string',
        'room_type' => 'string',
        'floor' => 'string',
        'status' => 'string',
        'maintenance_notes' => 'string'
    ];

    public static array $rules = [
        'hostel_id' => 'required|exists:hostels,hostel_id',
        'room_number' => 'required|string|max:20',
        'room_type' => 'required|in:single,double,triple,dormitory',
        'capacity' => 'required|integer|min:1',
        'floor' => 'nullable|string|max:20',
        'status' => 'nullable|in:' . self::STATUS_AVAILABLE . ',' . self::STATUS_FULL . ',' . self::STATUS_UNDER_MAINTENANCE,
        'maintenance_notes' => 'nullable|string',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    /**
     * Guard the enum at the model, not just in the form request.
     *
     * The original "Data truncated for column 'status'" bug happened because a
     * code path wrote a value the enum does not accept, and validation only ran
     * on HTTP input. Checking on `saving` means no write path — fill, forceFill,
     * relationship set or a mass update through the model — can store a status
     * the database would silently truncate.
     */
    protected static function booted(): void
    {
        static::saving(function (HostelRoom $room) {
            $status = $room->status;

            if ($status !== null && !in_array($status, self::STATUSES, true)) {
                throw ValidationException::withMessages([
                    'status' => sprintf(
                        'Room status must be one of: %s. "%s" is not a valid value.',
                        implode(', ', self::STATUSES),
                        $status
                    ),
                ]);
            }
        });
    }

    public function hostel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Hostel::class, 'hostel_id');
    }

    public function hostelAllocations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\HostelAllocation::class, 'room_id');
    }

    // Helper method to get available beds
    public function getAvailableBeds(): int
    {
        return max(0, (int) $this->capacity - (int) ($this->occupied ?? 0));
    }

    // Helper method to check if room is full
    public function isFull(): bool
    {
        return $this->getAvailableBeds() < 1;
    }

    // Helper method to check if room is out of service
    public function isUnderMaintenance(): bool
    {
        return $this->status === self::STATUS_UNDER_MAINTENANCE;
    }

    // Helper method to get occupancy percentage
    public function getOccupancyPercentage(): int
    {
        if ((int) $this->capacity == 0) return 0;
        return (int) (((int) ($this->occupied ?? 0) / (int) $this->capacity) * 100);
    }

    /**
     * Whether one more student can be given a bed in this room.
     *
     * Based on free beds rather than the stored status, so a room whose status
     * has drifted is still measured correctly.
     */
    public function canAllocate(int $numberOfBeds = 1): bool
    {
        return !$this->isUnderMaintenance() && $this->getAvailableBeds() >= $numberOfBeds;
    }
}
