<?php

namespace App\Repositories;

use App\Models\FeeStructure;
use App\Repositories\BaseRepository;

class FeeStructureRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'academic_year_id',
        'class_id',
        'category_id',
        'amount',
        'due_date'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return FeeStructure::class;
    }
}
