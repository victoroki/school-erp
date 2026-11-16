<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A hostel charge linked to a student and/or a bed allocation.
 *
 * The `hostel_fee` table has existed since the hostel migrations but had no
 * model, which left the `hostelFees()` relations on Student, HostelAllocation
 * and AcademicYear pointing at a missing class (a fatal error the moment any of
 * them was eager loaded). This model resolves them; fee collection UI is not
 * part of the hostel allocation module.
 */
class HostelFee extends Model
{
    public $table = 'hostel_fee';
    protected $primaryKey = 'fee_id';

    public const STATUS_PAID = 'paid';
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PARTIALLY_PAID = 'partially_paid';

    public $fillable = [
        'student_id',
        'allocation_id',
        'amount',
        'paid_amount',
        'due_date',
        'status',
        'academic_year_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'status' => 'string',
    ];

    public static array $rules = [
        'student_id' => 'nullable|exists:students,student_id',
        'allocation_id' => 'nullable|exists:hostel_allocations,allocation_id',
        'amount' => 'required|numeric|min:0',
        'paid_amount' => 'nullable|numeric|min:0',
        'due_date' => 'required|date',
        'status' => 'nullable|in:' . self::STATUS_PAID . ',' . self::STATUS_UNPAID . ',' . self::STATUS_PARTIALLY_PAID,
        'academic_year_id' => 'nullable|exists:academic_years,academic_year_id',
    ];

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function allocation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(HostelAllocation::class, 'allocation_id');
    }

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function getBalanceAttribute(): float
    {
        return (float) $this->amount - (float) ($this->paid_amount ?? 0);
    }

    public function isSettled(): bool
    {
        return $this->getBalanceAttribute() <= 0;
    }
}
