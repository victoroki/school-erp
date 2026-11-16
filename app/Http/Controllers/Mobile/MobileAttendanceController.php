<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use App\Models\StudentParentRelationship;
use App\Services\TeacherScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MobileAttendanceController extends Controller
{
    /**
     * GET /api/mobile/attendance
     *
     * Returns the attendance register for a given date and (optionally)
     * class section. Role-scoped:
     *  - Teacher: their assigned class sections.
     *  - Admin / Super Admin: all sections.
     *  - Parent: their children's records.
     *  - Student: own record.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles');
        $date = $request->query('date', now()->toDateString());

        $query = StudentAttendance::where('date', $date)
            ->with('student');

        if ($user->hasRole('Teacher')) {
            // Subject-teaching AND class-teacher sections (matches GET /students).
            $classSectionIds = app(TeacherScopeService::class)->getClassSectionIds($user);
            $query->whereIn('class_section_id', $classSectionIds);
        } elseif ($user->hasRole('Parent')) {
            $parentRecord = \App\Models\Parents::where('user_id', $user->id)->first();
            if ($parentRecord) {
                $childIds = StudentParentRelationship::where('parent_id', $parentRecord->parent_id)
                    ->pluck('student_id');
                $query->whereIn('student_id', $childIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->hasRole('Student')) {
            $student = Student::where('user_id', $user->id)->first();
            $query->where('student_id', $student?->student_id ?? 0);
        }
        // Owner / Super Admin / Admin: no additional filter.

        $records = $query->get([
            'attendance_id',
            'student_id',
            'class_section_id',
            'date',
            'status',
            'remarks',
        ]);

        return response()->json([
            'date'     => $date,
            'records'  => $records->map(fn ($r) => [
                'attendance_id'    => $r->attendance_id,
                'student_id'       => $r->student_id,
                'class_section_id' => $r->class_section_id,
                'student_name'     => trim($r->student?->first_name . ' ' . $r->student?->last_name),
                'date'             => $r->date?->format('Y-m-d'),
                'status'           => $r->status,
                'remark'           => $r->remarks,
            ]),
        ]);
    }

    /**
     * GET /api/mobile/attendance/mine
     *
     * Returns the attendance records for the current student user.
     */
    public function mine(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (! $student) {
            return response()->json(['records' => []]);
        }

        $records = StudentAttendance::where('student_id', $student->student_id)
            ->orderByDesc('date')
            ->limit(90)
            ->get(['attendance_id', 'date', 'status', 'remarks']);

        return response()->json([
            'records' => $records,
        ]);
    }

    /**
     * POST /api/mobile/attendance
     *
     * Store attendance records for a given date. Accepts:
     *  { records: [{ student_id, status }], date?: string }
     *
     * Only teachers / admins with academics.attendance.manage may call this.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasPermission('academics.attendance.manage')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'records'              => 'required|array|min:1',
            'records.*.student_id' => 'required|integer|exists:students,student_id',
            // Mirrors the student_attendance.status enum in the schema.
            'records.*.status'     => 'required|in:present,absent,late,half_day,excused',
            'records.*.remarks'    => 'nullable|string|max:2000',
            'date'                 => 'nullable|date',
        ]);

        $date = $request->input('date', now()->toDateString());
        $staff = $user->staff;

        // Teachers may only mark attendance in class sections they are assigned
        // to (subject teaching + class-teacher duties), matching the students
        // roster they see on the device.
        $teacherSections = null;
        if ($user->hasRole('Teacher') && ! $user->hasAnyRole(['Owner', 'Super Admin', 'Admin'])) {
            $teacherSections = app(TeacherScopeService::class)->getClassSectionIds($user);
        }

        $enrollments = StudentClassEnrollment::whereIn('student_id', collect($request->records)->pluck('student_id'))
            ->where('status', 'active')
            ->get()
            ->keyBy('student_id');

        foreach ($request->records as $record) {
            $enrollment = $enrollments->get($record['student_id']);
            if ($teacherSections && (! $enrollment || ! $teacherSections->contains($enrollment->class_section_id))) {
                return response()->json([
                    'message' => 'You can only mark attendance for your assigned classes.',
                ], 403);
            }
        }

        DB::transaction(function () use ($request, $date, $staff, $enrollments) {
            foreach ($request->records as $record) {
                $enrollment = $enrollments->get($record['student_id']);

                StudentAttendance::updateOrCreate(
                    [
                        'student_id'       => $record['student_id'],
                        'date'             => $date,
                    ],
                    [
                        'class_section_id' => $enrollment?->class_section_id,
                        'status'           => $record['status'],
                        // Remarks are optional per student — only forced when a
                        // non-present status needs context.
                        'remarks'          => isset($record['remarks']) && $record['remarks'] !== '' ? $record['remarks'] : null,
                        'marked_by'        => $staff?->staff_id,
                    ]
                );
            }
        });

        return response()->json(['ok' => true, 'server_received_at' => now()->toIso8601String()]);
    }
}
