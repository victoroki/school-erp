<?php

namespace App\Repositories;

use App\Models\Subject;
use App\Repositories\BaseRepository;

class SubjectRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'subject_code',
        'name',
        'description',
        'is_elective'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Subject::class;
    }
}
