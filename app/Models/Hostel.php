<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hostel extends Model
{
    public $table = 'hostels';

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
        'type' => 'required|string',
        'address' => 'required|string|max:65535',
        'warden_id' => 'nullable',
        'capacity' => 'required',
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
}
