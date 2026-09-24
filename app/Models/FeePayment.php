<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    use HasFactory;

    /**
     * Payment methods the `payment_method` ENUM column actually supports
     * (see 2025_06_17_100901_create_fee_payments_table). The mobile API
     * validates against this list so no invalid value can reach the DB —
     * MySQL silently coerces unknown ENUM values to ''.
     */
    public const PAYMENT_METHODS = ['cash', 'check', 'card', 'bank_transfer', 'online'];

    protected $table = 'fee_payments';
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'student_fee_assignment_id',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_id',
        'client_reference',
        'receipt_number',
        'remarks',
        'collected_by',
        'reversed_at',
        'reversal_reason',
        'reversed_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'reversed_at' => 'datetime',
    ];

    /**
     * Payments that still count towards a balance.
     *
     * Every balance, collection total and report figure must apply this scope.
     * A reversed payment stays on the ledger for audit but is no longer money
     * that was received.
     *
     * The column is qualified because this scope is applied to queries that
     * join student_fee_assignments, and an unqualified `reversed_at` would be
     * ambiguous (or silently pick the wrong table) in those queries.
     */
    public function scopeNotReversed($query)
    {
        return $query->whereNull($query->getModel()->qualifyColumn('reversed_at'));
    }

    public function scopeReversed($query)
    {
        return $query->whereNotNull($query->getModel()->qualifyColumn('reversed_at'));
    }

    public function isReversed(): bool
    {
        return $this->reversed_at !== null;
    }

    public function studentFeeAssignment()
    {
        return $this->belongsTo(StudentFeeAssignment::class, 'student_fee_assignment_id');
    }

    public function allocations()
    {
        return $this->hasMany(\App\Models\PaymentAllocation::class, 'payment_id', 'payment_id');
    }

    public function collectedBy()
    {
        return $this->belongsTo(\App\Models\Staff::class, 'collected_by', 'staff_id');
    }

    public function reversedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'reversed_by');
    }
}
