<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDiscount extends Model
{
    public $table = 'student_discounts';

    public $fillable = [
        'student_id',
        'discount_scheme_id',
        'academic_year_id',
        'applied_amount',
        'justification',
        'requested_by',
        'requested_date',
        'approval_status',
        'approved_by',
        'approved_date',
        'rejection_reason',
        'status'
    ];

    protected $casts = [
        'applied_amount' => 'decimal:2',
        'requested_date' => 'datetime',
        'approved_date' => 'datetime'
    ];

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function discountScheme()
    {
        return $this->belongsTo(\App\Models\DiscountScheme::class, 'discount_scheme_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function requester()
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}
