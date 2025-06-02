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
}
