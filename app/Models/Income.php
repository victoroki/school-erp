<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    public $table = 'income';
    protected $primaryKey = 'income_id';

    public $fillable = [
        'category_id',
        'bank_account_id',
        'amount',
        'payer_name',
        'income_date',
        'description',
        'payment_method',
        'reference_number',
        'attachment',
        'receipt_number',
        'status',
        'received_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'income_date' => 'date',
        'description' => 'string',
        'payment_method' => 'string',
        'reference_number' => 'string',
        'payer_name' => 'string',
        'attachment' => 'string',
        'receipt_number' => 'string',
        'status' => 'string'
    ];

    public static array $rules = [
        'category_id' => 'required',
        'amount' => 'required|numeric',
        'income_date' => 'required|date',
        'payment_method' => 'required|string',
        'bank_account_id' => 'required_if:payment_method,bank_transfer,check,online'
    ];

    public function category()
    {
        return $this->belongsTo(IncomeCategory::class, 'category_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(Staff::class, 'received_by');
    }
}
