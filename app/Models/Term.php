<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    public $table = 'terms';

    public $fillable = [
        'academic_year_id',
        'name',
        'code',
        'start_date',
        'end_date',
        'fee_due_date',
        'status',
        'display_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'fee_due_date' => 'date',
        'display_order' => 'integer',
    ];

    public static array $rules = [
        'academic_year_id' => 'required|exists:academic_years,academic_year_id',
        'name' => 'required|string|max:100',
        'code' => 'required|string|max:20',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'fee_due_date' => 'nullable|date',
        'status' => 'required|in:upcoming,active,completed',
        'display_order' => 'nullable|integer',
    ];

    public function academicYear()
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function feeStructures()
    {
        return $this->hasMany(\App\Models\FeeStructure::class, 'term', 'code');
    }

    public function feeAssignments()
    {
        return $this->hasMany(\App\Models\StudentFeeAssignment::class, 'term_id');
    }

    public function isCurrentAttribute()
    {
        return $this->status === 'active';
    }

    public function scopeForCurrentAcademicYear($query)
    {
        return $query->whereHas('academicYear', function ($q) {
            $q->where('is_current', true);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('start_date');
    }
}
