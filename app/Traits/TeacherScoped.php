<?php

namespace App\Traits;

use App\Services\TeacherScopeService;
use Illuminate\Support\Facades\Auth;

/**
 * Apply teacher scoping to any Eloquent query.
 *
 * Usage in a controller:
 *   $this->scopeByTeacher(ExamResult::query(), 'class_section_id')
 *       ->where(...)
 *       ->get();
 */
trait TeacherScoped
{
    protected ?TeacherScopeService $teacherScopeService = null;

    protected function getTeacherScope(): TeacherScopeService
    {
        if (!$this->teacherScopeService) {
            $this->teacherScopeService = app(TeacherScopeService::class);
        }
        return $this->teacherScopeService;
    }

    /**
     * Scope a query by the current teacher's class section IDs.
     */
    protected function scopeByTeacher($query, string $column = 'class_section_id')
    {
        return $this->getTeacherScope()->scopeByClassSections($query, Auth::user(), $column);
    }

    /**
     * Scope a query by the current teacher's subject IDs.
     */
    protected function scopeByTeacherSubjects($query, string $column = 'subject_id')
    {
        return $this->getTeacherScope()->scopeBySubjects($query, Auth::user(), $column);
    }

    /**
     * Get the authenticated user's teacher class section IDs.
     */
    protected function teacherClassSectionIds(): array
    {
        return $this->getTeacherScope()
            ->getClassSectionIds(Auth::user())
            ->toArray();
    }

    /**
     * Get the authenticated user's teacher subject IDs.
     */
    protected function teacherSubjectIds(): array
    {
        return $this->getTeacherScope()
            ->getSubjectIds(Auth::user())
            ->toArray();
    }
}
