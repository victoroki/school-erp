<?php

namespace App\Repositories;

use App\Models\Parents;
use App\Repositories\BaseRepository;

class ParentsRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'user_id',
        'first_name',
        'last_name',
        'relationship',
        'email',
        'phone',
        'alternate_phone',
        'occupation'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Parents::class;
    }
}
