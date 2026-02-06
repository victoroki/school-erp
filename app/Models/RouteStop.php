<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
    public $table = 'route_stops';
    
    protected $primaryKey = 'stop_id';

    public $fillable = [
        'route_id',
        'stop_name',
        'landmark',
        'stop_time',
        'sequence',
        'stop_fee',
        'status'
    ];

    protected $casts = [
        'stop_name' => 'string',
        'landmark' => 'string',
        'stop_fee' => 'decimal:2',
        'status' => 'string'
    ];

    public static array $rules = [
        'route_id' => 'required|integer',
        'stop_name' => 'required|string|max:100',
        'landmark' => 'nullable|string|max:255',
        'stop_time' => 'nullable',
        'sequence' => 'required|integer',
        'stop_fee' => 'nullable|numeric',
        'status' => 'nullable|in:active,inactive'
    ];

    public function route(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Route::class, 'route_id');
    }

    public function studentAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentTransportAssignment::class, 'pickup_stop_id');
    }

    public function getStudentCount()
    {
        return \App\Models\StudentTransportAssignment::where('pickup_stop_id', $this->stop_id)
            ->orWhere('drop_stop_id', $this->stop_id)
            ->where('status', 'active')
            ->count();
    }
}
