<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelRoom extends Model
{
    public $table = 'hostel_rooms';

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
        'hostel_id' => 'nullable',
        'room_number' => 'required|string|max:20',
        'room_type' => 'required|string',
        'capacity' => 'required',
        'occupied' => 'nullable',
        'floor' => 'nullable|string|max:20',
        'status' => 'nullable|string',
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
}
