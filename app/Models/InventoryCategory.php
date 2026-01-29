<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryCategory extends Model
{
    public $table = 'inventory_categories';
    
    protected $primaryKey = 'category_id';

    public $fillable = [
        'name',
        'description',
        'category_type',      // Consumable or Asset
        'icon',              // Icon for the category
        'trackable',         // Whether this category is trackable
        'default_location',  // Default storage location
        'code',              // Category code
        'reorder_level',     // Default reorder level for items in this category
        'warranty_period'    // Default warranty period for asset items in this category
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'category_type' => 'string',
        'icon' => 'string',
        'trackable' => 'boolean',
        'default_location' => 'string',
        'code' => 'string',
        'reorder_level' => 'integer',
        'warranty_period' => 'integer'
    ];

    public static array $rules = [
        'name' => 'required|string|max:100',
        'description' => 'nullable|string|max:65535',
        'category_type' => 'required|in:consumable,asset',
        'icon' => 'nullable|string|max:50',
        'trackable' => 'boolean',
        'default_location' => 'nullable|string|max:100',
        'code' => 'nullable|string|max:50|unique:inventory_categories,code',
        'reorder_level' => 'nullable|integer|min:0',
        'warranty_period' => 'nullable|integer|min:0',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function inventoryItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\InventoryItem::class, 'category_id');
    }

    public function getIsConsumableAttribute(): bool
    {
        return $this->category_type === 'consumable';
    }

    public function getIsAssetAttribute(): bool
    {
        return $this->category_type === 'asset';
    }
}