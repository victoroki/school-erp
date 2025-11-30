<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSection extends Model
{
    public $table = 'class_sections';
    protected $primaryKey = 'class_section_id';
    public $timestamps = false;

    public $fillable = [
        'academic_year_id',
        'class_id',
        'section_id',
        'classroom_id',
        'class_teacher_id'
    ];

    protected $casts = [

    ];

    public static array $rules = [
        'academic_year_id' => 'required|integer|exists:academic_years,academic_year_id',
        'class_id' => 'required|integer|exists:classes,class_id',
        'section_id' => 'required|integer|exists:sections,section_id',
        'classroom_id' => 'required|integer|exists:classrooms,classroom_id',
        'class_teacher_id' => 'required|integer|exists:staff,staff_id'
    ];

    public function class(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\SchoolClass::class, 'class_id');
    }

    public function schoolClass(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\SchoolClass::class, 'class_id', 'class_id');
    }

    public function classTeacher(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'class_teacher_id');
    }

    public function section(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Section::class, 'section_id');
    }

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function classroom(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Classroom::class, 'classroom_id');
    }

    public function assignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Assignment::class, 'class_section_id');
    }

    public function examResults(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ExamResult::class, 'class_section_id');
    }

    public function studentAttendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentAttendance::class, 'class_section_id');
    }

    public function studentClassEnrollments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentClassEnrollment::class, 'class_section_id');
    }

    public function teacherSubjects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TeacherSubject::class, 'class_section_id');
    }

    public function timetables(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Timetable::class, 'class_section_id');
    }

    // public function getClassAndSection (){
    //     $names = array_filter([
    //         $this->class_id,
    //         $this->section_id
    //     ]);

    //     return implode(' ', $names);
    //     dd($names);
    // }

}
