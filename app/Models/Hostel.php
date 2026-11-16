<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hostel extends Model
{
    public $table = 'hostels';
    protected $primaryKey = 'hostel_id';

    public $fillable = [
        'name',
        'type',
        'address',
        'warden_id',
        'capacity'
    ];

    protected $casts = [
        'name' => 'string',
        'type' => 'string',
        'address' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|string|max:100',
        'type' => 'required|in:boys,girls,co-ed',
        'address' => 'required|string|max:65535',
        'warden_id' => 'nullable|exists:staff,staff_id',
        'capacity' => 'required|integer|min:1',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function warden(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'warden_id');
    }

    public function hostelAllocations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\HostelAllocation::class, 'hostel_id');
    }

    public function hostelRooms(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\HostelRoom::class, 'hostel_id');
    }

    // Helper method to get current occupancy
    public function getCurrentOccupancy(): int
    {
        // Counted from the active allocation rows rather than the denormalised
        // room counter, so occupancy is never overstated.
        return $this->hostelAllocations()->where('status', 'active')->count();
    }

    /**
     * Beds actually available: the sum of the hostel's room capacities.
     *
     * `hostels.capacity` is a declared planning figure that regularly disagrees
     * with the rooms on the ground, so availability is always derived from the
     * rooms. Use capacityMismatch() to surface the difference.
     */
    public function getBedCapacity(): int
    {
        return (int) $this->hostelRooms()->sum('capacity');
    }

    // Helper method to get available capacity
    public function getAvailableCapacity(): int
    {
        return max(0, $this->getBedCapacity() - $this->getCurrentOccupancy());
    }

    // Helper method to check if hostel is fully booked
    public function isFullyBooked(): bool
    {
        return $this->getCurrentOccupancy() >= $this->getBedCapacity();
    }

    // Helper method to get occupancy percentage
    public function getOccupancyPercentage(): int
    {
        $capacity = $this->getBedCapacity();

        if ($capacity == 0) return 0;

        return (int) (($this->getCurrentOccupancy() / $capacity) * 100);
    }

    /**
     * How far the declared capacity sits from the rooms on the ground.
     * Positive means the rooms hold more beds than declared.
     */
    public function capacityMismatch(): int
    {
        return $this->getBedCapacity() - (int) $this->capacity;
    }
}
