<?php

namespace App\Observers;

use App\Models\ExamResult;

/**
 * ExamResultObserver
 *
 * NOTE: Result notifications to parents are intentionally NOT fired here.
 * Firing on `created` would notify parents on every raw mark entry before
 * the marks have been reviewed and approved.
 *
 * The correct place to dispatch the `exam_result_approved` notification is
 * MarksApprovalController::approve(), which runs only after an authorised
 * user has explicitly approved the batch.  That controller checks the
 * exam's `publish_result` flag before dispatching.
 */
class ExamResultObserver
{
    // Intentionally empty — see note above.
}
