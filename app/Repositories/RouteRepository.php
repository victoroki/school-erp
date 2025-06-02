<?php

namespace App\Repositories;

use App\Models\Route;
use App\Repositories\BaseRepository;

class RouteRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'description',
        'start_point',
        'end_point',
        'distance'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Route::class;
    }
}
