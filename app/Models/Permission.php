<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public $table = 'permissions';
    protected $primaryKey = 'permission_id';

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

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Role::class, 'role_permissions', 'permission_id', 'role_id');
    }
}
