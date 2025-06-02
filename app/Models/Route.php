<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    public $table = 'routes';

    public $fillable = [
        'name',
        'description',
        'start_point',
        'end_point',
        'distance'
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'start_point' => 'string',
        'end_point' => 'string',
        'distance' => 'decimal:2'
    ];

    public static array $rules = [
        'name' => 'required|string|max:100',
        'description' => 'nullable|string|max:65535',
        'start_point' => 'required|string|max:100',
        'end_point' => 'required|string|max:100',
        'distance' => 'nullable|numeric',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function routeStops(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\RouteStop::class, 'route_id');
    }

    public function transportAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TransportAssignment::class, 'route_id');
    }

    public function transportRegistrations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TransportRegistration::class, 'route_id');
    }
}
