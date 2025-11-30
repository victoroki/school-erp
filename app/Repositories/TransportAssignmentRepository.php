<?php

namespace App\Repositories;

use App\Models\TransportAssignment;
use App\Repositories\BaseRepository;

class TransportAssignmentRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'route_id',
        'vehicle_id',
        'driver_id',
        'assistant_id',
        'departure_time',
        'return_time',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return TransportAssignment::class;
    }
}