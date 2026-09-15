<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\Staff;
use App\Models\AcademicYear;
use App\Models\StudentClassEnrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileTimetableController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $year = AcademicYear::where('is_current', true)->first();

        if (!$year) {
            return response()->json(['error' => 'No current academic year set.'], 404);
        }

        $timetables = collect();

        if ($user->user_type === 'teacher' || $user->hasRole('Teacher')) {
            $staff = Staff::where('user_id', $user->id)->first();
            if (!$staff) {
                return response()->json(['error' => 'Staff record not found for this user.'], 404);
            }

            $timetables = Timetable::with(['subject', 'classSection.class', 'classSection.section', 'period', 'classroom'])
                ->where('teacher_id', $staff->staff_id)
                ->where('academic_year_id', $year->academic_year_id)
                ->get();
        } elseif ($user->user_type === 'student' || $user->hasRole('Student')) {
            $enrollment = StudentClassEnrollment::where('student_id', $user->student?->student_id)
                ->where('academic_year_id', $year->academic_year_id)
                ->first();

            if (!$enrollment) {
                return response()->json(['error' => 'No active class enrollment found for this academic year.'], 404);
            }

            $timetables = Timetable::with(['subject', 'teacher', 'period', 'classroom'])
                ->where('class_section_id', $enrollment->class_section_id)
                ->where('academic_year_id', $year->academic_year_id)
                ->get();
        } else {
            return response()->json(['error' => 'User role not authorized to view timetable.'], 403);
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $schedule = [];

        foreach ($days as $day) {
            $schedule[$day] = $timetables->where('day_of_week', $day)
                ->sortBy(fn($t) => $t->period->start_time ?? '00:00:00')
                ->map(fn($t) => [
                    'timetable_id' => $t->timetable_id,
                    'subject'      => $t->subject->name ?? 'Unknown',
                    'period'       => $t->period->name ?? 'N/A',
                    'start_time'   => $t->period->start_time ?? '',
                    'end_time'     => $t->period->end_time ?? '',
                    'class_section_id' => $t->class_section_id,
                    'classroom'    => $t->classroom->room_number ?? 'N/A',
                    'teacher'      => $t->teacher ? trim($t->teacher->first_name . ' ' . $t->teacher->last_name) : ($t->classSection ? trim($t->classSection->class->name . ' ' . $t->classSection->section->name) : 'N/A'),
                    'class_section' => $t->classSection ? trim($t->classSection->class->name . ' - ' . $t->classSection->section->name) : 'N/A',
                ])->values();
        }

        return response()->json([
            'academic_year' => $year->name,
            'schedule'      => $schedule,
        ]);
    }

    public function today(Request $request): JsonResponse
    {
        $today = strtolower(now()->format('l'));
        $user = $request->user();
        $year = AcademicYear::where('is_current', true)->first();

        if (!$year) {
            return response()->json(['error' => 'No current academic year set.'], 404);
        }

        $timetables = collect();

        if ($user->user_type === 'teacher' || $user->hasRole('Teacher')) {
            $staff = Staff::where('user_id', $user->id)->first();
            if (!$staff) return response()->json(['error' => 'Staff record not found.'], 404);

            $timetables = Timetable::with(['subject', 'classSection.class', 'classSection.section', 'period', 'classroom'])
                ->where('teacher_id', $staff->staff_id)
                ->where('academic_year_id', $year->academic_year_id)
                ->where('day_of_week', $today)
                ->get();
        } elseif ($user->user_type === 'student' || $user->hasRole('Student')) {
            $enrollment = StudentClassEnrollment::where('student_id', $user->student?->student_id)
                ->where('academic_year_id', $year->academic_year_id)
                ->first();
            if (!$enrollment) return response()->json(['error' => 'No active enrollment found.'], 404);

            $timetables = Timetable::with(['subject', 'teacher', 'period', 'classroom'])
                ->where('class_section_id', $enrollment->class_section_id)
                ->where('academic_year_id', $year->academic_year_id)
                ->where('day_of_week', $today)
                ->get();
        }

        $lessons = $timetables->sortBy(fn($t) => $t->period->start_time ?? '00:00:00')
            ->map(fn($t) => [
                'timetable_id' => $t->timetable_id,
                'subject'      => $t->subject->name ?? 'Unknown',
                'period'       => $t->period->name ?? 'N/A',
                'start_time'   => $t->period->start_time ?? '',
                'end_time'     => $t->period->end_time ?? '',
                'class_section_id' => $t->class_section_id,
                'classroom'    => $t->classroom->room_number ?? 'N/A',
                'teacher'      => $t->teacher ? trim($t->teacher->first_name . ' ' . $t->teacher->last_name) : 'N/A',
                'class_section' => $t->classSection ? trim($t->classSection->class->name . ' - ' . $t->classSection->section->name) : 'N/A',
            ])->values();

        return response()->json([
            'day'     => ucfirst($today),
            'lessons' => $lessons,
        ]);
    }
}