<?php

namespace App\Repositories;

use App\Models\HostelAllocation;
use App\Repositories\BaseRepository;

class HostelAllocationRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'student_id',
        'hostel_id',
        'room_id',
        'bed_number',
        'allocation_date',
        'vacating_date',
        'status',
        'academic_year_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return HostelAllocation::class;
    }
}
