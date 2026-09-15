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
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function studentFeeAssignment()
    {
        return $this->belongsTo(StudentFeeAssignment::class, 'student_fee_assignment_id');
    }

    public function collectedBy()
    {
        return $this->belongsTo(\App\Models\Staff::class, 'collected_by', 'staff_id');
    }
}
