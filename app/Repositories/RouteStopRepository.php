<?php

namespace App\Repositories;

use App\Models\RouteStop;
use App\Repositories\BaseRepository;

class RouteStopRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'route_id',
        'stop_name',
        'stop_time',
        'sequence'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return RouteStop::class;
    }
}
