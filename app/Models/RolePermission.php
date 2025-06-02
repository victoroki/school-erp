<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    public $table = 'role_permissions';

    public $fillable = [
        'permission_id'
    ];

    protected $casts = [
        
    ];

    public static array $rules = [
        'permission_id' => 'required'
    ];

    public function permission(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Permission::class, 'permission_id');
    }

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Role::class, 'role_id');
    }
}
