<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    public $table = 'subjects';
    protected $primaryKey = 'subject_id';

    public $fillable = [
        'subject_code',
        'name',
        'description',
        'is_elective'
    ];

    protected $casts = [
        'subject_code' => 'string',
        'name' => 'string',
        'description' => 'string',
        'is_elective' => 'boolean'
    ];

    public static array $rules = [
        'subject_code' => 'required|string|max:20',
        'name' => 'required|string|max:100',
        'description' => 'nullable|string|max:65535',
        'is_elective' => 'nullable|boolean',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function assignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Assignment::class, 'subject_id');
    }

    public function classSubjects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClassSubject::class, 'subject_id');
    }

    public function examResults(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ExamResult::class, 'subject_id');
    }

    public function examSchedules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ExamSchedule::class, 'subject_id');
    }

    public function teacherSubjects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TeacherSubject::class, 'subject_id');
    }

    public function timetables(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Timetable::class, 'subject_id');
    }
}
