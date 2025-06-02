<?php

namespace App\Repositories;

use App\Models\ExpenseCategories;
use App\Repositories\BaseRepository;

class ExpenseCategoriesRepository extends BaseRepository
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
        return ExpenseCategories::class;
    }
}
