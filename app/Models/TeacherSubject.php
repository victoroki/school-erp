<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSubject extends Model
{
    public $table = 'teacher_subjects';

    public $fillable = [
        'staff_id',
        'subject_id',
        'class_section_id',
        'academic_year_id'
    ];

    protected $casts = [
        
    ];

    public static array $rules = [
        'staff_id' => 'nullable',
        'subject_id' => 'nullable',
        'class_section_id' => 'nullable',
        'academic_year_id' => 'nullable'
    ];

    public function classSection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\ClassSection::class, 'class_section_id');
    }

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
    }

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Subject::class, 'subject_id');
    }
}
