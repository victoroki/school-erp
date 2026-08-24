<?php

namespace App\Policies;

use App\Models\User;

class ExamPolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Teacher']);
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('academics.settings.manage');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermission('exams.approve');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('exams.import');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('exams.report-cards.export');
    }
}
