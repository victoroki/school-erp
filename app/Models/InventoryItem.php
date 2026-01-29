<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    public $table = 'inventory_items';
    protected $primaryKey = 'item_id';

    public $fillable = [
        'name',
        'category_id',
        'quantity',
        'unit',
        'minimum_quantity',
        'cost_per_unit',
        'supplier_id',
        'location',
        'description',
        'item_code',           // Auto-generated item code
        'reorder_quantity',    // Quantity to reorder when low
        'has_expiry',          // Whether item has expiry date
        'photo',               // Item photo
        'asset_tag',           // Asset tag for asset items
        'serial_number',       // Serial number for asset items
        'purchase_date',       // Purchase date for asset items
        'warranty_period',     // Warranty period in months for asset items
        'warranty_expiry',     // Warranty expiry date
        'current_condition',   // Current condition for asset items
        'assigned_to',         // Who is the item assigned to
        'requires_maintenance',// Whether asset requires maintenance
        'next_maintenance_due',// Next maintenance due date
        'purchase_receipt'     // Purchase receipt for asset items
    ];

    protected $casts = [
        'name' => 'string',
        'unit' => 'string',
        'cost_per_unit' => 'decimal:2',
        'location' => 'string',
        'description' => 'string',
        'has_expiry' => 'boolean',
        'photo' => 'string',
        'purchase_date' => 'date',
        'warranty_period' => 'integer',
        'warranty_expiry' => 'date',
        'current_condition' => 'string',
        'assigned_to' => 'integer',
        'requires_maintenance' => 'boolean',
        'next_maintenance_due' => 'date',
        'purchase_receipt' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|string|max:255',
        'category_id' => 'nullable|exists:inventory_categories,category_id',
        'quantity' => 'required|integer|min:0',
        'unit' => 'nullable|string|max:20',
        'minimum_quantity' => 'nullable|integer|min:0',
        'cost_per_unit' => 'nullable|numeric|min:0',
        'supplier_id' => 'nullable|exists:suppliers,supplier_id',
        'location' => 'nullable|string|max:100',
        'description' => 'nullable|string|max:65535',
        'item_code' => 'nullable|string|max:50|unique:inventory_items,item_code',
        'reorder_quantity' => 'nullable|integer|min:0',
        'has_expiry' => 'boolean',
        'photo' => 'nullable|string|max:255',
        'asset_tag' => 'nullable|string|max:50|unique:inventory_items,asset_tag',
        'serial_number' => 'nullable|string|max:100',
        'purchase_date' => 'nullable|date',
        'warranty_period' => 'nullable|integer|min:0',
        'warranty_expiry' => 'nullable|date',
        'current_condition' => 'nullable|in:Excellent,Good,Fair,Poor,Damaged',
        'assigned_to' => 'nullable|integer',
        'requires_maintenance' => 'boolean',
        'next_maintenance_due' => 'nullable|date',
        'purchase_receipt' => 'nullable|string|max:255',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\InventoryCategory::class, 'category_id');
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Staff::class, 'inventory_transactions');
    }

    // Helper to check if stock is low
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->minimum_quantity;
    }

    // Helper to check if stock is out
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    // Helper to check if item is an asset
    public function getIsAssetAttribute(): bool
    {
        return !empty($this->asset_tag);
    }

    // Helper to check if item is consumable
    public function getIsConsumableAttribute(): bool
    {
        return empty($this->asset_tag);
    }

    // Calculate total value
    public function getTotalValue(): float
    {
        return $this->quantity * $this->cost_per_unit;
    }

    // Get stock status
    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    // Relationship for inventory transactions
    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\InventoryTransaction::class, 'item_id');
    }

    // Get the category type
    public function getCategoryTypeAttribute(): string
    {
        return $this->category ? $this->category->category_type : 'consumable';
    }
}