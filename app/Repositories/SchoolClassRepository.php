<?php

namespace App\Repositories;

use App\Models\SchoolClass;
use App\Repositories\BaseRepository;

class SchoolClassRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'numeric_value',
        'description'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return SchoolClass::class;
    }
}
