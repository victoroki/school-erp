<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    public $table = 'requisition_items';

    protected $primaryKey = 'requisition_item_id';

    public $fillable = [
        'requisition_id',
        'item_id',
        'item_name',
        'quantity_needed',
        'estimated_price',
        'purpose',
        'quantity_fulfilled',
        'fulfilled_at'
    ];

    protected $casts = [
        'requisition_id' => 'integer',
        'item_id' => 'integer',
        'item_name' => 'string',
        'quantity_needed' => 'integer',
        'estimated_price' => 'decimal:2',
        'purpose' => 'string',
        'quantity_fulfilled' => 'integer',
        'fulfilled_at' => 'datetime'
    ];

    public static array $rules = [
        'requisition_id' => 'required|exists:requisitions,requisition_id',
        'item_id' => 'nullable|exists:inventory_items,item_id',
        'item_name' => 'required|string|max:255',
        'quantity_needed' => 'required|integer|min:1',
        'estimated_price' => 'required|numeric|min:0',
        'purpose' => 'required|string|max:500'
    ];

    // Relationships
    public function requisition()
    {
        return $this->belongsTo(\App\Models\Requisition::class, 'requisition_id');
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\InventoryItem::class, 'item_id');
    }

    // Accessor methods
    public function getAmountAttribute(): float
    {
        return $this->quantity_needed * $this->estimated_price;
    }

    public function getIsFulfilledAttribute(): bool
    {
        return $this->quantity_fulfilled >= $this->quantity_needed;
    }

    public function getRemainingQuantityAttribute(): int
    {
        return max(0, $this->quantity_needed - $this->quantity_fulfilled);
    }
}