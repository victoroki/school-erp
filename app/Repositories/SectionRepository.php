<?php

namespace App\Repositories;

use App\Models\Section;
use App\Repositories\BaseRepository;

class SectionRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'class_id',
        'name',
        'capacity'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Section::class;
    }

    public function with(array $relations)
    {
        return $this->model->with($relations);
    }
}
