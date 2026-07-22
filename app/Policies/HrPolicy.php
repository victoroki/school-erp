<?php

namespace App\Policies;

use App\Models\LeaveApplication;
use App\Models\Staff;
use App\Models\User;

class HrPolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('hr.manage');
    }

    public function approve(User $user, ?LeaveApplication $leave = null): bool
    {
        if (!$user->hasPermission('hr.approve')) {
            return false;
        }

        if (!$leave) {
            return true;
        }

        return $this->canApproveLeave($user, $leave);
    }

    /**
     * HOD can approve leave for staff in their department.
     * HR Manager / Admin can approve all leave.
     */
    protected function canApproveLeave(User $user, LeaveApplication $leave): bool
    {
        $staff = Staff::where('user_id', $user->id)->first();
        if (!$staff) {
            return false;
        }

        $applicantStaff = Staff::find($leave->staff_id);
        if (!$applicantStaff) {
            return false;
        }

        if ($staff->staff_type === 'non_teaching' && $user->hasRole('Admin')) {
            return true;
        }

        return $staff->department_id === $applicantStaff->department_id
            && $applicantStaff->reporting_manager_id === $staff->staff_id;
    }
}
