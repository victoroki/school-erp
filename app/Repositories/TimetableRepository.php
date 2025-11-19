<?php

namespace App\Repositories;

use App\Models\Timetable;
use App\Repositories\BaseRepository;

class TimetableRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'class_section_id',
        'day_of_week',
        'period_id',
        'subject_id',
        'teacher_id',
        'classroom_id',
        'academic_year_id'
    ];

    public function with(array $relations) {
        return $this->model->with($relations);
    }

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Timetable::class;
    }
}
