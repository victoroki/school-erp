<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    public $table = 'bank_transactions';
    protected $primaryKey = 'transaction_id';

    public $fillable = [
        'account_id',
        'amount',
        'transaction_type',
        'transaction_date',
        'description',
        'reference_number',
        'source_account_id',
        'target_account_id',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'description' => 'string',
        'reference_number' => 'string'
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'account_id', 'account_id');
    }

    public function sourceAccount()
    {
        return $this->belongsTo(BankAccount::class, 'source_account_id', 'account_id');
    }

    public function targetAccount()
    {
        return $this->belongsTo(BankAccount::class, 'target_account_id', 'account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
