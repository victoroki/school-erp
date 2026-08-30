<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeAdjustment extends Model
{
    public $table = 'fee_adjustments';

    public $fillable = [
        'student_fee_assignment_id',
        'student_id',
        'original_amount',
        'new_amount',
        'adjustment_amount',
        'adjustment_type',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejection_reason',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'new_amount' => 'decimal:2',
        'adjustment_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public static array $rules = [
        'student_fee_assignment_id' => 'required|exists:student_fee_assignments,id',
        'student_id' => 'required|exists:students,student_id',
        'adjustment_type' => 'required|in:reduction,increase,waiver',
        'new_amount' => 'required|numeric|min:0',
        'reason' => 'required|string|max:1000',
    ];

    public function studentFeeAssignment()
    {
        return $this->belongsTo(\App\Models\StudentFeeAssignment::class, 'student_fee_assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(\App\Models\FeeAdjustmentAuditLog::class, 'fee_adjustment_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function approve($userId, $notes = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        $this->auditLogs()->create([
            'action' => 'approved',
            'user_id' => $userId,
            'details' => json_encode(['notes' => $notes]),
        ]);

        $oldFinal = (float) $this->studentFeeAssignment()->value('final_amount');
        $this->studentFeeAssignment()->update([
            'final_amount' => $this->new_amount,
            'discount_amount' => $this->original_amount - $this->new_amount,
        ]);
        $newFinal = (float) $this->new_amount;

        // Post the financial effect to the source-of-truth ledger.
        $delta = $newFinal - $oldFinal;
        if (abs($delta) > 0.009) {
            app(\App\Services\LedgerService::class)->postAdjustment(
                $this->studentFeeAssignment()->first(),
                $delta,
                'Approved fee adjustment: ' . $this->reason
            );
        }
    }

    public function reject($userId, $reason)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $this->auditLogs()->create([
            'action' => 'rejected',
            'user_id' => $userId,
            'details' => json_encode(['reason' => $reason]),
        ]);
    }

    public function logAction($action, $userId = null, $details = null)
    {
        $this->auditLogs()->create([
            'action' => $action,
            'user_id' => $userId ?? auth()->id(),
            'details' => is_array($details) ? json_encode($details) : $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function getAdjustmentPercentageAttribute()
    {
        if ($this->original_amount == 0) {
            return 0;
        }
        return round((abs($this->adjustment_amount) / $this->original_amount) * 100, 2);
    }
}
