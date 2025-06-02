<?php

namespace App\Repositories;

use App\Models\TeacherSubject;
use App\Repositories\BaseRepository;

class TeacherSubjectRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'staff_id',
        'subject_id',
        'class_section_id',
        'academic_year_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return TeacherSubject::class;
    }
}
