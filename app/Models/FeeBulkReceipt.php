<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Money received from a sponsor (CDF, county, NGO, church, donor...) to be
 * distributed across students.
 *
 * The receipt is the single cash event for the amount. Student allocations
 * create ordinary FeePayment rows carrying bulk_receipt_id, so balances,
 * statements, receipts and reversals flow through the standard fee machinery.
 * The unallocated remainder stays on the receipt — it never disappears and it
 * never becomes a student credit.
 */
class FeeBulkReceipt extends Model
{
    public const SPONSOR_TYPES = [
        'cdf', 'county_government', 'ngo', 'church', 'donor',
        'scholarship_provider', 'corporate', 'other',
    ];

    protected $table = 'fee_bulk_receipts';

    protected $fillable = [
        'sponsor_name',
        'sponsor_type',
        'reference_number',
        'amount',
        'payment_method',
        'bank_account_id',
        'transaction_id',
        'received_date',
        'academic_year_id',
        'term_id',
        'remarks',
        'reversed_at',
        'reversal_reason',
        'reversed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'received_date' => 'date',
        'reversed_at' => 'datetime',
    ];

    public function allocations(): HasMany
    {
        return $this->hasMany(FeePayment::class, 'bulk_receipt_id', 'id');
    }

    public function activeAllocations(): HasMany
    {
        return $this->allocations()->whereNull('reversed_at');
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id', 'account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversedBy()
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    /**
     * Money distributed to students through still-valid payments.
     */
    public function allocatedAmount(): float
    {
        return round((float) $this->allocations()->whereNull('reversed_at')->sum('amount'), 2);
    }

    /**
     * Money on the receipt not yet given to any student. Reversed child
     * payments return here automatically.
     */
    public function remainingAmount(): float
    {
        return round((float) $this->amount - $this->allocatedAmount(), 2);
    }

    public function isReversed(): bool
    {
        return $this->reversed_at !== null;
    }

    /**
     * A receipt can be closed (reversed) only when none of its money is
     * sitting on a student account.
     */
    public function canBeReversed(): bool
    {
        return ! $this->isReversed() && $this->allocatedAmount() <= 0;
    }

    /**
     * Reverse the parent receipt. Only allowed once no active child payments
     * remain (children must be reversed first), so the cash figure and the
     * students' balances stay explainable.
     */
    public function reverse(string $reason, ?int $byUserId = null): void
    {
        if ($this->isReversed()) {
            throw new \RuntimeException('This bulk receipt has already been reversed.');
        }

        if ($this->allocatedAmount() > 0) {
            throw new \RuntimeException('Reverse the student allocations of this receipt first.');
        }

        $this->update([
            'reversed_at' => now(),
            'reversal_reason' => $reason,
            'reversed_by' => $byUserId ?? auth()->id(),
        ]);
    }
}
