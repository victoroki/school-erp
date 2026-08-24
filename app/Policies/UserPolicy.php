<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * True when the target account holds the platform Owner role. Those
     * accounts belong to the SaaS provider and are invisible and untouchable
     * for school staff — even Super Admins.
     */
    private function targetIsPlatformOwner(User $model): bool
    {
        if (! $model->relationLoaded('roles')) {
            $model->load('roles');
        }

        return $model->roles->contains('role_name', 'Owner');
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('users.view')
            || $user->hasPermission('users.manage');
    }

    public function view(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        if ($this->targetIsPlatformOwner($model) && ! $user->isOwner()) {
            return false;
        }

        if ($model->is_hidden && ! $user->canBypassProtection()) {
            return false;
        }

        return $user->hasPermission('users.view')
            || $user->hasPermission('users.manage');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function assignRoles(User $user): bool
    {
        return $user->hasPermission('users.manage')
            || $user->hasPermission('roles.manage');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('users.manage');
    }

    public function update(User $user, User $model): bool
    {
        if ($this->targetIsPlatformOwner($model) && ! $user->isOwner()) {
            return false;
        }

        if (! $user->hasPermission('users.manage')) {
            return false;
        }

        return $user->canBypassProtection() || ! $model->is_protected;
    }

    public function resetPassword(User $user, User $model): bool
    {
        if ($this->targetIsPlatformOwner($model) && ! $user->isOwner()) {
            return false;
        }

        if (! $user->hasPermission('users.manage')) {
            return false;
        }

        return $user->canBypassProtection() || ! $model->is_protected;
    }

    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        if ($this->targetIsPlatformOwner($model) && ! $user->isOwner()) {
            return false;
        }

        if (! $user->hasPermission('users.manage')) {
            return false;
        }

        return $user->canBypassProtection() || ! $model->is_protected;
    }
}
