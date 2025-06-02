<?php

namespace App\Repositories;

use App\Models\InventoryCategory;
use App\Repositories\BaseRepository;

class InventoryCategoryRepository extends BaseRepository
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
        return InventoryCategory::class;
    }
}
