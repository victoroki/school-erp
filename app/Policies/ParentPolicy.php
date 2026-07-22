<?php

namespace App\Policies;

use App\Models\Parents;
use App\Models\User;

class ParentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }

    public function view(User $user, Parents $parent): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin'])) {
            return true;
        }

        if ($user->hasRole('Parent')) {
            return $parent->user_id === $user->id;
        }

        return false;
    }

    public function manage(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }

    /**
     * Parent portal access — any user with role 'Parent' can access
     * portal-level parent features (their own profile, their children).
     */
    public function portalAccess(User $user): bool
    {
        return $user->hasRole('Parent');
    }
}
