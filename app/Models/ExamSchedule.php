<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
    public $table = 'exam_schedules';
    
    protected $primaryKey = 'schedule_id';

    public $fillable = [
        'exam_id',
        'class_id',
        'subject_id',
        'exam_date',
        'start_time',
        'end_time',
        'room_id',
        'max_marks',
        'passing_marks'
    ];

    protected $casts = [
        'exam_date' => 'date',
        'max_marks' => 'decimal:2',
        'passing_marks' => 'decimal:2'
    ];

    public static array $rules = [
        'exam_id' => 'nullable',
        'class_id' => 'nullable',
        'subject_id' => 'nullable',
        'exam_date' => 'required',
        'start_time' => 'required',
        'end_time' => 'required',
        'room_id' => 'nullable',
        'max_marks' => 'required|numeric',
        'passing_marks' => 'required|numeric',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function exam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Exam::class, 'exam_id');
    }

    public function room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Classroom::class, 'room_id');
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
