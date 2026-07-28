<?php

namespace App\Policies;

use App\Models\FeePayment;
use App\Models\Student;
use App\Models\User;
use App\Models\Parents;

class FeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Accountant']);
    }

    public function view(User $user, ?FeePayment $payment = null): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Accountant'])) {
            return true;
        }

        if ($user->hasRole('Parent') && $payment) {
            return $this->parentOwnsPayment($user, $payment);
        }

        if ($user->hasRole('Student') && $payment) {
            return $this->studentIsPaymentOwner($user, $payment);
        }

        return false;
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('fees.manage');
    }

    public function collect(User $user): bool
    {
        return $user->hasPermission('fees.collect');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermission('fees.approve');
    }

    /**
     * A parent can view a fee payment if the student linked to the payment
     * is their child.
     */
    protected function parentOwnsPayment(User $user, FeePayment $payment): bool
    {
        $parent = Parents::where('user_id', $user->id)->first();
        if (!$parent) {
            return false;
        }

        $studentFeeAssignment = $payment->studentFeeAssignment;
        if (!$studentFeeAssignment) {
            return false;
        }

        return Student::where('student_id', $studentFeeAssignment->student_id)
            ->whereHas('parents', fn($q) => $q->where('parent_id', $parent->parent_id))
            ->exists();
    }

    /**
     * A student can only view their own fee payments.
     */
    protected function studentIsPaymentOwner(User $user, FeePayment $payment): bool
    {
        $studentFeeAssignment = $payment->studentFeeAssignment;
        if (!$studentFeeAssignment) {
            return false;
        }

        $student = Student::where('user_id', $user->id)->first();
        return $student && $studentFeeAssignment->student_id === $student->student_id;
    }
}
