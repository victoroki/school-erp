<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\CbcAssessment;
use App\Models\ClassSection;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentParentRelationship;
use App\Services\TeacherScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MobileExamController extends Controller
{
    /**
     * GET /api/mobile/exams
     *
     * Exam schedules, role-scoped:
     *  - Student: schedules for their enrolled classes.
     *  - Parent: schedules for their linked children's classes.
     *  - Teacher: schedules for the classes they teach.
     *  - Admin / Super Admin / Accountant: all schedules.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles');

        $query = ExamSchedule::with(['exam', 'subject', 'class']);

        if ($user->hasRole('Student')) {
            $classIds = $this->studentClassIds($user);
            $query->whereIn('class_id', $classIds->isEmpty() ? [0] : $classIds);
        } elseif ($user->hasRole('Parent')) {
            $classIds = $this->parentStudentClassIds($user);
            $query->whereIn('class_id', $classIds->isEmpty() ? [0] : $classIds);
        } elseif ($user->hasRole('Teacher')) {
            $classIds = app(TeacherScopeService::class)->getClassIds($user);
            $query->whereIn('class_id', $classIds->isEmpty() ? [0] : $classIds);
        }
        // Admin / Super Admin / Accountant / other staff: no filter (all schedules).

        $schedules = $query->orderBy('exam_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->limit(100)
            ->get();

        // Teachers may only enter marks for subjects they can actually manage
        // in the scheduled class (assigned subject or class teacher). Filter
        // the list so the mobile UI never surfaces exams they can't access.
        if ($user->hasRole('Teacher') && ! $user->hasAnyRole(['Super Admin', 'Admin'])) {
            $scope = app(TeacherScopeService::class);
            $schedules = $schedules->filter(function ($s) use ($scope, $user) {
                return $this->teacherCanManageSchedule($scope, $user, $s);
            })->values();
        }

        return response()->json([
            'data' => $schedules->map(function ($s) {
                $status = 'scheduled';
                if ($s->exam_date && $s->exam_date->isToday()) {
                    $status = 'ongoing';
                } elseif ($s->exam_date && $s->exam_date->isPast()) {
                    $status = 'completed';
                }

                return [
                    'id'              => $s->schedule_id,
                    'exam_id'         => $s->exam_id,
                    'subject_id'      => $s->subject_id,
                    'class_id'        => $s->class_id,
                    'class_section_id'=> $s->class_id,
                    'exam_name'       => $s->exam ? $s->exam->name : 'Unknown Exam',
                    'subject'         => $s->subject ? $s->subject->name : 'Unknown Subject',
                    'class_section'   => $s->class ? $s->class->name : 'All Classes',
                    'date'            => $s->exam_date ? $s->exam_date->format('Y-m-d') : 'TBD',
                    'start_time'      => $s->start_time ? $s->start_time->format('H:i') : '08:00',
                    'max_marks'       => (float) ($s->max_marks ?? 100),
                    'status'          => $status,
                ];
            }),
        ]);
    }

    /**
     * GET /api/mobile/exams/{examId}/students
     *
     * The student roster for an exam's class(es), used for mark entry.
     * Teachers are scoped to their assigned class sections; admins get all
     * students enrolled in the exam's class.
     */
    public function studentsForExam(Request $request, $examId): JsonResponse
    {
        $schedule = ExamSchedule::findOrFail($examId);

        $user = $request->user();

        $classSectionIds = ClassSection::where('class_id', $schedule->class_id)
            ->pluck('class_section_id');

        if ($classSectionIds->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $enrollments = StudentClassEnrollment::whereIn('class_section_id', $classSectionIds)
            ->where('status', 'active')
            ->with(['student', 'classSection.schoolClass', 'classSection.section'])
            ->get();

        // Teacher scoping: only show students in the teacher's own sections
        // AND only for sections where they can actually manage this subject
        // (assigned subject or class teacher).
        if ($user->hasRole('Teacher') && ! $user->hasAnyRole(['Super Admin', 'Admin'])) {
            $scope = app(TeacherScopeService::class);
            $subjectId = (int) $schedule->subject_id;
            $enrollments = $enrollments->filter(function ($e) use ($scope, $user, $subjectId) {
                return $scope->canManageSubjectInSection($user, (int) $e->class_section_id, $subjectId);
            })->values();

            // A subject the teacher cannot manage anywhere → deny the roster.
            if ($enrollments->isEmpty() && ! $this->teacherCanManageSchedule($scope, $user, $schedule)) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        }

        return response()->json([
            'data' => $enrollments->map(function ($e) {
                $student = $e->student;
                return [
                    'student_id'    => $student->student_id,
                    'first_name'    => $student->first_name,
                    'last_name'     => $student->last_name,
                    'admission_no'  => $student->admission_no,
                    'class_section' => trim(($e->classSection?->schoolClass?->name ?? '') . ' ' . ($e->classSection?->section?->name ?? '')),
                ];
            })->values(),
        ]);
    }

    /**
     * GET /api/mobile/exams/my-marks
     *
     * Individual marks for the authenticated student (ownership-scoped).
     */
    public function myMarks(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasRole('Student')) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $student = Student::where('user_id', $user->id)->first();
        if (! $student) {
            return response()->json(['error' => 'Student record not found.'], 404);
        }

        $results = ExamResult::with(['exam', 'subject', 'grade'])
            ->where('student_id', $student->student_id)
            ->orderBy('exam_id', 'desc')
            ->get()
            ->map(fn ($r) => [
                'exam'      => $r->exam->name ?? 'Unknown Exam',
                'subject'   => $r->subject->name ?? 'Unknown Subject',
                'marks'     => (float) $r->marks_obtained,
                'grade'     => $r->grade->name ?? ($r->grade->grade_name ?? null),
                'remarks'   => $r->remarks,
                'date'      => $r->exam?->created_at?->toDateString(),
            ]);

        return response()->json(['results' => $results]);
    }

    /**
     * POST /api/mobile/exams/marks
     *
     * Store marks for an exam + subject. Body:
     *  { exam_id, subject_id, results: [{ student_id, marks_obtained, grade?, remarks? }] }
     *
     * Requires the exams.marks.enter-own permission. Teachers may only enter
     * marks for subjects/classes they teach; admins may enter for any.
     */
    public function storeMarks(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasPermission('exams.marks.enter-own')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'exam_id'                   => 'required|integer|exists:exams,exam_id',
            'subject_id'                => 'required|integer|exists:subjects,subject_id',
            'results'                   => 'required|array|min:1',
            'results.*.student_id'      => 'required|integer|exists:students,student_id',
            'results.*.marks_obtained'  => 'required|numeric|min:0',
            'results.*.remarks'         => 'nullable|string|max:65535',
        ]);

        $isTeacher = $user->hasRole('Teacher') && ! $user->hasAnyRole(['Super Admin', 'Admin']);
        $scope = app(TeacherScopeService::class);
        $subjectId = (int) $request->subject_id;

        // Resolve each student's active class section up front so the
        // authorization check runs against real data once per student.
        $enrollments = StudentClassEnrollment::with('classSection.schoolClass', 'classSection.section')
            ->where('status', 'active')
            ->whereIn('student_id', collect($request->results)->pluck('student_id'))
            ->get()
            ->keyBy('student_id');

        if ($isTeacher) {
            // A teacher may only enter marks for this subject in class sections
            // where (a) they are assigned to teach that subject, or (b) they are
            // the class teacher. Any unauthorized student → reject the request.
            foreach ($request->results as $result) {
                $enrollment = $enrollments->get((int) $result['student_id']);
                $sectionId = $enrollment ? (int) $enrollment->class_section_id : null;

                if ($sectionId === null) {
                    continue;
                }

                if (! $scope->canManageSubjectInSection($user, $sectionId, $subjectId)) {
                    return response()->json([
                        'message' => sprintf(
                            'Forbidden: you are not assigned to teach this subject in %s.',
                            $this->sectionLabel($enrollment)
                        ),
                    ], 403);
                }
            }
        }

        DB::transaction(function () use ($request, $user, $enrollments, $subjectId) {
            foreach ($request->results as $result) {
                $enrollment = $enrollments->get((int) $result['student_id']);

                ExamResult::updateOrCreate(
                    [
                        'exam_id'          => (int) $request->exam_id,
                        'student_id'       => (int) $result['student_id'],
                        'subject_id'       => $subjectId,
                    ],
                    [
                        'class_section_id' => $enrollment?->class_section_id,
                        'marks_obtained'   => (float) $result['marks_obtained'],
                        'remarks'          => $result['remarks'] ?? null,
                        'created_by'       => $user->staff?->staff_id,
                    ]
                );
            }
        });

        return response()->json(['message' => 'Marks stored successfully']);
    }

    /**
     * GET /api/mobile/exams/cbc/{studentId}
     *
     * CBC competency assessments for a student. Ownership-scoped:
     *  - Student: own record only.
     *  - Parent: linked children only.
     *  - Teacher / Admin: any student.
     */
    public function studentCbc(Request $request, $studentId): JsonResponse
    {
        $user = $request->user();

        if (! $this->canViewStudent($user, (int) $studentId)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $assessments = CbcAssessment::with(['learningArea', 'strand'])
            ->where('student_id', $studentId)
            ->orderByDesc('assessment_date')
            ->get();

        return response()->json([
            'data' => $assessments->map(fn ($a) => [
                'id'             => $a->id,
                'learning_area'  => $a->learningArea->name ?? 'Unknown',
                'strand'         => $a->strand->name ?? 'Unknown',
                'rating'         => $a->rating,
                'rating_code'    => CbcAssessment::RATINGS[$a->rating]['code'] ?? null,
                'remarks'        => $a->remarks,
                'date'           => $a->assessment_date?->toDateString(),
            ]),
        ]);
    }

    /**
     * POST /api/mobile/exams/cbc
     *
     * Record a CBC assessment. Body:
     *  { student_id, learning_area_id, strand_id, rating (1-4), remarks? }
     *
     * Requires a marks/CBC permission. Teachers/admins only.
     */
    public function storeCbc(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->hasPermission('exams.marks.enter-own')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $request->validate([
            'student_id'       => 'required|integer|exists:students,student_id',
            'learning_area_id' => 'required|integer|exists:cbc_learning_areas,id',
            'strand_id'        => 'required|integer|exists:cbc_strands,id',
            'rating'           => 'required|integer|between:1,4',
            'remarks'          => 'nullable|string|max:65535',
        ]);

        $assessment = CbcAssessment::updateOrCreate(
            [
                'student_id'       => (int) $request->student_id,
                'learning_area_id' => (int) $request->learning_area_id,
                'strand_id'        => (int) $request->strand_id,
            ],
            [
                'rating'          => (int) $request->rating,
                'remarks'         => $request->remarks,
                'assessed_by'     => $user->staff?->staff_id,
                'assessment_date' => now(),
            ]
        );

        return response()->json([
            'message' => 'CBC assessment recorded',
            'data'    => $assessment,
        ]);
    }

    /**
     * GET /api/mobile/exams/cbc/structure
     *
     * CBC competency framework: learning areas with their strands (and
     * sub-strands). Used by the mobile CBC assessment screen to let teachers
     * record strand-level ratings against real ids.
     */
    public function cbcStructure(Request $request): JsonResponse
    {
        $areas = \App\Models\CbcLearningArea::with('strands.subStrands')
            ->orderBy('name')
            ->get()
            ->map(fn ($area) => [
                'id'    => $area->id,
                'name'  => $area->name,
                'code'  => $area->code,
                'strands' => $area->strands->map(fn ($s) => [
                    'id'             => $s->id,
                    'name'           => $s->name,
                    'learning_area'  => $area->name,
                ]),
            ]);

        return response()->json(['learning_areas' => $areas]);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /**
     * True when a teacher may enter marks for an exam schedule: they teach the
     * scheduled subject in at least one class section of the class, OR they are
     * the class teacher of such a section.
     */
    private function teacherCanManageSchedule($scope, $user, $schedule): bool
    {
        $subjectId = (int) $schedule->subject_id;
        $sectionIds = ClassSection::where('class_id', $schedule->class_id)
            ->pluck('class_section_id');

        if ($sectionIds->isEmpty()) {
            return false;
        }

        foreach ($sectionIds as $sectionId) {
            if ($scope->canManageSubjectInSection($user, (int) $sectionId, $subjectId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Human-readable label for a class section (used in 403 messages).
     */
    private function sectionLabel($enrollment): string
    {
        if (! $enrollment?->classSection) {
            return 'this class';
        }
        $cs = $enrollment->classSection;
        return trim(($cs->schoolClass->name ?? '') . ' ' . ($cs->section->name ?? ''));
    }

    private function studentClassIds($user): \Illuminate\Support\Collection
    {
        $student = Student::where('user_id', $user->id)->with('studentClassEnrollments')->first();
        if (! $student) {
            return collect();
        }

        $sectionIds = $student->studentClassEnrollments
            ->where('status', 'active')
            ->pluck('class_section_id');

        return ClassSection::whereIn('class_section_id', $sectionIds)
            ->pluck('class_id')
            ->unique()
            ->values();
    }

    private function parentStudentClassIds($user): \Illuminate\Support\Collection
    {
        $parentRecord = \App\Models\Parents::where('user_id', $user->id)->first();
        if (! $parentRecord) {
            return collect();
        }

        $studentIds = StudentParentRelationship::where('parent_id', $parentRecord->parent_id)
            ->pluck('student_id');

        $sectionIds = StudentClassEnrollment::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->pluck('class_section_id');

        return ClassSection::whereIn('class_section_id', $sectionIds)
            ->pluck('class_id')
            ->unique()
            ->values();
    }

    private function canViewStudent($user, int $studentId): bool
    {
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Teacher', 'Accountant'])) {
            return true;
        }

        if ($user->hasRole('Student')) {
            $student = Student::where('user_id', $user->id)->first();
            return $student && (int) $student->student_id === $studentId;
        }

        if ($user->hasRole('Parent')) {
            $parentRecord = \App\Models\Parents::where('user_id', $user->id)->first();
            if (! $parentRecord) {
                return false;
            }
            return StudentParentRelationship::where('parent_id', $parentRecord->parent_id)
                ->where('student_id', $studentId)
                ->exists();
        }

        return false;
    }
}
