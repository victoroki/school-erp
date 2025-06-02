<?php

namespace App\Repositories;

use App\Models\ExpenseCategory;
use App\Repositories\BaseRepository;

class ExpenseCategoryRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'description'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ExpenseCategory::class;
    }
}
