<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    public $table = 'exam_results';

    public $fillable = [
        'exam_id',
        'student_id',
        'class_section_id',
        'subject_id',
        'marks_obtained',
        'grade_id',
        'remarks',
        'created_by'
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'remarks' => 'string'
    ];

    public static array $rules = [
        'exam_id' => 'nullable',
        'student_id' => 'nullable',
        'class_section_id' => 'nullable',
        'subject_id' => 'nullable',
        'marks_obtained' => 'required|numeric',
        'grade_id' => 'nullable',
        'remarks' => 'nullable|string|max:65535',
        'created_by' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Subject::class, 'subject_id');
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function grade(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\GradingScale::class, 'grade_id');
    }

    public function classSection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\ClassSection::class, 'class_section_id');
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'created_by');
    }

    public function exam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Exam::class, 'exam_id');
    }
}
