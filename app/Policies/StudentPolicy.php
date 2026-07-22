<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Models\Staff;
use App\Models\Parents;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Accountant', 'Teacher']);
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin'])) {
            return true;
        }

        if ($user->hasRole('Accountant')) {
            return true;
        }

        if ($user->hasRole('Teacher')) {
            return $this->teacherOwnsStudent($user, $student);
        }

        if ($user->hasRole('Parent')) {
            return $this->parentOwnsStudent($user, $student);
        }

        if ($user->hasRole('Student')) {
            return $this->studentIsSelf($user, $student);
        }

        return false;
    }

    public function manage(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('students.import');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('students.export');
    }

    /**
     * A teacher can view a student if the student is enrolled in a class_section
     * assigned to the teacher via teacher_subjects.
     */
    protected function teacherOwnsStudent(User $user, Student $student): bool
    {
        $staff = $user->staff;
        if (!$staff) {
            return false;
        }

        $teacherClassSections = $staff->teacherSubjects()
            ->pluck('class_section_id')
            ->unique();

        return $student->studentClassEnrollments()
            ->whereIn('class_section_id', $teacherClassSections)
            ->exists();
    }

    /**
     * A parent can view a student if the student is linked to the parent
     * via student_parent_relationship.
     */
    protected function parentOwnsStudent(User $user, Student $student): bool
    {
        $parent = Parents::where('user_id', $user->id)->first();
        if (!$parent) {
            return false;
        }

        return $student->parents()->where('parent_id', $parent->parent_id)->exists();
    }

    /**
     * A student can only view their own profile.
     */
    protected function studentIsSelf(User $user, Student $student): bool
    {
        return $student->user_id === $user->id;
    }
}
