<?php

namespace App\Repositories;

use App\Models\ClassSection;
use App\Repositories\BaseRepository;

class ClassSectionRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'academic_year_id',
        'class_id',
        'section_id',
        'classroom_id',
        'class_teacher_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ClassSection::class;
    }
}
