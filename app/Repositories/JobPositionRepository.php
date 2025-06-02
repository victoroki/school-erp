<?php

namespace App\Repositories;

use App\Models\JobPosition;
use App\Repositories\BaseRepository;

class JobPositionRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title',
        'department_id',
        'description',
        'responsibilities',
        'qualifications',
        'is_active'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return JobPosition::class;
    }
}
