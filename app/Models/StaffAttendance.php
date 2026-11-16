<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    public $table = 'staff_attendance';
    protected $primaryKey = 'attendance_id';

    public $fillable = [
        'staff_id',
        'date',
        'status',
        'time_in',
        'time_out',
        'late_minutes',
        'overtime_hours',
        'notes',
        'marked_by',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'late_minutes' => 'integer',
        'overtime_hours' => 'decimal:2',
    ];

    public static array $rules = [
        'staff_id' => 'required|exists:staff,staff_id',
        'date' => 'required|date',
        'status' => 'required|in:present,absent,late,half_day,on_leave',
    ];

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'staff_id', 'staff_id');
    }

    public function markedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'marked_by');
    }
}
