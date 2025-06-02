<?php

namespace App\Repositories;

use App\Models\GradingScale;
use App\Repositories\BaseRepository;

class GradingScaleRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'min_percentage',
        'max_percentage',
        'grade_point',
        'description'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return GradingScale::class;
    }
}
