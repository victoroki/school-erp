<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeCategory extends Model
{
    public $table = 'fee_categories';
    protected $primaryKey = 'category_id';

    public $fillable = [
        'name',
        'code',
        'type',
        'description',
        'income_category_id',
        'display_order',
        'status',
        'created_by'
    ];

    protected $casts = [
        'name' => 'string',
        'code' => 'string',
        'type' => 'string',
        'description' => 'string',
        'income_category_id' => 'integer',
        'display_order' => 'integer',
        'status' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|string|max:100|unique:fee_categories,name',
        'code' => 'nullable|string|max:20',
        'type' => 'required|in:mandatory,optional',
        'description' => 'nullable|string',
        'income_category_id' => 'nullable|integer',
        'display_order' => 'nullable|integer',
        'status' => 'required|in:active,inactive'
    ];

    public function feeStructures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\FeeStructure::class, 'category_id');
    }

    public function incomeCategory()
    {
        return $this->belongsTo(\App\Models\IncomeCategory::class, 'income_category_id');
    }
}
