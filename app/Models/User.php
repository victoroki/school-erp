<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    /**
     * The user's staff record (if any). Used by HR policies for
     * HOD/department scoping and by teacher scoping.
     */
    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class, 'user_id');
    }

    /**
     * Check if the user has a role with the given name.
     * Operates on the already-loaded roles collection (no extra query).
     */
    public function hasRole(string $role): bool
    {
        return $this->roles->contains('role_name', $role);
    }

    /**
     * Check if the user holds any of the given role names.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->whereIn('role_name', $roles)->isNotEmpty();
    }

    public function assignRole($role): void
    {
        if (is_string($role)) {
            $role = Role::where('role_name', $role)->firstOrFail();
        }
        $this->roles()->syncWithoutDetaching($role);
    }

    /**
     * Check if the user has a specific permission.
     *
     * IMPORTANT: roles.permissions must be eager-loaded before this is called
     * (done by Authenticate middleware). If not loaded, falls back to loading
     * them now (slower path, for safety).
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->relationLoaded('roles')) {
            $this->load('roles.permissions');
        }

        return $this->roles->flatMap->permissions
            ->pluck('permission_name')
            ->contains($permission);
    }

    /**
     * Get all permission names the user holds (flattened from all roles).
     */
    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        if (!$this->relationLoaded('roles')) {
            $this->load('roles.permissions');
        }

        return $this->roles->flatMap->permissions
            ->pluck('permission_name')
            ->unique();
    }
}
