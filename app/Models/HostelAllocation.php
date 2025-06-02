<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelAllocation extends Model
{
    public $table = 'hostel_allocations';

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
        'student_id' => 'nullable',
        'hostel_id' => 'nullable',
        'room_id' => 'nullable',
        'bed_number' => 'nullable',
        'allocation_date' => 'required',
        'vacating_date' => 'nullable',
        'status' => 'nullable|string',
        'academic_year_id' => 'nullable',
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
}
