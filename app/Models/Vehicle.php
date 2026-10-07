<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    public $table = 'vehicles';
    
    protected $primaryKey = 'vehicle_id';

    public $fillable = [
        'vehicle_number',
        'vehicle_type',
        'model',
        'make',
        'year',
        'seating_capacity',
        'driver_name',
        'driver_id',
        'status',
        'insurance_expiry_date'
    ];

    protected $casts = [
        'vehicle_number' => 'string',
        'vehicle_type' => 'string',
        'model' => 'string',
        'make' => 'string',
        'year' => 'integer',
        'seating_capacity' => 'integer',
        'driver_name' => 'string',
        'driver_id' => 'integer',
        'status' => 'string',
        'insurance_expiry_date' => 'date'
    ];

    public static array $rules = [
        'vehicle_number' => 'required|string|max:20|unique:vehicles,vehicle_number',
        'vehicle_type' => 'required|string|max:50',
        'model' => 'nullable|string|max:50',
        'make' => 'nullable|string|max:50',
        'year' => 'nullable|integer',
        'seating_capacity' => 'required|integer|min:1',
        'driver_name' => 'nullable|string|max:100',
        'driver_id' => 'nullable|exists:staff,staff_id',
        'status' => 'nullable|in:active,maintenance,inactive',
        'insurance_expiry_date' => 'nullable|date',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    /**
     * The driver's name as it should be displayed.
     *
     * Prefers the free-text name typed on the form and falls back to the linked
     * staff record, so vehicles assigned a driver before this field existed
     * still show a name.
     */
    public function getDriverDisplayAttribute(): string
    {
        $name = trim((string) $this->driver_name);

        if ($name !== '') {
            return $name;
        }

        return trim((string) $this->driver?->full_name) ?: 'Not Assigned';
    }

    /**
     * Get the driver that owns the vehicle.
     */
    public function driver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'driver_id', 'staff_id');
    }

    /**
     * Get the transport assignments for the vehicle.
     */
    public function transportAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TransportAssignment::class, 'vehicle_id', 'vehicle_id');
    }

    /**
     * Check if the vehicle's insurance is expired.
     *
     * @return bool
     */
    public function isInsuranceExpired(): bool
    {
        if (!$this->insurance_expiry_date) {
            return false;
        }
        
        return \Carbon\Carbon::parse($this->insurance_expiry_date)->isPast();
    }
}