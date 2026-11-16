<?php

namespace App\Services;

use App\Models\ClassSection;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Period;
use App\Models\Staff;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Authoritative "teacher daily home" queries for the mobile API.
 *
 * Everything a teacher's home screen shows must come from THIS kind of query —
 * the app must never infer completion from locally fabricated rows:
 *  - the next lesson they are scheduled to teach,
 *  - which class registers are still outstanding for a given date,
 *  - which exam rosters still have marks missing (grading debt).
 *
 * Attendance business model (verified against the schema): student_attendance
 * is keyed per (student_id, date) — i.e. ONE DAILY register per class section,
 * NOT per lesson. "Pending register" therefore means: a section this teacher
 * belongs to whose active-enrolled students do not all have an attendance row
 * for the date.
 */
class TeacherMobileHomeService
{
    public function __construct(private TeacherScopeService $scope)
    {
    }

    /**
     * The teacher's next scheduled lesson today (period starting after now).
     * Returns the lesson extended with everything the app needs to deep-link
     * straight into the class register: class_section_id + period end time.
     */
    public function nextLesson(User $user, Staff $staff): ?array
    {
        $dayOfWeek = strtolower(now()->format('l'));
        $currentTime = now()->format('H:i:s');

        $lesson = Timetable::with(['subject', 'period', 'classroom', 'classSection.class', 'classSection.section'])
            ->where('teacher_id', $staff->staff_id)
            ->where('day_of_week', $dayOfWeek)
            ->whereHas('period', fn ($q) => $q->where('start_time', '>', $currentTime))
            ->orderBy(
                Period::select('start_time')
                    ->whereColumn('periods.period_id', 'timetable.period_id'),
                'asc'
            )
            ->first();

        if (!$lesson) {
            return null;
        }

        return [
            'timetable_id'     => $lesson->timetable_id,
            'class_section_id' => $lesson->class_section_id,
            'subject'          => $lesson->subject->name ?? 'Unknown',
            'period'           => $lesson->period->name ?? '',
            'start_time'       => $lesson->period->start_time ?? '',
            'end_time'         => $lesson->period->end_time ?? '',
            'room'             => $lesson->classroom?->room_number ?? 'N/A',
            'class'            => trim(($lesson->classSection?->schoolClass?->name ?? $lesson->classSection?->class?->name ?? '') . ' ' . ($lesson->classSection?->section?->name ?? '')),
        ];
    }

    /**
     * Sections this teacher is responsible for whose DAILY register for the
     * date is not yet complete (every active-enrolled student must have an
     * attendance row). A section with no active students is never pending.
     *
     * @return list<array{class_section_id:int,label:string,marked:int,enrolled:int,pending:int}>
     */
    public function pendingRegisters(User $user, string $date): array
    {
        $sections = $this->responsibleSections($user);
        if ($sections->isEmpty()) {
            return [];
        }

        $sectionIds = $sections->pluck('class_section_id');

        // enrolled: active enrollments in the section. marked: of those
        // students, how many already have ANY attendance row for the date
        // (the register is keyed per student/date, class_section_id is the
        // section recorded at marking time).
        $enrolledCounts = StudentClassEnrollment::whereIn('class_section_id', $sectionIds)
            ->where('status', 'active')
            ->selectRaw('class_section_id, COUNT(DISTINCT student_id) as c')
            ->groupBy('class_section_id')
            ->pluck('c', 'class_section_id');

        $markedIds = StudentAttendance::where('date', $date)
            ->pluck('student_id')
            ->unique()
            ->flip();

        $pending = [];
        foreach ($sections as $section) {
            $enrolled = (int) ($enrolledCounts[$section->class_section_id] ?? 0);
            if ($enrolled === 0) {
                continue; // nothing to register — never fabricate a task
            }

            $roster = StudentClassEnrollment::where('class_section_id', $section->class_section_id)
                ->where('status', 'active')
                ->pluck('student_id');
            $marked = $roster->intersect($markedIds->keys())->count();

            if ($marked >= $enrolled) {
                continue; // register complete — it must disappear from the list
            }

            $pending[] = [
                'class_section_id' => (int) $section->class_section_id,
                'label'            => trim(($section->schoolClass->name ?? '') . ' ' . ($section->section->name ?? '')),
                'marked'           => $marked,
                'enrolled'         => $enrolled,
                'pending'          => $enrolled - $marked,
            ];
        }

        usort($pending, fn ($a, $b) => strcmp($a['label'], $b['label']));

        return $pending;
    }

    /**
     * Exam schedules on or before today that this teacher must mark and still
     * have students without a recorded mark (true grading debt — the old
     * dashboard reported TOTAL marks recorded, which is not actionable).
     *
     * Roster + authorization mirror MobileExamController::studentsForExam /
     * storeMarks (active enrollments in the scheduled class's sections where
     * the teacher can manage that subject), so the count matches what the
     * mark-entry screen will actually let them fill.
     *
     * @return list<array{schedule_id:int,exam_id:int,subject_id:int,exam_name:string,subject:string,class_name:string,date:string,max_marks:float,missing:int,roster:int}>
     */
    public function pendingMarks(User $user, Staff $staff): array
    {
        $classIds = $this->scope->getClassIds($user);
        if ($classIds->isEmpty()) {
            return [];
        }

        $schedules = ExamSchedule::with(['exam', 'subject', 'class'])
            ->whereIn('class_id', $classIds)
            ->whereNotNull('exam_date')
            ->whereDate('exam_date', '<=', now()->toDateString())
            ->whereDate('exam_date', '>=', now()->subDays(60)->toDateString())
            ->orderBy('exam_date', 'desc')
            ->limit(50)
            ->get();

        $isTeacherOnly = $user->hasRole('Teacher') && !$user->hasAnyRole(['Owner', 'Super Admin', 'Admin']);

        $result = [];
        foreach ($schedules as $s) {
            $sections = ClassSection::where('class_id', $s->class_id)->get();
            $eligibleStudentIds = collect();
            foreach ($sections as $section) {
                if ($isTeacherOnly
                    && !$this->scope->canManageSubjectInSection($user, (int) $section->class_section_id, (int) $s->subject_id)) {
                    continue;
                }
                $eligibleStudentIds = $eligibleStudentIds->merge(
                    StudentClassEnrollment::where('class_section_id', $section->class_section_id)
                        ->where('status', 'active')
                        ->pluck('student_id')
                );
            }
            $eligibleStudentIds = $eligibleStudentIds->unique();
            $roster = $eligibleStudentIds->count();
            if ($roster === 0) {
                continue;
            }

            $recorded = ExamResult::where('exam_id', $s->exam_id)
                ->where('subject_id', $s->subject_id)
                ->whereIn('student_id', $eligibleStudentIds)
                ->count();

            if ($recorded >= $roster) {
                continue; // fully marked — nothing pending
            }

            $result[] = [
                'schedule_id' => (int) $s->schedule_id,
                'exam_id'     => (int) $s->exam_id,
                'subject_id'  => (int) $s->subject_id,
                'exam_name'   => $s->exam->name ?? 'Exam',
                'subject'     => $s->subject->name ?? 'Subject',
                'class_name'  => $s->class->name ?? 'Class',
                'date'        => $s->exam_date->toDateString(),
                'max_marks'   => (float) ($s->max_marks ?? 100),
                'missing'     => $roster - $recorded,
                'roster'      => $roster,
            ];
        }

        return $result;
    }

    /**
     * The class sections whose register/mark duties fall to this teacher:
     * subject-assignment ∪ class-teacher (TeacherScopeService).
     */
    private function responsibleSections(User $user): Collection
    {
        $ids = $this->scope->getClassSectionIds($user);
        if ($ids->isEmpty()) {
            return collect();
        }

        return ClassSection::with(['schoolClass', 'section'])
            ->whereIn('class_section_id', $ids)
            ->get();
    }
}
