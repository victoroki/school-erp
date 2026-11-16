<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    /**
     * True for the platform Owner role. It belongs to the SaaS provider and
     * can never be renamed, re-permissioned or deleted by school staff —
     * even Super Admins.
     */
    private function isPlatformOwnerRole(Role $role): bool
    {
        return $role->role_name === 'Owner';
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.view')
            || $user->hasPermission('users.manage');
    }

    public function view(User $user, Role $role): bool
    {
        if ($this->isPlatformOwnerRole($role) && ! $user->isOwner()) {
            return false;
        }

        if ($role->is_hidden && ! $user->canBypassProtection()) {
            return false;
        }

        return $user->hasPermission('users.view')
            || $user->hasPermission('users.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function update(User $user, Role $role): bool
    {
        if ($this->isPlatformOwnerRole($role) && ! $user->isOwner()) {
            return false;
        }

        if (! $user->hasPermission('users.manage')) {
            return false;
        }

        return $user->canBypassProtection() || ! $role->is_protected;
    }

    public function delete(User $user, Role $role): bool
    {
        if ($this->isPlatformOwnerRole($role) && ! $user->isOwner()) {
            return false;
        }

        if ($role->is_protected && ! $user->canBypassProtection()) {
            return false;
        }

        if ($role->users()->exists()) {
            return false;
        }

        return $user->hasPermission('users.manage');
    }
}
