<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One payment (or part-payment) towards a purchase order.
 *
 * The supplier is paid through these rows — never through receiving goods.
 * Each row moves real money: the controller records it and posts the matching
 * BankLedger withdrawal in the same transaction, so the bank statement and the
 * payable balance can never disagree.
 */
class PurchaseOrderPayment extends Model
{
    public const PAYMENT_METHODS = ['cash', 'check', 'bank_transfer', 'card', 'online'];

    protected $table = 'purchase_order_payments';

    protected $fillable = [
        'po_id',
        'amount',
        'payment_date',
        'payment_method',
        'bank_account_id',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id', 'po_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id', 'account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
