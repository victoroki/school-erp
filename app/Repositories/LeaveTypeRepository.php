<?php

namespace App\Repositories;

use App\Models\LeaveType;
use App\Repositories\BaseRepository;

class LeaveTypeRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'days_allowed',
        'description',
        'is_paid'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return LeaveType::class;
    }
}
