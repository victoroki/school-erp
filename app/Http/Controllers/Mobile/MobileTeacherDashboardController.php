<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Staff;
use App\Models\StudentAttendance;
use App\Models\Notification;
use App\Services\TeacherMobileHomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileTeacherDashboardController extends Controller
{
    /**
     * GET /api/mobile/teacher/dashboard
     *
     * Aggregated high-value data for the teacher's home screen. All
     * sense-making queries (next lesson, pending registers, pending marks)
     * live in TeacherMobileHomeService so this controller stays thin.
     */
    public function __invoke(Request $request, TeacherMobileHomeService $home): JsonResponse
    {
        $user = $request->user();
        $staff = Staff::where('user_id', $user->id)->first();

        if (!$staff) {
            return response()->json(['error' => 'Staff record not found.'], 404);
        }

        $year = AcademicYear::where('is_current', true)->first();
        if (!$year) {
            return response()->json(['error' => 'No current academic year set.'], 404);
        }

        $today = now()->toDateString();

        // Attendance pulse: how many of this teacher's students are absent
        // today (Sense-making: "Is my class missing people?").
        $absentToday = StudentAttendance::where('date', $today)
            ->where('status', 'absent')
            ->whereHas('student.studentClassEnrollments.classSection.timetables', function ($q) use ($staff) {
                $q->where('teacher_id', $staff->staff_id);
            })
            ->count();

        // Critical alerts — real notification rows only.
        $alerts = Notification::whereHas('recipients', function ($q) use ($user) {
            $q->where('recipient_id', $user->id);
        })
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return response()->json([
            'teacher_name' => trim($staff->first_name . ' ' . $staff->last_name),
            'date'         => $today,
            'next_class'   => $home->nextLesson($user, $staff),
            'pending_registers' => $home->pendingRegisters($user, $today),
            'pending_marks'     => $home->pendingMarks($user, $staff),
            'stats' => [
                'absent_students_today' => $absentToday,
            ],
            'notifications' => $alerts->map(fn ($n) => [
                'title' => $n->title,
                'body'  => $n->message,
                'date'  => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    /**
     * GET /api/mobile/teacher/pending-registers?date=YYYY-MM-DD
     *
     * Authoritative list of class registers still outstanding for a date —
     * computed server-side from real enrollments + attendance rows. Pull-to-
     * refresh on the teacher's home calls this without a full dashboard sync.
     */
    public function registers(Request $request, TeacherMobileHomeService $home): JsonResponse
    {
        $request->validate([
            'date' => 'nullable|date',
        ]);

        $user = $request->user();
        $date = $request->query('date', now()->toDateString());

        return response()->json([
            'date'    => $date,
            'pending' => $home->pendingRegisters($user, $date),
        ]);
    }
}
