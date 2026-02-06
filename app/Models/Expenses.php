<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    public $table = 'expenses';

    public $fillable = [
        'category_id',
        'bank_account_id',
        'supplier_id',
        'requested_by',
        'amount',
        'expense_date',
        'payment_date',
        'description',
        'status',
        'payment_method',
        'reference_number',
        'attachment',
        'receipt_number',
        'approved_by',
        'created_by',
        'rejected_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'payment_date' => 'date',
        'description' => 'string',
        'payment_method' => 'string',
        'reference_number' => 'string',
        'status' => 'string',
        'attachment' => 'string',
        'receipt_number' => 'string',
        'rejected_reason' => 'string'
    ];

    public static array $rules = [
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

    public function bankAccount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\BankAccount::class, 'bank_account_id', 'account_id');
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }

    public function requestedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'requested_by');
    }
}
