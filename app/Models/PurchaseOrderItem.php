<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    public $table = 'purchase_order_items';

    protected $primaryKey = 'po_item_id';

    public $fillable = [
        'po_id',
        'item_id',
        'item_name',
        'description',
        'quantity',
        'unit_price',
        'total_price'
    ];

    protected $casts = [
        'po_id' => 'integer',
        'item_id' => 'integer',
        'item_name' => 'string',
        'description' => 'string',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    public static array $rules = [
        'po_id' => 'required|exists:purchase_orders,po_id',
        'item_id' => 'nullable|exists:inventory_items,item_id',
        'item_name' => 'required|string|max:255',
        'quantity' => 'required|integer|min:1',
        'unit_price' => 'required|numeric|min:0',
        'total_price' => 'required|numeric|min:0'
    ];

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(\App\Models\PurchaseOrder::class, 'po_id');
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\InventoryItem::class, 'item_id');
    }

    // Accessor methods
    public function getTotalPriceAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }
}