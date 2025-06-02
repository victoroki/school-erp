<?php

namespace App\Repositories;

use App\Models\InventoryItem;
use App\Repositories\BaseRepository;

class InventoryItemRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'category_id',
        'quantity',
        'unit',
        'minimum_quantity',
        'cost_per_unit',
        'supplier_id',
        'location',
        'description'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return InventoryItem::class;
    }
}
