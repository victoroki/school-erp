<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    public $table = 'suppliers';
    
    protected $primaryKey = 'supplier_id';

    public $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'code',              // Supplier code (auto-generated)
        'supply_categories', // Categories supplied (JSON)
        'payment_terms',     // Payment terms (Cash, 30 days, 60 days)
        'is_active',         // Active status
        'rating',            // Rating (1-5 stars)
        'bank_details',      // Bank details for payments
        'notes',             // Notes
        'supplying_since'    // Date since they started supplying
    ];

    protected $casts = [
        'name' => 'string',
        'contact_person' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'address' => 'string',
        'supply_categories' => 'array',
        'payment_terms' => 'string',
        'is_active' => 'boolean',
        'rating' => 'integer',
        'notes' => 'string',
        'supplying_since' => 'date'
    ];

    public static array $rules = [
        'name' => 'required|string|max:100',
        'contact_person' => 'nullable|string|max:100',
        'email' => 'nullable|email|max:100',
        'phone' => 'required|string|max:20',
        'address' => 'nullable|string|max:65535',
        'code' => 'nullable|string|max:50|unique:suppliers,code',
        'supply_categories' => 'nullable|array',
        'payment_terms' => 'nullable|in:Cash,Net 15,Net 30,Net 60,Net 90',
        'is_active' => 'boolean',
        'rating' => 'nullable|integer|min:1|max:5',
        'bank_details' => 'nullable|string|max:500',
        'notes' => 'nullable|string|max:65535',
        'supplying_since' => 'nullable|date',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    // Relationships
    public function inventoryItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\InventoryItem::class, 'supplier_id');
    }

    // Get total items supplied
    public function getTotalItemsSupplied(): int
    {
        return $this->inventoryItems()->count();
    }

    // Get total value of items supplied
    public function getTotalValueSupplied(): float
    {
        return $this->inventoryItems()->sum('cost_per_unit');
    }

    // Get active status badge
    public function getActiveStatusBadgeAttribute(): string
    {
        return $this->is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>';
    }
}