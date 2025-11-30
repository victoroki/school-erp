<?php

namespace App\Repositories;

use App\Models\StudentParentRelationship;
use App\Repositories\BaseRepository;

class StudentParentRelationshipRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'student_id',
        'parent_id',
        'is_primary_contact'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return StudentParentRelationship::class;
    }

    public function paginate(int $perPage, array $columns = ['*']): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->allQuery();
        $query->with(['student','parent']);
        return $query->paginate($perPage, $columns);
    }
}
