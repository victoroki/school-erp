<?php

namespace App\Repositories;

use App\Models\IncomeCategory;
use App\Repositories\BaseRepository;

class IncomeCategoryRepository extends BaseRepository
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
        return IncomeCategory::class;
    }
}
