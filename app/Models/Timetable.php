<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    public $table = 'timetable';
    protected $primaryKey = 'timetable_id';

    public $fillable = [
        'class_section_id',
        'day_of_week',
        'period_id',
        'subject_id',
        'teacher_id',
        'classroom_id',
        'academic_year_id'
    ];

    protected $casts = [
        'day_of_week' => 'string'
    ];

    public static array $rules = [
        'class_section_id' => 'required|integer|exists:class_sections,class_section_id',
        'day_of_week' => 'required|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        'period_id' => 'required|integer|exists:periods,period_id',
        'subject_id' => 'required|integer|exists:subjects,subject_id',
        'teacher_id' => 'required|integer|exists:staff,staff_id',
        'classroom_id' => 'required|integer|exists:classrooms,classroom_id',
        'academic_year_id' => 'required|integer|exists:academic_years,academic_year_id',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function period(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Period::class, 'period_id', 'period_id');
    }

    public function classroom(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Classroom::class, 'classroom_id');
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Subject::class, 'subject_id');
    }

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function classSection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\ClassSection::class, 'class_section_id');
    }

    public function teacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'teacher_id');
    }
}
