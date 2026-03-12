<?php

namespace App\Repositories;

use App\Models\DiscountScheme;
use App\Repositories\BaseRepository;

class DiscountSchemeRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'code',
        'type',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return DiscountScheme::class;
    }
}
