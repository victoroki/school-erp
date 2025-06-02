<?php

namespace App\Repositories;

use App\Models\ExamResult;
use App\Repositories\BaseRepository;

class ExamResultRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'exam_id',
        'student_id',
        'class_section_id',
        'subject_id',
        'marks_obtained',
        'grade_id',
        'remarks',
        'created_by'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ExamResult::class;
    }
}
