<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    public $table = 'refunds';

    public $fillable = [
        'student_id',
        'payment_id',
        'student_fee_assignment_id',
        'amount',
        'reason',
        'supporting_info',
        'status',
        'requested_by',
        'requested_at',
        'reviewed_by',
        'reviewed_at',
        'approval_notes',
        'rejection_reason',
        'refund_method',
        'refund_reference',
        'ledger_entry_id',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public static array $rules = [
        'student_id' => 'required|exists:students,student_id',
        'amount' => 'required|numeric|min:0.01',
        'reason' => 'required|string|max:2000',
    ];

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function payment()
    {
        return $this->belongsTo(\App\Models\FeePayment::class, 'payment_id', 'payment_id');
    }

    public function studentFeeAssignment()
    {
        return $this->belongsTo(\App\Models\StudentFeeAssignment::class, 'student_fee_assignment_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'completed_by');
    }

    public function ledgerEntry()
    {
        return $this->belongsTo(\App\Models\LedgerEntry::class, 'ledger_entry_id');
    }

    public function scopeRequested($query)
    {
        return $query->where('status', 'requested');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePendingApproval($query)
    {
        return $query->whereIn('status', ['requested', 'approved']);
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }
}
