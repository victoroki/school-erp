<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankReconciliation extends Model
{
    public $table = 'bank_reconciliations';

    public $fillable = [
        'bank_account_id',
        'statement_date',
        'statement_balance',
        'system_balance',
        'status',
        'reconciled_by'
    ];

    protected $casts = [
        'statement_date' => 'date',
        'statement_balance' => 'decimal:2',
        'system_balance' => 'decimal:2'
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function reconciledBy()
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }
}
