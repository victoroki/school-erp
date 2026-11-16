<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\Staff;
use App\Models\AcademicYear;
use App\Models\StudentClassEnrollment;
use App\Services\PortalScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileTimetableController extends Controller
{
    public function __construct(private readonly PortalScopeService $scope)
    {
    }

    /**
     * Resolve the class section whose timetable the caller may see.
     *
     *  - Student: their own active enrollment (never a supplied id).
     *  - Parent: the enrollment of a student_id ONLY if that child is theirs
     *    (PortalScopeService membership; 403 otherwise) — STEP 11.
     *  - Teacher: own lessons are selected by teacher_id instead (see below).
     */
    private function sectionForPortalUser(Request $request, ?AcademicYear $year): ?int
    {
        $user = $request->user();
        if ($user->hasRole('Parent') && ! $user->hasRole('Student')) {
            $studentId = (int) $request->query('student_id', 0);
            if ($studentId <= 0 || ! in_array($studentId, $this->scope->visibleStudentIds($user), true)) {
                return null; // caller guards with an explicit 403
            }
            $enrollment = StudentClassEnrollment::where('student_id', $studentId)
                ->where('academic_year_id', $year->academic_year_id)
                ->first();

            return $enrollment?->class_section_id;
        }

        if ($user->user_type === 'student' || $user->hasRole('Student')) {
            $enrollment = StudentClassEnrollment::where('student_id', $user->student?->student_id)
                ->where('academic_year_id', $year->academic_year_id)
                ->first();

            return $enrollment?->class_section_id;
        }

        return null;
    }

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
        } elseif ($user->hasRole('Parent')) {
            $sectionId = $this->sectionForPortalUser($request, $year);
            if ($sectionId === null) {
                return response()->json(['error' => 'Provide a student_id linked to your account.'], 403);
            }
            $timetables = Timetable::with(['subject', 'teacher', 'period', 'classroom'])
                ->where('class_section_id', $sectionId)
                ->where('academic_year_id', $year->academic_year_id)
                ->get();
        } elseif ($user->user_type === 'student' || $user->hasRole('Student')) {
            $sectionId = $this->sectionForPortalUser($request, $year);
            if (!$sectionId) {
                return response()->json(['error' => 'No active class enrollment found for this academic year.'], 404);
            }

            $timetables = Timetable::with(['subject', 'teacher', 'period', 'classroom'])
                ->where('class_section_id', $sectionId)
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
        } elseif ($user->hasRole('Parent')) {
            // Parent: the selected child's section, ownership-checked server-side.
            $sectionId = $this->sectionForPortalUser($request, $year);
            if ($sectionId === null) {
                return response()->json(['error' => 'Provide a student_id linked to your account.'], 403);
            }
            $timetables = Timetable::with(['subject', 'teacher', 'period', 'classroom'])
                ->where('class_section_id', $sectionId)
                ->where('academic_year_id', $year->academic_year_id)
                ->where('day_of_week', $today)
                ->get();
        } elseif ($user->user_type === 'student' || $user->hasRole('Student')) {
            $sectionId = $this->sectionForPortalUser($request, $year);
            if (!$sectionId) return response()->json(['error' => 'No active enrollment found.'], 404);

            $timetables = Timetable::with(['subject', 'teacher', 'period', 'classroom'])
                ->where('class_section_id', $sectionId)
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