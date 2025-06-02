<?php

namespace App\Repositories;

use App\Models\Period;
use App\Repositories\BaseRepository;

class PeriodRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'start_time',
        'end_time'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Period::class;
    }
}
