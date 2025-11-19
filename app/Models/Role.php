<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $table = 'roles';
    protected $primaryKey = 'role_id';

    public $fillable = [
        'role_name',
        'description'
    ];

    protected $casts = [
        'role_name' => 'string',
        'description' => 'string'
    ];

    public static array $rules = [
        'role_name' => 'required|string|max:50',
        'description' => 'nullable|string|max:65535'
    ];

    public function rolePermission(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\RolePermission::class);
    }

    public function userRoles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\UserRole::class, 'role_id');
    }
}
