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
        'user_type',
        'is_active',
        'email_verified_at',
        'is_protected',
        'is_hidden',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'is_protected' => 'boolean',
        'is_hidden' => 'boolean',
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
     * The parent record linked to this user account (if any).
     * Parent accounts use this to scope queries to their linked children.
     */
    public function parent(): HasOne
    {
        return $this->hasOne(\App\Models\Parents::class, 'user_id');
    }

    /**
     * The student record linked to this user account (if any).
     * Student accounts use this to scope queries to their own data.
     */
    public function student(): HasOne
    {
        return $this->hasOne(\App\Models\Student::class, 'user_id');
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
     * True when the user holds a protected role (e.g. Super Admin, Owner).
     * Protected roles may modify other protected roles/accounts.
     */
    public function canBypassProtection(): bool
    {
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        return $this->roles->contains('is_protected', true);
    }

    /**
     * True only for the SaaS platform owner (the developer's account, seeded
     * per deployment via OwnerSeeder). The Owner role is never granted by the
     * setup flow and is the sole key to the Administration module (modules,
     * audit trail, system logs).
     */
    public function isOwner(): bool
    {
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        return $this->roles->contains('role_name', 'Owner');
    }

    /**
     * True when the user holds a super role (config('rbac.super_roles')).
     *
     * This is the single place the application answers "outranks everybody?".
     * `hasPermission()` consults it, and the Gate::before hook consults it, so
     * every `can:` middleware check, every `@can` in Blade and every policy
     * method admits a super user — including policies whose role list never
     * mentioned the Owner role, which is exactly how the platform owner ended
     * up refused by /medical-incidents, homework and student notices.
     *
     * Deliberately *not* the same thing as canBypassProtection(): that one
     * answers "may this user modify protected accounts", which is a narrower
     * question about destructive actions, not about module access.
     */
    public function isSuperUser(): bool
    {
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        $superRoles = (array) config('rbac.super_roles', ['Owner']);

        // Deliberately not `$this->roles->contains('role_name', $superRoles)`.
        // That two-argument form routes into Collection::operatorForWhere(),
        // which compares each role name loosely against the whole array
        // (`'Owner' == ['Owner']`) — always false in PHP 8, so a configurable
        // list would silently match nothing.
        return $this->roles->contains(
            fn ($role) => in_array($role->role_name, $superRoles, true)
        );
    }

    /**
     * Check if the user has a specific permission.
     *
     * A super user holds every permission. Short-circuiting here — rather than
     * in each middleware, policy and Blade @can — is what makes that a single
     * decision instead of forty.
     *
     * IMPORTANT: roles.permissions must be eager-loaded before this is called
     * (done by the `permissions` middleware). If not loaded, falls back to
     * loading them now (slower path, for safety).
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        if (!$this->relationLoaded('roles')) {
            $this->load('roles.permissions');
        }

        return $this->roles->flatMap->permissions
            ->pluck('permission_name')
            ->contains($permission);
    }

    /**
     * Get all permission names the user holds (flattened from all roles).
     *
     * A super user holds every permission, so the full catalogue is returned
     * rather than the (possibly incomplete) grants recorded on the pivot.
     */
    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        if ($this->isSuperUser()) {
            return Permission::query()->pluck('permission_name');
        }

        if (!$this->relationLoaded('roles')) {
            $this->load('roles.permissions');
        }

        return $this->roles->flatMap->permissions
            ->pluck('permission_name')
            ->unique();
    }
}
