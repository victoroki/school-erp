<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    public $table = 'bank_accounts';

    public $fillable = [
        'account_name',
        'account_number',
        'bank_name',
        'branch_name',
        'ifsc_code',
        'opening_balance',
        'current_balance',
        'account_type'
    ];

    protected $casts = [
        'account_name' => 'string',
        'account_number' => 'string',
        'bank_name' => 'string',
        'branch_name' => 'string',
        'ifsc_code' => 'string',
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'account_type' => 'string'
    ];

    public static array $rules = [
        'account_name' => 'required|string|max:100',
        'account_number' => 'required|string|max:50',
        'bank_name' => 'required|string|max:100',
        'branch_name' => 'nullable|string|max:100',
        'ifsc_code' => 'nullable|string|max:20',
        'opening_balance' => 'required|numeric',
        'current_balance' => 'required|numeric',
        'account_type' => 'nullable|string|max:50',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function bankTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BankTransaction::class, 'account_id');
    }

    public function bankTransaction1s(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BankTransaction::class, 'source_account_id');
    }

    public function bankTransaction2s(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BankTransaction::class, 'target_account_id');
    }
}
