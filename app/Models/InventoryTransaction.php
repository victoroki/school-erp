<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    public $table = 'inventory_transactions';

    public $fillable = [
        'item_id',
        'transaction_type',
        'quantity',
        'balance_after',
        'transaction_date',
        'remarks',
        'issued_to',
        'handled_by'
    ];

    protected $casts = [
        'transaction_type' => 'string',
        'transaction_date' => 'date',
        'balance_after' => 'integer',
        'remarks' => 'string'
    ];

    public static array $rules = [
        'item_id' => 'nullable',
        'transaction_type' => 'required|string',
        'quantity' => 'required',
        'transaction_date' => 'required',
        'remarks' => 'nullable|string|max:65535',
        'issued_to' => 'nullable',
        'handled_by' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function item(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\InventoryItem::class, 'item_id');
    }

    public function handledBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'handled_by');
    }

    public function issuedTo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'issued_to');
    }

    // Calculate transaction value
    public function getTransactionValue(): float
    {
        return $this->quantity * $this->item->cost_per_unit;
    }

    // Get transaction type badge
    public function getTypeBadgeAttribute(): string
    {
        $badges = [
            'purchase' => 'badge-success',
            'issue' => 'badge-warning',
            'return' => 'badge-info',
            'damaged' => 'badge-danger',
            'write_off' => 'badge-dark',
            'adjustment' => 'badge-primary'
        ];
        
        $type = $this->transaction_type;
        $label = ucfirst(str_replace('_', ' ', $type));
        $badge = $badges[$type] ?? 'badge-secondary';
        
        return "<span class=\"badge {$badge}\">{$label}</span>";
    }

    // Get transaction direction
    public function getTransactionDirection(): string
    {
        $incomingTypes = ['purchase', 'return', 'adjustment'];
        return in_array($this->transaction_type, $incomingTypes) ? 'in' : 'out';
    }
}
