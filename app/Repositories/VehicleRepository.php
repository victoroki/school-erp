<?php

namespace App\Repositories;

use App\Models\Vehicle;
use App\Repositories\BaseRepository;

class VehicleRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'vehicle_number',
        'vehicle_type',
        'model',
        'make',
        'year',
        'seating_capacity',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Vehicle::class;
    }
}