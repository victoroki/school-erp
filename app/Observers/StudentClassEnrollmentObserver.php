<?php

namespace App\Observers;

use App\Models\StudentClassEnrollment;
use App\Services\FeeAssignmentService;

class StudentClassEnrollmentObserver
{
    public function __construct(
        protected FeeAssignmentService $feeAssignmentService
    ) {
    }

    public function created(StudentClassEnrollment $enrollment): void
    {
        if (!$enrollment->is_current || !$enrollment->student) {
            return;
        }

        $student = $enrollment->student;

        if (!$student->is_active) {
            return;
        }

        $this->feeAssignmentService->autoAssignFeesToStudent(
            $student,
            $enrollment->academic_year_id
        );
    }

    public function updated(StudentClassEnrollment $enrollment): void
    {
        if ($enrollment->wasChanged('is_current') && $enrollment->is_current) {
            $student = $enrollment->student;

            if (!$student || !$student->is_active) {
                return;
            }

            $this->feeAssignmentService->autoAssignFeesToStudent(
                $student,
                $enrollment->academic_year_id
            );
        }
    }
}
