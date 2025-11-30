<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentClassEnrollment extends Model
{
    public $table = 'student_class_enrollment';
    protected $primaryKey = 'enrollment_id';
    public $timestamps = false;

    public $fillable = [
        'student_id',
        'class_section_id',
        'roll_number',
        'academic_year_id',
        'enrollment_date',
        'status'
    ];

    protected $casts = [
        'roll_number' => 'string',
        'enrollment_date' => 'date',
        'status' => 'string'
    ];

    public static array $rules = [
        'student_id' => 'required|exists:students,student_id',
        'class_section_id' => 'required|exists:class_sections,class_section_id',
        'roll_number' => 'nullable|string|max:20',
        'academic_year_id' => 'required|exists:academic_years,academic_year_id',
        'enrollment_date' => 'required|date',
        'status' => 'required|in:active,transferred,completed,dropped'
    ];

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function classSection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\ClassSection::class, 'class_section_id');
    }
}
