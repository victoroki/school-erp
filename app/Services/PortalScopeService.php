<?php

namespace App\Services;

use App\Models\ClassSection;
use App\Models\ExamSchedule;
use App\Models\FeePayment;
use App\Models\Homework;
use App\Models\Parents;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use App\Models\StudentFeeAssignment;
use App\Models\StudentParentRelationship;
use App\Models\Term;
use App\Models\Timetable;
use Illuminate\Support\Collection;

/**
 * PHASE 4 — single home for parent/student data-ownership resolution.
 *
 * Every portal-facing read that takes a student_id (child overviews,
 * attendance history, fee endpoints, exam/CBC views) funnels through here so
 * "is THIS user allowed to see THAT student" has exactly one implementation.
 * The rules are the same ones the Phase 0–3 controllers already applied
 * privately (parents → StudentParentRelationship rows, students → their own
 * user-linked record, staff roles → their existing scope) — hoisted, not
 * re-invented.
 */
class PortalScopeService
{
    /**
     * Active enrollment for a student in the current academic year, with
     * section + class loaded (or null when unenrolled).
     */
    public static function enrollment(?int $studentId): ?StudentClassEnrollment
    {
        if (!$studentId) {
            return null;
        }

        return StudentClassEnrollment::where('student_id', $studentId)
            ->where('status', 'active')
            ->whereHas('academicYear', fn ($q) => $q->where('is_current', true))
            ->with(['classSection.schoolClass', 'classSection.section'])
            ->first();
    }

    /** "Form 2 North" display label for an enrollment (or null). */
    public static function className(?StudentClassEnrollment $enrollment): ?string
    {
        if (!$enrollment?->classSection) {
            return null;
        }
        $cs = $enrollment->classSection;

        return trim(($cs->schoolClass?->name ?? '') . ' ' . ($cs->section?->name ?? '')) ?: null;
    }

    /**
     * Normalized "form 2 north" match key — same collapse the homework
     * controller uses so free-text class_name labels compare equal.
     */
    public static function normalizeLabel(?string $label): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $label)));
    }

    /**
     * The student record bound to a portal user (student role) or null.
     */
    public static function selfStudent($user): ?Student
    {
        return $user->student ?? Student::where('user_id', $user->id)->first();
    }

    /**
     * Student IDs the caller may read. Mirrors the union of role rules the
     * individual controllers used:
     *  - Owner/Super Admin/Admin/Accountant → every active student;
     *  - Teacher → students in their assigned sections;
     *  - Parent → linked children;
     *  - Student → self only.
     * Multi-role users get the union (a Teacher who is also a Parent).
     */
    public function visibleStudentIds($user): array
    {
        $user->loadMissing('roles');
        $ids = collect();

        if ($user->hasAnyRole(['Owner', 'Super Admin', 'Admin', 'Accountant'])) {
            return Student::where('status', 'active')->pluck('student_id')->toArray();
        }

        if ($user->hasRole('Teacher')) {
            $sectionIds = app(TeacherScopeService::class)->getClassSectionIds($user);
            $teacherIds = StudentClassEnrollment::whereIn('class_section_id', $sectionIds->isEmpty() ? [0] : $sectionIds)
                ->where('status', 'active')
                ->pluck('student_id');
            $ids = $ids->merge($teacherIds);
        }

        if ($user->hasRole('Parent')) {
            $parent = Parents::where('user_id', $user->id)->first();
            if ($parent) {
                $ids = $ids->merge(
                    StudentParentRelationship::where('parent_id', $parent->parent_id)->pluck('student_id')
                );
            }
        }

        if ($user->hasRole('Student')) {
            $student = self::selfStudent($user);
            if ($student) {
                $ids->push($student->student_id);
            }
        }

        return $ids->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    /** Parent-owned children (id/name/class) for the dashboard + switcher. */
    public function childrenFor($user): Collection
    {
        $parent = Parents::where('user_id', $user->id)->first();
        if (!$parent) {
            return collect();
        }

        $rels = StudentParentRelationship::where('parent_id', $parent->parent_id)
            ->with('student')
            ->get();

        // Section labels in one query, keyed by student id.
        $labels = [];
        foreach ($rels as $rel) {
            $enrollment = self::enrollment($rel->student_id);
            $labels[$rel->student_id] = self::className($enrollment) ?? 'Not assigned';
        }

        return $rels
            ->filter(fn ($rel) => $rel->student !== null)
            ->map(function ($rel) use ($labels) {
                $s = $rel->student;

                return [
                    'student_id' => (int) $rel->student_id,
                    'name' => trim($s->first_name . ' ' . $s->last_name),
                    'admission_no' => $s->admission_no,
                    'class' => $labels[$rel->student_id] ?? 'Not assigned',
                    'is_primary_contact' => (bool) $rel->is_primary_contact,
                ];
            })
            ->values();
    }

    /**
     * The per-child rollup both the Parent Home and the Child overview use:
     * today's attendance status, next lesson, today's lessons, current-term
     * fee position, homework due, upcoming exams.
     *
     * Null discipline/medical counts are DELIBERATE: those screens read the
     * existing scoped endpoints — this rollup does not widen access.
     */
    public function childOverview(int $studentId): array
    {
        $student = Student::find($studentId);
        $enrollment = self::enrollment($studentId);
        $classSectionId = $enrollment?->classSection?->class_section_id ?? null;

        // Attendance: today + current-term counts.
        $term = Term::active()
            ->whereHas('academicYear', fn ($q) => $q->where('is_current', true))
            ->first();

        $todayRecord = StudentAttendance::where('student_id', $studentId)
            ->where('date', now()->toDateString())
            ->first(['date', 'status', 'remarks']);

        $termCounts = null;
        $termQuery = StudentAttendance::where('student_id', $studentId);
        if ($term) {
            $termQuery
                ->where('date', '>=', $term->start_date->toDateString())
                ->where('date', '<=', $term->end_date->toDateString());
        } else {
            $termQuery->whereRaw('1 = 0'); // no current term → no summary, not a fake zero
        }
        $rows = $termQuery->get(['status']);
        if ($rows->isNotEmpty()) {
            $c = $rows->countBy('status');
            $termCounts = [
                'term' => $term->name,
                'present' => (int) ($c['present'] ?? 0),
                'absent' => (int) ($c['absent'] ?? 0),
                'late' => (int) ($c['late'] ?? 0),
                'half_day' => (int) ($c['half_day'] ?? 0),
                'excused' => (int) ($c['excused'] ?? 0),
                'days_counted' => $rows->count(),
            ];
        }

        // Timetable (today + next lesson) from the child's section.
        $dayOfWeek = strtolower(now()->format('l'));
        $lessons = collect();
        $nextLesson = null;
        if ($classSectionId) {
            $lessons = Timetable::with(['subject', 'period', 'classroom'])
                ->where('class_section_id', $classSectionId)
                ->where('day_of_week', $dayOfWeek)
                ->get()
                ->sortBy(fn ($t) => $t->period->start_time ?? '00:00:00');

            $nowTime = now()->format('H:i:s');
            $next = $lessons->first(function ($t) use ($nowTime) {
                return $t->period && (string) $t->period->start_time > $nowTime;
            });
            if ($next) {
                $nextLesson = [
                    'subject' => $next->subject?->name ?? 'Unknown',
                    'start_time' => (string) $next->period?->start_time,
                    'end_time' => (string) $next->period?->end_time,
                    'room' => $next->classroom?->room_number,
                    'period' => $next->period?->name,
                ];
            }
        }

        // Fee position — same grouping semantics as MobileFeeController::summary
        // (sum over ACTIVE assignments; never a second calculation path).
        $assignments = StudentFeeAssignment::where('student_id', $studentId)
            ->where('status', 'active')
            ->get();
        $totalAssigned = (float) $assignments->sum('final_amount');
        $totalPaid = (float) $assignments->sum('paid_amount');
        $balance = round($totalAssigned - $totalPaid, 2);
        $feeStatus = $balance <= 0 ? 'Paid' : ($totalPaid > 0 ? 'Partial' : 'Unpaid');

        $currentTermAssigned = null;
        $currentTermPaid = null;
        if ($term) {
            $termRows = $assignments->filter(function ($a) use ($term) {
                if ($a->term_id !== null) {
                    return (int) $a->term_id === (int) $term->getKey();
                }

                return $a->term !== null && $a->term !== '' && trim($a->term) === trim($term->name);
            });
            $currentTermAssigned = (float) $termRows->sum('final_amount');
            $currentTermPaid = (float) $termRows->sum('paid_amount');
        }

        $lastPayment = FeePayment::whereHas('studentFeeAssignment', fn ($q) => $q->where('student_id', $studentId))
            ->orderByDesc('payment_date')
            ->orderByDesc('payment_id')
            ->first();

        // Homework for the child's class (normalized label match, school-wide
        // blanks included) — due today or later, mirroring MobileHomeworkController.
        $label = $enrollment ? self::normalizeLabel(self::className($enrollment) ?? '') : '';
        $homework = collect();
        if ($label !== '') {
            $homework = Homework::where('status', 'active')
                ->where('due_date', '>=', now()->toDateString())
                ->orderBy('due_date')
                ->limit(60)
                ->get()
                ->filter(function ($h) use ($label) {
                    if (!$h->class_name || trim($h->class_name) === '') {
                        return true;
                    }

                    return self::normalizeLabel($h->class_name) === $label;
                })
                ->sortBy('due_date')
                ->take(5)
                ->values();
        }

        // PHASE 6 — the child's own submission status per homework row
        // (one query for the whole block; never fabricated).
        $submissionStatus = self::submissionStatusMap($studentId, $homework);

        // Upcoming exams for the child's class.
        $upcomingExams = [];
        if ($enrollment?->classSection) {
            $classId = $enrollment->classSection->schoolClass?->class_id ?? $enrollment->classSection->class_id ?? null;
            if ($classId) {
                $upcomingExams = ExamSchedule::with(['exam', 'subject'])
                    ->where('class_id', $classId)
                    ->where('exam_date', '>=', now()->toDateString())
                    ->orderBy('exam_date')
                    ->limit(3)
                    ->get()
                    ->map(fn ($s) => [
                        'exam_name' => $s->exam?->name ?? 'Exam',
                        'subject' => $s->subject?->name,
                        'date' => $s->exam_date?->toDateString(),
                    ])
                    ->all();
            }
        }

        return [
            'student_id' => $studentId,
            'name' => $student ? trim($student->first_name . ' ' . $student->last_name) : null,
            'class' => $enrollment ? self::className($enrollment) : null,
            'attendance' => [
                'today' => $todayRecord ? [
                    'date' => $todayRecord->date->toDateString(),
                    'status' => $todayRecord->status,
                    'remark' => $todayRecord->remarks,
                ] : null,
                'term' => $termCounts,
            ],
            'timetable' => [
                'day' => ucfirst($dayOfWeek),
                'next_lesson' => $nextLesson,
                'lessons' => $lessons->map(fn ($t) => [
                    'subject' => $t->subject?->name ?? 'Unknown',
                    'start_time' => (string) ($t->period?->start_time ?? ''),
                    'end_time' => (string) ($t->period?->end_time ?? ''),
                    'room' => $t->classroom?->room_number,
                ])->values()->all(),
            ],
            'fees' => [
                'total_assigned' => round($totalAssigned, 2),
                'total_paid' => round($totalPaid, 2),
                'balance' => $balance,
                'status' => $feeStatus,
                'current_term' => $term ? [
                    'name' => $term->name,
                    'assigned' => $currentTermAssigned,
                    'paid' => $currentTermPaid,
                ] : null,
                'last_payment' => $lastPayment ? [
                    'amount' => (float) $lastPayment->amount,
                    'date' => $lastPayment->payment_date?->toDateString(),
                    'receipt_no' => $lastPayment->receipt_number,
                ] : null,
            ],
            'homework_due' => $homework->map(fn ($h) => [
                'id' => $h->id,
                'title' => $h->title,
                'subject' => $h->subject,
                'due_date' => $h->due_date?->toDateString(),
                'submission_status' => $submissionStatus[$h->id] ?? null,
            ])->all(),
            'upcoming_exams' => $upcomingExams,
        ];
    }

    /**
     * PHASE 6 — map of "which status does THIS student have for THAT
     * homework" for a whole rollup block. One grouped query, keyed by
     * homework id; missing rows mean "not submitted" (null, never contrived).
     */
    private static function submissionStatusMap(int $studentId, Collection $homeworks): array
    {
        if ($homeworks->isEmpty()) {
            return [];
        }

        return \App\Models\HomeworkSubmission::where('student_id', $studentId)
            ->whereIn('homework_id', $homeworks->pluck('id'))
            ->get(['homework_id', 'status'])
            ->pluck('status', 'homework_id')
            ->all();
    }
}
