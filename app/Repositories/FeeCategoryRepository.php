<?php

namespace App\Repositories;

use App\Models\FeeCategory;
use App\Repositories\BaseRepository;

class FeeCategoryRepository extends BaseRepository
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
        return FeeCategory::class;
    }
}
