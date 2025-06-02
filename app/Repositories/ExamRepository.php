<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Repositories\BaseRepository;

class ExamRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'exam_type_id',
        'name',
        'description',
        'academic_year_id',
        'start_date',
        'end_date',
        'publish_result'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Exam::class;
    }
}
