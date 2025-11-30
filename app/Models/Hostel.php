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
        'warden_id' => 'nullable|exists:staff,id',
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
        return $this->hostelRooms()->sum('occupied');
    }

    // Helper method to get available capacity
    public function getAvailableCapacity(): int
    {
        return max(0, $this->capacity - $this->getCurrentOccupancy());
    }

    // Helper method to check if hostel is fully booked
    public function isFullyBooked(): bool
    {
        return $this->getCurrentOccupancy() >= $this->capacity;
    }

    // Helper method to get occupancy percentage
    public function getOccupancyPercentage(): int
    {
        if ($this->capacity == 0) return 0;
        return (int) (($this->getCurrentOccupancy() / $this->capacity) * 100);
    }
}
