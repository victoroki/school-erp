<?php

namespace App\Policies;

use App\Models\User;

class AcademicsPolicy
{
    public function view(User $user): bool
    {
        // The platform Owner outranks every operational role (config/rbac.php).
        // Without this the Owner was refused academic modules that Super Admin
        // and Admin reached fine.
        return $user->isSuperUser()
            || $user->hasAnyRole(['Super Admin', 'Admin', 'Teacher']);
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('academics.settings.manage');
    }
}
