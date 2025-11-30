<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelAllocation extends Model
{
    public $table = 'hostel_allocations';
    protected $primaryKey = 'allocation_id';

    public $fillable = [
        'student_id',
        'hostel_id',
        'room_id',
        'bed_number',
        'allocation_date',
        'vacating_date',
        'status',
        'academic_year_id'
    ];

    protected $casts = [
        'allocation_date' => 'date',
        'vacating_date' => 'date',
        'status' => 'string'
    ];

    public static array $rules = [
        'student_id' => 'required|exists:students,id',
        'hostel_id' => 'required|exists:hostels,hostel_id',
        'room_id' => 'required|exists:hostel_rooms,room_id',
        'bed_number' => 'nullable|integer|min:1',
        'allocation_date' => 'required|date',
        'vacating_date' => 'nullable|date|after:allocation_date',
        'status' => 'nullable|in:active,vacated,pending',
        'academic_year_id' => 'nullable|exists:academic_years,id',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\HostelRoom::class, 'room_id');
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function hostel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Hostel::class, 'hostel_id');
    }

    public function hostelFees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\HostelFee::class, 'allocation_id');
    }

    // Helper method to check if allocation is active
    public function isActive(): bool
    {
        return $this->status === 'active' && (is_null($this->vacating_date) || $this->vacating_date->isFuture());
    }
}
