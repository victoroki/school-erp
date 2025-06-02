<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
    public $table = 'route_stops';

    public $fillable = [
        'route_id',
        'stop_name',
        'stop_time',
        'sequence'
    ];

    protected $casts = [
        'stop_name' => 'string'
    ];

    public static array $rules = [
        'route_id' => 'nullable',
        'stop_name' => 'required|string|max:100',
        'stop_time' => 'nullable',
        'sequence' => 'required',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function route(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Route::class, 'route_id');
    }

    public function transportRegistrations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TransportRegistration::class, 'stop_id');
    }
}
