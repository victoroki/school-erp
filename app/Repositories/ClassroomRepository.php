<?php

namespace App\Repositories;

use App\Models\Classroom;
use App\Repositories\BaseRepository;

class ClassroomRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'room_number',
        'building',
        'floor',
        'capacity',
        'has_sockets',
        'has_whiteboard'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Classroom::class;
    }
}
