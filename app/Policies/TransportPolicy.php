<?php

namespace App\Policies;

use App\Models\User;

class TransportPolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('transport.manage');
    }

    public function print(User $user): bool
    {
        return $user->hasPermission('transport.print');
    }
}
