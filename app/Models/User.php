<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('role_name', $role);
        }
        return $role->intersect($this->roles)->count() > 0;
    }

    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = \App\Models\Role::where('role_name', $role)->firstOrFail();
        }
        $this->roles()->syncWithoutDetaching($role);
    }

    public function hasPermission($permission)
    {
        return $this->roles->flatMap->permissions->pluck('permission_name')->contains($permission);
    }
}
