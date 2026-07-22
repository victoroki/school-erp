<?php

namespace App\Policies;

use App\Models\User;

class AcademicsPolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Teacher']);
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('academics.manage');
    }
}
