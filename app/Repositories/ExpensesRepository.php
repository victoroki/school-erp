<?php

namespace App\Repositories;

use App\Models\Expenses;
use App\Repositories\BaseRepository;

class ExpensesRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'category_id',
        'amount',
        'expense_date',
        'description',
        'payment_method',
        'reference_number',
        'approved_by',
        'created_by'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Expenses::class;
    }
}
