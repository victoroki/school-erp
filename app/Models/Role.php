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

    public function permissions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\User::class, 'user_roles', 'role_id', 'user_id');
    }
}
