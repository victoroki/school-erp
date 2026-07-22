<?php

namespace App\Policies;

use App\Models\User;

class HostelPolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('hostel.manage');
    }
}
