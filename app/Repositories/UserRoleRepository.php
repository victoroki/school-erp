<?php

namespace App\Repositories;

use App\Models\UserRole;
use App\Repositories\BaseRepository;

class UserRoleRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'role_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return UserRole::class;
    }
}
