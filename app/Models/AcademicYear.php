<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    public $table = 'academic_years';
    
    protected $primaryKey = 'academic_year_id';

    public $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_current'
    ];

    protected $casts = [
        'name' => 'string',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean'
    ];

    public static array $rules = [
        'name' => 'required|string|max:50|unique:academic_years,name',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'is_current' => 'nullable|boolean',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function classSections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClassSection::class, 'academic_year_id');
    }

    public function classSubjects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClassSubject::class, 'academic_year_id');
    }

    public function examTypes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\ExamType::class, 'exams');
    }

    public function feeStructures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\FeeStructure::class, 'academic_year_id');
    }

    public function hostelAllocations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\HostelAllocation::class, 'academic_year_id');
    }

    public function hostelFees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\HostelFee::class, 'academic_year_id');
    }

    public function studentClassEnrollments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentClassEnrollment::class, 'academic_year_id');
    }

    public function studentFeeDiscounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentFeeDiscount::class, 'academic_year_id');
    }

    public function teacherSubjects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TeacherSubject::class, 'academic_year_id');
    }

    public function timetables(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Timetable::class, 'academic_year_id');
    }

    public function transportRegistrations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TransportRegistration::class, 'academic_year_id');
    }
}
