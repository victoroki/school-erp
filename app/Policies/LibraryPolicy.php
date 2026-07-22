<?php

namespace App\Policies;

use App\Models\User;

class LibraryPolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('library.manage');
    }

    public function print(User $user): bool
    {
        return $user->hasPermission('library.print');
    }
}
