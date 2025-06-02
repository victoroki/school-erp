<?php

namespace App\Repositories;

use App\Models\ExamSchedule;
use App\Repositories\BaseRepository;

class ExamScheduleRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'exam_id',
        'class_id',
        'subject_id',
        'exam_date',
        'start_time',
        'end_time',
        'room_id',
        'max_marks',
        'passing_marks'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ExamSchedule::class;
    }
}
