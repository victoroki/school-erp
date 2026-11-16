<?php

namespace App\Policies;

use App\Models\User;

class ModulePolicy
{
    /**
     * Enabling/disabling modules (the paid feature switches) is reserved for
     * the platform Owner. Regular permission-based checks never gate this
     * screen, matching the audit-trail pattern of role-based (not
     * permission-based) enforcement.
     */
    public function toggle(User $user): bool
    {
        return $user->isOwner();
    }
}
