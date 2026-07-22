<?php

namespace App\Policies;

use App\Models\User;

class CommunicationPolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('communication.manage');
    }

    public function print(User $user): bool
    {
        return $user->hasPermission('communication.print');
    }
}
