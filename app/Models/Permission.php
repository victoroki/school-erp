<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public $table = 'permissions';

    public $fillable = [
        'permission_name',
        'description'
    ];

    protected $casts = [
        'permission_name' => 'string',
        'description' => 'string'
    ];

    public static array $rules = [
        'permission_name' => 'required|string|max:100',
        'description' => 'nullable|string|max:65535'
    ];

    public function rolePermissions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\RolePermission::class, 'permission_id');
    }
}
