<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    public $table = 'students';
    protected $primaryKey = 'student_id';

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
        'user_id' => 'nullable|exists:users,id',
        'admission_no' => 'required|string|max:20',
        'first_name' => 'required|string|max:50',
        'middle_name' => 'nullable|string|max:50',
        'last_name' => 'required|string|max:50',
        'date_of_birth' => 'required|date',
        'gender' => 'required|in:male,female,other',
        'city' => 'required|string|max:50',
        'country' => 'required|string|max:50',
        'phone' => 'nullable|string|max:20',
        'emergency_contact' => 'required|string|max:20',
        'admission_date' => 'required|date',
        'photo_url' => 'nullable|string|max:255',
        'status' => 'nullable|in:active,inactive,alumni,transferred',
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

    public function studentFees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentFee::class, 'student_id');
    }

    public function payments()
    {
        return $this->hasManyThrough(\App\Models\FeePayment::class, \App\Models\StudentFee::class, 'student_id', 'student_fee_id');
    }

    public function getStudentFeeDiscountsAttribute()
    {
        return $this->hasMany(\App\Models\StudentFeeDiscount::class, 'student_id');
    }

    public function parents(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Parents::class, 'student_parent_relationship', 'student_id', 'parent_id');
    }

    public function transportRegistrations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TransportRegistration::class, 'student_id');
    }

    // Helper Attributes for Fee Management
    public function getTotalFeeAttribute()
    {
        return $this->studentFees->sum('final_amount');
    }

    public function getPaidFeeAttribute()
    {
        return $this->payments->sum('amount');
    }

    public function getBalanceFeeAttribute()
    {
        return $this->total_fee - $this->paid_fee;
    }

    public function getPaymentStatusAttribute()
    {
        $total = $this->total_fee;
        $paid = $this->paid_fee;

        if ($total <= 0) {
            return 'No Fee';
        }

        if ($paid >= $total) {
            return 'Paid';
        }

        if ($paid > 0) {
            return 'Partial';
        }

        return 'Unpaid';
    }
}
