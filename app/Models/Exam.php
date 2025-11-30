<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    public $table = 'exams';
    
    protected $primaryKey = 'exam_id';

    public $fillable = [
        'exam_type_id',
        'name',
        'description',
        'academic_year_id',
        'start_date',
        'end_date',
        'publish_result'
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'start_date' => 'date',
        'end_date' => 'date',
        'publish_result' => 'boolean'
    ];

    public static array $rules = [
        'exam_type_id' => 'nullable',
        'name' => 'required|string|max:100',
        'description' => 'nullable|string|max:65535',
        'academic_year_id' => 'nullable',
        'start_date' => 'required',
        'end_date' => 'required',
        'publish_result' => 'nullable|boolean',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function examType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\ExamType::class, 'exam_type_id');
    }

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function examResults(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ExamResult::class, 'exam_id');
    }

    public function examSchedules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ExamSchedule::class, 'exam_id');
    }
}
