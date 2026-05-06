<?php

namespace App\Observers;

use App\Models\Student;
use App\Services\FeeAssignmentService;

class StudentObserver
{
    public function __construct(
        protected FeeAssignmentService $feeAssignmentService
    ) {
    }

    public function created(Student $student): void
    {
        if (!$student->is_active) {
            return;
        }

        $enrollment = $student->studentClassEnrollments()
            ->where('is_current', true)
            ->first();

        if (!$enrollment) {
            return;
        }

        $this->feeAssignmentService->autoAssignFeesToStudent(
            $student,
            $enrollment->academic_year_id
        );
    }

    public function updated(Student $student): void
    {
        if ($student->wasChanged('is_active') && $student->is_active) {
            $enrollment = $student->studentClassEnrollments()
                ->where('is_current', true)
                ->first();

            if ($enrollment) {
                $this->feeAssignmentService->autoAssignFeesToStudent(
                    $student,
                    $enrollment->academic_year_id
                );
            }
        }
    }
}
