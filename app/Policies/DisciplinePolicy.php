<?php

namespace App\Policies;

use App\Models\User;

class DisciplinePolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Teacher']);
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('discipline.manage');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermission('discipline.approve');
    }
}
