<?php

namespace App\Observers;

use App\Events\NotificationTriggered;
use App\Models\ExamResult;

class ExamResultObserver
{
    public function created(ExamResult $result): void
    {
        $exam = $result->exam;
        if (!$exam || !$exam->publish_result) {
            return;
        }

        $subject = $result->subject?->name ?? '';
        $grade = $result->grade?->grade ?? '';
        $remarks = $result->remarks ?? '';

        event(new NotificationTriggered(
            triggerType: 'exam_published',
            studentId: $result->student_id,
            triggerModel: ExamResult::class,
            triggerId: $result->result_id,
            context: [
                'subject_name' => $subject,
                'marks' => (string) $result->marks_obtained,
                'grade' => $grade,
                'remarks' => $remarks,
            ]
        ));
    }
}
