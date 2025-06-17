<?php

namespace App\Repositories;

use App\Models\Staff;
use App\Repositories\BaseRepository;

class StaffRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'user_id',
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'joining_date',
        'department_id',
        'designation',
        'qualification',
        'experience',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'photo_url',
        'staff_type',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Staff::class;
    }
}
