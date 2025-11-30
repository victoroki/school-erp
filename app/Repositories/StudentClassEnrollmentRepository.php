<?php

namespace App\Repositories;

use App\Models\StudentClassEnrollment;
use App\Repositories\BaseRepository;

class StudentClassEnrollmentRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'student_id',
        'class_section_id',
        'roll_number',
        'academic_year_id',
        'enrollment_date',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return StudentClassEnrollment::class;
    }

    public function paginate(int $perPage, array $columns = ['*']): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->allQuery();
        $query->with(['student','classSection.class','classSection.section','academicYear']);
        return $query->paginate($perPage, $columns);
    }
}
