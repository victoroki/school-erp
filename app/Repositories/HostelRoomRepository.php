<?php

namespace App\Repositories;

use App\Models\HostelRoom;
use App\Repositories\BaseRepository;

class HostelRoomRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'hostel_id',
        'room_number',
        'room_type',
        'capacity',
        'occupied',
        'floor',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return HostelRoom::class;
    }
}
