<?php

namespace App\Repositories;

use App\Models\ExamType;
use App\Repositories\BaseRepository;

class ExamTypeRepository extends BaseRepository
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
        return ExamType::class;
    }
}
