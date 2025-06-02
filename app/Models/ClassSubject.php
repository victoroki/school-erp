<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    public $table = 'class_subjects';

    public $fillable = [
        'class_id',
        'subject_id',
        'academic_year_id'
    ];

    protected $casts = [
        
    ];

    public static array $rules = [
        'class_id' => 'nullable',
        'subject_id' => 'nullable',
        'academic_year_id' => 'nullable'
    ];

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function class(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Class::class, 'class_id');
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Subject::class, 'subject_id');
    }
}
