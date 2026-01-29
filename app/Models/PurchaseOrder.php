<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    public $table = 'purchase_orders';

    protected $primaryKey = 'po_id';

    public $fillable = [
        'po_number',
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