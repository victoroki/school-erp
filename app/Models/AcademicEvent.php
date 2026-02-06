<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicEvent extends Model
{
    public $table = 'academic_events';

    public $fillable = [
        'title',
        'description',
        'event_type',
        'start_date',
        'end_date',
        'event_color',
        'is_public',
        'academic_year_id'
    ];

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
        'event_type' => 'string',
        'start_date' => 'date',
        'end_date' => 'date',
        'event_color' => 'string',
        'is_public' => 'boolean',
        'academic_year_id' => 'integer'
    ];

    public static array $rules = [
        'title' => 'required|string|max:255',
        'event_type' => 'required|string',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'academic_year_id' => 'required|exists:academic_years,academic_year_id'
    ];

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }
}
