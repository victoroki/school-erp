<?php

namespace App\Repositories;

use App\Models\Hostel;
use App\Repositories\BaseRepository;

class HostelRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'type',
        'address',
        'warden_id',
        'capacity'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Hostel::class;
    }
}
