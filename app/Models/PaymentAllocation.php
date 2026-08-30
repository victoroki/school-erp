<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAllocation extends Model
{
    public $table = 'payment_allocations';

    // No auto-updated_at maintenance for this append-only table
    public $timestamps = false;

    public $fillable = [
        'payment_id',
        'student_fee_assignment_id',
        'amount',
        'allocation_strategy',
        'created_by',
        'allocated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'allocated_at' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(\App\Models\FeePayment::class, 'payment_id', 'payment_id');
    }

    public function studentFeeAssignment()
    {
        return $this->belongsTo(\App\Models\StudentFeeAssignment::class, 'student_fee_assignment_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
