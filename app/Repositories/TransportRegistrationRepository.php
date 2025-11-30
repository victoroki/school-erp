<?php

namespace App\Repositories;

use App\Models\TransportRegistration;
use App\Repositories\BaseRepository;

class TransportRegistrationRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'student_id',
        'route_id',
        'stop_id',
        'fee_amount',
        'payment_status',
        'academic_year_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return TransportRegistration::class;
    }
}