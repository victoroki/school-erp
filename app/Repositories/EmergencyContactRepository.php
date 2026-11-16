<?php

namespace App\Repositories;

use App\Models\EmergencyContact;
use App\Repositories\BaseRepository;

class EmergencyContactRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'student_id',
        'name',
        'relationship',
        'phone',
        'phone_2',
        'email',
        'address',
        'priority',
        'is_authorized_pickup'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return EmergencyContact::class;
    }
}
