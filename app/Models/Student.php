<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    public $table = 'students';

    public $fillable = [
        'user_id',
        'admission_no',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'city',
        'country',
        'phone',
        'emergency_contact',
        'admission_date',
        'photo_url',
        'status'
    ];

    protected $casts = [
        'admission_no' => 'string',
        'first_name' => 'string',
        'middle_name' => 'string',
        'last_name' => 'string',
        'date_of_birth' => 'date',
        'gender' => 'string',
        'city' => 'string',
        'country' => 'string',
        'phone' => 'string',
        'emergency_contact' => 'string',
        'admission_date' => 'date',
        'photo_url' => 'string',
        'status' => 'string'
    ];

    public static array $rules = [
        'user_id' => 'nullable',
        'admission_no' => 'required|string|max:20',
        'first_name' => 'required|string|max:50',
        'middle_name' => 'nullable|string|max:50',
        'last_name' => 'required|string|max:50',
        'date_of_birth' => 'required',
        'gender' => 'required|string',
        'city' => 'required|string|max:50',
        'country' => 'required|string|max:50',
        'phone' => 'nullable|string|max:20',
        'emergency_contact' => 'required|string|max:20',
        'admission_date' => 'required',
        'photo_url' => 'nullable|string|max:255',
        'status' => 'nullable|string',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function assignments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Assignment::class, 'assignment_submissions');
    }

    public function examResults(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ExamResult::class, 'student_id');
    }

    public function hostelAllocations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\HostelAllocation::class, 'student_id');
    }

    public function hostelFees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\HostelFee::class, 'student_id');
    }

    public function studentAttendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentAttendance::class, 'student_id');
    }

    public function studentClassEnrollments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentClassEnrollment::class, 'student_id');
    }

    public function studentDocuments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentDocument::class, 'student_id');
    }

    public function feeStructures(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\FeeStructure::class, 'student_fees');
    }

    public function studentFeeDiscounts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentFeeDiscount::class, 'student_id');
    }

    public function parents(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Parent::class, 'student_parent_relationship');
    }

    public function transportRegistrations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TransportRegistration::class, 'student_id');
    }
}
