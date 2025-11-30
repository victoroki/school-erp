<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelRoom extends Model
{
    public $table = 'hostel_rooms';
    protected $primaryKey = 'room_id';

    public $fillable = [
        'hostel_id',
        'room_number',
        'room_type',
        'capacity',
        'occupied',
        'floor',
        'status'
    ];

    protected $casts = [
        'room_number' => 'string',
        'room_type' => 'string',
        'floor' => 'string',
        'status' => 'string'
    ];

    public static array $rules = [
        'hostel_id' => 'required|exists:hostels,hostel_id',
        'room_number' => 'required|string|max:20',
        'room_type' => 'required|in:single,double,triple,dormitory',
        'capacity' => 'required|integer|min:1',
        'occupied' => 'nullable|integer|min:0',
        'floor' => 'nullable|string|max:20',
        'status' => 'nullable|in:available,full,under_maintenance',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

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
        return max(0, $this->capacity - ($this->occupied ?? 0));
    }

    // Helper method to check if room is full
    public function isFull(): bool
    {
        return ($this->occupied ?? 0) >= $this->capacity;
    }

    // Helper method to get occupancy percentage
    public function getOccupancyPercentage(): int
    {
        if ($this->capacity == 0) return 0;
        return (int) ((($this->occupied ?? 0) / $this->capacity) * 100);
    }

    // Helper method to check if can allocate
    public function canAllocate(int $numberOfBeds = 1): bool
    {
        return $this->status === 'available' && $this->getAvailableBeds() >= $numberOfBeds;
    }
}
