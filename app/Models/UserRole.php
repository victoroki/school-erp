<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    public $table = 'user_roles';

    public $fillable = [
        'user_id',
        'role_id'
    ];

    protected $casts = [
        
    ];

    public static array $rules = [
        'user_id' => 'required',
        'role_id' => 'required'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Role::class, 'role_id');
    }
}
