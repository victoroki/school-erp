<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    public $table = 'expenses';

    public $fillable = [
        'category_id',
        'amount',
        'expense_date',
        'description',
        'payment_method',
        'reference_number',
        'approved_by',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'description' => 'string',
        'payment_method' => 'string',
        'reference_number' => 'string'
    ];

    public static array $rules = [
        'category_id' => 'nullable',
        'amount' => 'required|numeric',
        'expense_date' => 'required',
        'description' => 'nullable|string|max:65535',
        'payment_method' => 'required|string',
        'reference_number' => 'nullable|string|max:100',
        'approved_by' => 'nullable',
        'created_by' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'created_by');
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\ExpenseCategory::class, 'category_id');
    }

    public function approvedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'approved_by');
    }
}
