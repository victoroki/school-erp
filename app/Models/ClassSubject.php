<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    public $table = 'class_subjects';
    protected $primaryKey = 'class_subject_id';
    public $timestamps = false; 

    public $fillable = [
        'class_id',
        'subject_id',
        'academic_year_id',
        'periods_per_week'
    ];

    protected $casts = [
        'periods_per_week' => 'integer',
    ];

    public static array $rules = [
        'class_id' => 'nullable',
        'subject_id' => 'nullable',
        'academic_year_id' => 'nullable',
        'periods_per_week' => 'nullable|integer|min:1|max:40'
    ];

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function class(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\SchoolClass::class, 'class_id');
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Subject::class, 'subject_id');
    }
}
