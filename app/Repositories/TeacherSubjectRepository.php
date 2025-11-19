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

    /**
     * Override paginate to eager load relationships
     */
    public function paginate(int $perPage, array $columns = ['*']): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->allQuery();
        
        // Eager load relationships to avoid N+1 queries
        $query->with(['staff', 'subject', 'classSection.section', 'classSection.class', 'academicYear']);
        
        return $query->paginate($perPage, $columns);
    }
}
