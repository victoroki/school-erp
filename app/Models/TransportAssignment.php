<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportAssignment extends Model
{
    public $table = 'transport_assignments';
    
    protected $primaryKey = 'assignment_id';

    public $fillable = [
        'route_id',
        'vehicle_id',
        'driver_id',
        'assistant_id',
        'departure_time',
        'return_time',
        'status'
    ];

    protected $casts = [
        'route_id' => 'integer',
        'vehicle_id' => 'integer',
        'driver_id' => 'integer',
        'assistant_id' => 'integer',
        'departure_time' => 'time',
        'return_time' => 'time',
        'status' => 'string'
    ];

    public static array $rules = [
        'route_id' => 'nullable|exists:routes,route_id',
        'vehicle_id' => 'nullable|exists:vehicles,vehicle_id',
        'driver_id' => 'nullable|exists:staff,staff_id',
        'assistant_id' => 'nullable|exists:staff,staff_id',
        'departure_time' => 'required',
        'return_time' => 'nullable',
        'status' => 'nullable|in:active,inactive',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    /**
     * Get the route that owns the transport assignment.
     */
    public function route(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Route::class, 'route_id', 'route_id');
    }

    /**
     * Get the vehicle that owns the transport assignment.
     */
    public function vehicle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Vehicle::class, 'vehicle_id', 'vehicle_id');
    }

    /**
     * Get the driver (staff) that owns the transport assignment.
     */
    public function driver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'driver_id', 'staff_id');
    }

    /**
     * Get the assistant (staff) that owns the transport assignment.
     */
    public function assistant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'assistant_id', 'staff_id');
    }
}