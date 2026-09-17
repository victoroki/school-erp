<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\StudentClassEnrollment;
use App\Models\Homework;
use App\Models\Notification;
use App\Models\Student;
use App\Services\PortalScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileStudentDashboardController extends Controller
{
    /**
     * GET /api/mobile/dashboard
     *
     * Aggregated data for the student's home screen. PHASE 4: the per-student
     * rollup is PortalScopeService::childOverview computed for the caller's
     * OWN record — a Student user can never steer it with a student_id from
     * the device (STEP 3/20). The legacy keys (next_class, homework, fees,
     * notifications) keep their shapes; fee arithmetic now covers ALL active
     * assignments (previously one row, understating balances), and attendance,
     * latest results and upcoming exams join the payload.
     */
    public function __invoke(Request $request, PortalScopeService $scope): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasRole('Student')) {
            return response()->json(['error' => 'Only students can access this dashboard.'], 403);
        }

        $year = AcademicYear::where('is_current', true)->first();
        if (!$year) {
            return response()->json(['error' => 'No current academic year set.'], 404);
        }

        $student = PortalScopeService::selfStudent($user);
        if (!$student) {
            return response()->json(['error' => 'No student record for this account.'], 404);
        }

        $overview = $scope->childOverview((int) $student->student_id);

        // Pending homework — same filter as the rollup, kept as its own block
        // for the legacy contract (all due items, not just five).
        $enrollment = PortalScopeService::enrollment($student->student_id);
        $label = $enrollment ? PortalScopeService::normalizeLabel(PortalScopeService::className($enrollment) ?? '') : '';
        $pendingHomework = collect();
        if ($label !== '') {
            $pendingHomework = Homework::where('status', 'active')
                ->where('due_date', '>=', now()->toDateString())
                ->orderBy('due_date', 'asc')
                ->get()
                ->filter(function ($h) use ($label) {
                    if (!$h->class_name || trim($h->class_name) === '') {
                        return true;
                    }

                    return PortalScopeService::normalizeLabel($h->class_name) === $label;
                })
                ->values();
        }

        // Recent Notices (delivered via NotificationRecipient rows)
        $notices = Notification::whereHas('recipients', function ($q) use ($user) {
            $q->where('recipient_id', $user->id);
        })
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // Latest published result summary (report cards for self only).
        $cards = app(\App\Http\Controllers\Mobile\MobileReportController::class)
            ->reportCards($request)
            ->getData(true);
        $own = collect($cards)
            ->filter(fn ($c) => (int) ($c['student_id'] ?? -1) === (int) $student->student_id)
            ->sortByDesc(fn ($c) => $c['year'] . '|' . ($c['examName'] ?? ''))
            ->first();
        $latestResult = $own ? [
            'examName' => $own['examName'],
            'term' => $own['term'],
            'average' => $own['average'] ?? null,
            'position' => $own['position'] ?? null,
        ] : null;

        $ownStatuses = \App\Models\HomeworkSubmission::where('student_id', $student->student_id)
            ->whereIn('homework_id', $pendingHomework->pluck('id'))
            ->pluck('status', 'homework_id');

        return response()->json([
            'student_name' => $user->name,
            'current_class' => $overview['class'] ?? 'N/A',
            'next_class' => $overview['timetable']['next_lesson'] ? [
                'subject' => $overview['timetable']['next_lesson']['subject'],
                'time'    => $overview['timetable']['next_lesson']['start_time'],
                'room'    => $overview['timetable']['next_lesson']['room'],
            ] : null,
            'homework' => $pendingHomework->map(fn ($h) => [
                'id' => $h->id,
                'title' => $h->title,
                'due'   => $h->due_date?->toDateString(),
                'subject' => $h->subject,
                'submission_status' => $ownStatuses[$h->id] ?? null,
            ]),
            'fees' => [
                'balance' => $overview['fees']['balance'],
                'status'  => $overview['fees']['balance'] <= 0 ? 'Clear' : 'Pending',
            ],
            'notifications' => $notices->map(fn ($n) => [
                'title' => $n->title,
                'body'  => $n->message,
                'date'  => $n->created_at->diffForHumans(),
            ]),
            // ── Phase 4 additions ──────────────────────────────────────────
            'attendance' => $overview['attendance'],
            'today' => [
                'day' => $overview['timetable']['day'],
                'lessons' => $overview['timetable']['lessons'],
            ],
            'upcoming_exams' => $overview['upcoming_exams'],
            'latest_result' => $latestResult,
            'student_id' => (int) $student->student_id,
        ]);
    }
}
