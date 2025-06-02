<?php

namespace App\Repositories;

use App\Models\BankAccount;
use App\Repositories\BaseRepository;

class BankAccountRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'account_name',
        'account_number',
        'bank_name',
        'branch_name',
        'ifsc_code',
        'opening_balance',
        'current_balance',
        'account_type'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return BankAccount::class;
    }
}
