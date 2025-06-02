<?php

namespace App\Repositories;

use App\Models\RolePermission;
use App\Repositories\BaseRepository;

class RolePermissionRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'permission_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return RolePermission::class;
    }
}
