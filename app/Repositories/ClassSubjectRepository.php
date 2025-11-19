<?php

namespace App\Repositories;

use App\Models\ClassSubject;
use App\Repositories\BaseRepository;

class ClassSubjectRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'class_id',
        'subject_id',
        'academic_year_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ClassSubject::class;
    }

    public function with(array $relations)
    {
        return $this->model->with($relations);
    }
}
