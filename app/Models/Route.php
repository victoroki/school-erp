<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    public $table = 'routes';
    
    protected $primaryKey = 'route_id';

    public $fillable = [
        'name',
        'route_code',
        'description',
        'start_point',
        'end_point',
        'distance',
        'vehicle_name',
        'vehicle_number',
        'vehicle_capacity',
        'driver_name',
        'driver_contact',
        'conductor_name',
        'conductor_contact',
        'morning_start_time',
        'morning_end_time',
        'evening_start_time',
        'evening_end_time',
        'route_fee',
        'status',
        'academic_year_id'
    ];

    protected $casts = [
        'name' => 'string',
        'route_code' => 'string',
        'description' => 'string',
        'start_point' => 'string',
        'end_point' => 'string',
        'distance' => 'decimal:2',
        'vehicle_name' => 'string',
        'vehicle_number' => 'string',
        'vehicle_capacity' => 'integer',
        'driver_name' => 'string',
        'driver_contact' => 'string',
        'conductor_name' => 'string',
        'conductor_contact' => 'string',
        'morning_start_time' => 'string',
        'morning_end_time' => 'string',
        'evening_start_time' => 'string',
        'evening_end_time' => 'string',
        'route_fee' => 'decimal:2',
        'status' => 'string',
        'academic_year_id' => 'integer'
    ];

    public static array $rules = [
        'name' => 'required|string|max:100',
        'route_code' => 'nullable|string|max:20',
        'description' => 'nullable|string|max:65535',
        'start_point' => 'required|string|max:100',
        'end_point' => 'required|string|max:100',
        'distance' => 'nullable|numeric',
        'vehicle_name' => 'nullable|string|max:100',
        'vehicle_number' => 'nullable|string|max:50',
        'vehicle_capacity' => 'nullable|integer',
        'driver_name' => 'nullable|string|max:100',
        'driver_contact' => 'nullable|string|max:20',
        'conductor_name' => 'nullable|string|max:100',
        'conductor_contact' => 'nullable|string|max:20',
        'morning_start_time' => 'nullable',
        'morning_end_time' => 'nullable',
        'evening_start_time' => 'nullable',
        'evening_end_time' => 'nullable',
        'route_fee' => 'nullable|numeric',
        'status' => 'nullable|in:active,inactive,maintenance',
        'academic_year_id' => 'nullable|integer'
    ];

    public function routeStops(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\RouteStop::class, 'route_id')->orderBy('sequence');
    }

    public function studentAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentTransportAssignment::class, 'route_id');
    }

    public function getCurrentOccupancy()
    {
        return $this->studentAssignments()->where('status', 'active')->count();
    }

    public function getOccupancyPercentage()
    {
        if ($this->vehicle_capacity <= 0) return 0;
        return round(($this->getCurrentOccupancy() / $this->vehicle_capacity) * 100);
    }
}