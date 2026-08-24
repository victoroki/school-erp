<?php

namespace App\Services;

use App\Models\User;
use App\Models\Staff;
use App\Models\TeacherSubject;
use App\Models\ClassSection;
use Illuminate\Support\Collection;

/**
 * Resolves a teacher's assigned class_section_ids and subject_ids
 * from the teacher_subjects pivot table and class_teacher assignments.
 */
class TeacherScopeService
{
    /**
     * Get all class_section_ids this teacher is assigned to.
     * Union of: teacher_subjects assignments + class_teacher (homeroom) assignments.
     */
    public function getClassSectionIds(User $user): Collection
    {
        $staff = $this->resolveStaff($user);
        if (!$staff) {
            return collect();
        }

        $fromSubjects = TeacherSubject::where('staff_id', $staff->staff_id)
            ->pluck('class_section_id');

        $fromClassTeacher = ClassSection::where('class_teacher_id', $staff->staff_id)
            ->pluck('class_section_id');

        return $fromSubjects->merge($fromClassTeacher)->unique()->values();
    }

    /**
     * Get all subject_ids this teacher is assigned to teach.
     */
    public function getSubjectIds(User $user): Collection
    {
        $staff = $this->resolveStaff($user);
        if (!$staff) {
            return collect();
        }

        return TeacherSubject::where('staff_id', $staff->staff_id)
            ->pluck('subject_id')
            ->unique()
            ->values();
    }

    /**
     * Get the class_ids derived from the teacher's assigned class sections.
     * exam_schedules references classes (class_id), not class sections.
     */
    public function getClassIds(User $user): Collection
    {
        $sectionIds = $this->getClassSectionIds($user);
        if ($sectionIds->isEmpty()) {
            return collect();
        }

        return ClassSection::whereIn('class_section_id', $sectionIds)
            ->pluck('class_id')
            ->unique()
            ->values();
    }

    /**
     * Scope an Exam query to only the exams scheduled for the teacher's classes.
     * Exams with no schedules at all remain visible.
     */
    public function scopeExams($query, User $user)
    {
        $classIds = $this->getClassIds($user);
        if ($classIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($classIds) {
            $q->whereHas('examSchedules', function ($s) use ($classIds) {
                $s->whereIn('class_id', $classIds);
            })->orWhereDoesntHave('examSchedules');
        });
    }

    /**
     * Apply a class_section_id scope to a query builder.
     * Falls back to empty scope (no results) if user is not a teacher/staff.
     */
    public function scopeByClassSections($query, User $user, string $column = 'class_section_id')
    {
        $ids = $this->getClassSectionIds($user);
        if ($ids->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }
        return $query->whereIn($column, $ids);
    }

    /**
     * Apply a subject_id scope to a query builder.
     */
    public function scopeBySubjects($query, User $user, string $column = 'subject_id')
    {
        $ids = $this->getSubjectIds($user);
        if ($ids->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }
        return $query->whereIn($column, $ids);
    }

    private function resolveStaff(User $user): ?Staff
    {
        return $user->relationLoaded('staff')
            ? $user->staff
            : Staff::where('user_id', $user->id)->first();
    }
}
