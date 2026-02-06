<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentTransportAssignment extends Model
{
    public $table = 'student_transport_assignments';
    
    protected $primaryKey = 'assignment_id';

    public $fillable = [
        'student_id',
        'route_id',
        'pickup_stop_id',
        'drop_stop_id',
        'academic_year_id',
        'assigned_date',
        'status'
    ];

    protected $casts = [
        'student_id' => 'integer',
        'route_id' => 'integer',
        'pickup_stop_id' => 'integer',
        'drop_stop_id' => 'integer',
        'academic_year_id' => 'integer',
        'assigned_date' => 'date',
        'status' => 'string'
    ];

    public static array $rules = [
        'student_id' => 'required|integer',
        'route_id' => 'required|integer',
        'pickup_stop_id' => 'nullable|integer',
        'drop_stop_id' => 'nullable|integer',
        'academic_year_id' => 'nullable|integer',
        'assigned_date' => 'nullable|date',
        'status' => 'nullable|in:active,inactive'
    ];

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function route(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Route::class, 'route_id');
    }

    public function pickupStop(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\RouteStop::class, 'pickup_stop_id');
    }

    public function dropStop(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\RouteStop::class, 'drop_stop_id');
    }

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }
}
