<?php

namespace App\Models;

use App\Exceptions\InsufficientFundsException;
use App\Models\Concerns\GeneratesDailySequenceNumber;
use App\Services\BankLedger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseOrder extends Model
{
    use GeneratesDailySequenceNumber;

    /** Status values the purchase_orders.status enum accepts. */
    public const STATUS_DRAFT = 'Draft';
    public const STATUS_PENDING_APPROVAL = 'Pending_Approval';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_SENT = 'Sent';
    public const STATUS_PARTIALLY_RECEIVED = 'Partially_Received';
    public const STATUS_FULLY_RECEIVED = 'Fully_Received';
    public const STATUS_CANCELLED = 'Cancelled';

    /** Payment arrangements: pay on receipt, or pay later (credit). */
    public const ARRANGEMENT_IMMEDIATE = 'immediate';
    public const ARRANGEMENT_CREDIT = 'credit';

    public const ARRANGEMENTS = [
        self::ARRANGEMENT_IMMEDIATE => 'Immediate Payment',
        self::ARRANGEMENT_CREDIT => 'Credit / Pay Later',
    ];

    /** Derived (never stored) payment states. */
    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PARTIAL = 'partially_paid';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_OVERDUE = 'overdue';

    public $table = 'purchase_orders';

    protected $primaryKey = 'po_id';

    public $fillable = [
        'po_number',
        'requisition_id',
        'supplier_id',
        'order_date',
        'expected_delivery_date',
        'delivery_address',
        'sub_total',
        'tax_amount',
        'delivery_charges',
        'grand_total',
        'terms_conditions',
        'special_instructions',
        'status',
        'payment_arrangement',
        'payment_due_date',
        'credit_terms',
        'paid_date',
        'approved_by',
        'approved_date',
        'received_by',
        'received_date'
    ];

    protected $casts = [
        'po_number' => 'string',
        'supplier_id' => 'integer',
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'delivery_address' => 'string',
        'sub_total' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'delivery_charges' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'terms_conditions' => 'string',
        'special_instructions' => 'string',
        'status' => 'string',
        'payment_arrangement' => 'string',
        'payment_due_date' => 'date',
        'credit_terms' => 'string',
        'paid_date' => 'datetime',
        'approved_by' => 'integer',
        'approved_date' => 'datetime',
        'received_by' => 'integer',
        'received_date' => 'datetime'
    ];

    public static array $rules = [
        'po_number' => 'required|string|unique:purchase_orders,po_number',
        'supplier_id' => 'required|exists:suppliers,supplier_id',
        'order_date' => 'required|date',
        'expected_delivery_date' => 'required|date|after_or_equal:order_date',
        'status' => 'required|in:Draft,Pending_Approval,Approved,Sent,Partially_Received,Fully_Received,Cancelled',
        'grand_total' => 'required|numeric|min:0'
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'received_by');
    }

    public function items()
    {
        return $this->hasMany(\App\Models\PurchaseOrderItem::class, 'po_id');
    }

    /**
     * The requisition this order was generated from — null for POs created
     * directly without a requisition.
     */
    public function requisition()
    {
        return $this->belongsTo(\App\Models\Requisition::class, 'requisition_id', 'requisition_id');
    }

    /**
     * The supplier payments made against this order.
     */
    public function payments()
    {
        return $this->hasMany(\App\Models\PurchaseOrderPayment::class, 'po_id', 'po_id');
    }

    /**
     * Total money actually paid towards this order.
     */
    public function paidAmount(): float
    {
        return round((float) $this->payments()->sum('amount'), 2);
    }

    /**
     * grand_total - valid payments. Never negative: the pay path refuses
     * overpayment, and this clamp keeps historical rows display-safe.
     */
    public function outstandingBalance(): float
    {
        return max(0, round((float) $this->grand_total - $this->paidAmount(), 2));
    }

    /**
     * The payment state derived from payments and the due date — nothing is
     * stored, so it can never drift from the payment rows.
     */
    public function derivedPaymentStatus(): string
    {
        $paid = $this->paidAmount();
        $total = (float) $this->grand_total;

        if ($total > 0 && $paid >= $total) {
            return self::PAYMENT_PAID;
        }

        if ($paid > 0) {
            return self::PAYMENT_PARTIAL;
        }

        if ($this->payment_due_date !== null && $this->payment_due_date->isPast() && $this->isReceived()) {
            return self::PAYMENT_OVERDUE;
        }

        return self::PAYMENT_UNPAID;
    }

    /**
     * Whether this order's goods have been received (fully or partially).
     */
    public function isReceived(): bool
    {
        return in_array($this->status, [self::STATUS_PARTIALLY_RECEIVED, self::STATUS_FULLY_RECEIVED], true);
    }

    /**
     * Orders whose goods have arrived — the population a payable exists for.
     * Defined once so the PO screens, the supplier profile and the statement
     * of financial position can never disagree about it.
     */
    public function scopeReceived($query)
    {
        return $query->whereIn('status', [self::STATUS_PARTIALLY_RECEIVED, self::STATUS_FULLY_RECEIVED]);
    }

    /**
     * Pay the supplier (fully or partially).
     *
     * One transaction: lock the PO row so two concurrent payments read the
     * same outstanding balance, validate the amount, write the payment row
     * and post the BankLedger withdrawal together. A credit purchase never
     * touches the bank on its own — only an actual payment does.
     *
     * @throws RuntimeException            on invalid payment state/amount
     * @throws InsufficientFundsException  when the bank account cannot cover it
     */
    public static function recordPayment(self $purchaseOrder, array $data): PurchaseOrderPayment
    {
        return DB::transaction(function () use ($purchaseOrder, $data) {
            // Lock the order so concurrent payments cannot both pass the
            // outstanding-balance test against the same figure.
            $purchaseOrder = self::where('po_id', $purchaseOrder->po_id)
                ->lockForUpdate()
                ->firstOrFail();

            $outstanding = $purchaseOrder->outstandingBalance();

            if ($outstanding <= 0) {
                throw new RuntimeException('This purchase order is already paid in full.');
            }

            $amount = round((float) ($data['amount'] ?? 0), 2);

            if ($amount <= 0) {
                throw new RuntimeException('Payment amount must be greater than zero.');
            }

            if ($amount > $outstanding) {
                throw new RuntimeException(sprintf(
                    'Payment of %s exceeds the outstanding balance of %s.',
                    number_format($amount, 2),
                    number_format($outstanding, 2)
                ));
            }

            $method = $data['payment_method'] ?? 'cash';
            $bankAccountId = $data['bank_account_id'] ?? null;

            // Non-cash payments must name the account the money leaves, the
            // same rule the Expenses module enforces.
            if ($method !== 'cash' && empty($bankAccountId)) {
                throw new RuntimeException('Please choose the bank account this payment is made from.');
            }

            $bankAccount = null;
            if ($bankAccountId) {
                $bankAccount = BankAccount::lockForUpdate()->find($bankAccountId);

                if (!$bankAccount) {
                    throw new RuntimeException('The selected bank account no longer exists.');
                }
            }

            $payment = PurchaseOrderPayment::create([
                'po_id' => $purchaseOrder->po_id,
                'amount' => $amount,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'payment_method' => $method,
                'bank_account_id' => $bankAccount?->account_id,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // The one place money moves: a single BankLedger withdrawal so the
            // balance and the bank statement stay in step. Cash payments keep
            // the ledger untouched, matching the Expenses cash convention.
            if ($bankAccount) {
                BankLedger::recordWithdrawal(
                    $bankAccount,
                    $amount,
                    $payment->payment_date->toDateString(),
                    BankLedger::describe('PurchaseOrder', $purchaseOrder->po_id,
                        $purchaseOrder->po_number . ' - ' . ($purchaseOrder->supplier->name ?? 'Supplier')),
                    $payment->reference_number,
                    auth()->id(),
                    'unreconciled',
                    'PurchaseOrderPayment',
                    $payment->id
                );
            }

            if ($purchaseOrder->outstandingBalance() <= 0) {
                $purchaseOrder->forceFill(['paid_date' => now()])->save();
            }

            AuditTrail::log('Purchase Order', 'PAYMENT', $purchaseOrder->po_id, null, [
                'payment_id' => $payment->id,
                'amount' => $amount,
                'payment_method' => $method,
                'bank_account_id' => $bankAccount?->account_id,
                'outstanding_after' => $purchaseOrder->outstandingBalance(),
            ]);

            return $payment;
        });
    }

    /**
     * A collision-free PO number.
     *
     * PO-YYYYMMDD-NNN from a MAX+1 scan of today's numbers, retried against
     * the unique index if two clerks order at the same instant. rand()
     * previously allowed two orders created in the same second to mint the
     * same number.
     */
    public static function generateNumber(): string
    {
        return static::nextSequenceNumber();
    }

    protected static function sequenceColumn(): string
    {
        return 'po_number';
    }

    protected static function sequencePrefix(): string
    {
        return 'PO-';
    }

    // Accessor methods
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'Draft' => 'badge-secondary',
            'Pending_Approval' => 'badge-warning',
            'Approved' => 'badge-success',
            'Sent' => 'badge-info',
            'Partially_Received' => 'badge-primary',
            'Fully_Received' => 'badge-dark',
            'Cancelled' => 'badge-danger'
        ];

        $status = str_replace('_', ' ', $this->status);
        $badge = $badges[$this->status] ?? 'badge-secondary';

        return "<span class=\"badge {$badge}\">{$status}</span>";
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->status === 'Draft';
    }

    public function getIsPendingApprovalAttribute(): bool
    {
        return $this->status === 'Pending_Approval';
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'Approved';
    }

    public function getIsReceivedAttribute(): bool
    {
        return in_array($this->status, ['Partially_Received', 'Fully_Received']);
    }
}