<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\StudentClassEnrollment;
use App\Models\Timetable;
use App\Models\Homework;
use App\Models\StudentFeeAssignment;
use App\Models\Notification;
use App\Models\Period;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileStudentDashboardController extends Controller
{
    /**
     * GET /api/mobile/dashboard
     *
     * Aggregated data for the student's home screen.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasRole('Student')) {
            return response()->json(['error' => 'Only students can access this dashboard.'], 403);
        }

        $year = AcademicYear::where('is_current', true)->first();
        if (!$year) {
            return response()->json(['error' => 'No current academic year set.'], 404);
        }

        $enrollment = StudentClassEnrollment::where('student_id', $user->student?->student_id)
            ->where('academic_year_id', $year->academic_year_id)
            ->with(['classSection.class', 'classSection.section'])
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'No active class enrollment found.'], 404);
        }

        $classSection = $enrollment->classSection;
        $className = trim(($classSection->class->name ?? '') . ' - ' . ($classSection->section->name ?? ''));

        // 1. Next Class
        $dayOfWeek = strtolower(now()->format('l'));
        $currentTime = now()->format('H:i:s');
        $nextClass = Timetable::with(['subject', 'period', 'classroom'])
            ->where('class_section_id', $classSection->class_section_id)
            ->where('day_of_week', $dayOfWeek)
            ->whereHas('period', fn($q) => $q->where('start_time', '>', $currentTime))
            ->orderBy(
                Period::select('start_time')
                    ->whereColumn('periods.period_id', 'timetable.period_id'),
                'asc'
            )
            ->first();

        // 2. Pending Homework
        // Note: Homework model uses 'class_name'.
        $pendingHomework = Homework::where('class_name', $className)
            ->where('due_date', '>=', now()->toDateString())
            ->orderBy('due_date', 'asc')
            ->limit(3)
            ->get();

        // 3. Fee Balance
        $feeAssignment = StudentFeeAssignment::where('student_id', $user->student?->student_id)
            ->where('status', 'active')
            ->first();
        $balance = $feeAssignment ? (($feeAssignment->final_amount ?? 0) - $feeAssignment->paid_amount) : 0;

        // 4. Recent Notices (delivered via NotificationRecipient rows)
        $notices = Notification::whereHas('recipients', function ($q) use ($user) {
            $q->where('recipient_id', $user->id);
        })
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return response()->json([
            'student_name' => $user->name,
            'current_class' => $className,
            'next_class' => $nextClass ? [
                'subject' => $nextClass->subject->name,
                'time'    => $nextClass->period->start_time,
                'room'    => $nextClass->classroom->room_number,
            ] : null,
            'homework' => $pendingHomework->map(fn($h) => [
                'title' => $h->title,
                'due'   => $h->due_date->toDateString(),
                'subject' => $h->subject,
            ]),
            'fees' => [
                'balance' => (float) $balance,
                'status'  => $balance <= 0 ? 'Clear' : 'Pending',
            ],
            'notifications' => $notices->map(fn($n) => [
                'title' => $n->title,
                'body'  => $n->message,
                'date'  => $n->created_at->diffForHumans(),
            ]),
        ]);
    }
}