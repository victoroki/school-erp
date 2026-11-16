<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\Timetable;
use App\Models\TimetableOverride;
use App\Models\TermWeek;

/**
 * Shared collision rules used by both the timetable Request validators
 * and the auto-generation service. Extended with override-aware checks
 * and teacher workload cap warnings.
 */
class TimetableConflictService
{
    // ─── Original static methods (base schedule only) ────────────────────

    /**
     * @param array<int, array<string, mixed>> $timetables
     */
    public static function classSlotTaken(array $timetables, $classSectionId, $academicYearId, $day, $periodId, $ignoreId = null): bool
    {
        foreach ($timetables as $t) {
            if ($ignoreId !== null && (int) $t['timetable_id'] === (int) $ignoreId) {
                continue;
            }
            if ((int) $t['class_section_id'] === (int) $classSectionId
                && (int) $t['academic_year_id'] === (int) $academicYearId
                && $t['day_of_week'] === $day
                && (int) $t['period_id'] === (int) $periodId) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<int, array<string, mixed>> $timetables
     */
    public static function teacherConflict(array $timetables, $teacherId, $academicYearId, $day, $periodId, $ignoreId = null): bool
    {
        foreach ($timetables as $t) {
            if ($ignoreId !== null && (int) $t['timetable_id'] === (int) $ignoreId) {
                continue;
            }
            if ((int) $t['teacher_id'] === (int) $teacherId
                && (int) $t['academic_year_id'] === (int) $academicYearId
                && $t['day_of_week'] === $day
                && (int) $t['period_id'] === (int) $periodId) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<int, array<string, mixed>> $timetables
     */
    public static function classroomConflict(array $timetables, $classroomId, $academicYearId, $day, $periodId, $ignoreId = null): bool
    {
        foreach ($timetables as $t) {
            if ($ignoreId !== null && (int) $t['timetable_id'] === (int) $ignoreId) {
                continue;
            }
            if ((int) $t['classroom_id'] === (int) $classroomId
                && (int) $t['academic_year_id'] === (int) $academicYearId
                && $t['day_of_week'] === $day
                && (int) $t['period_id'] === (int) $periodId) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<int, array<string, mixed>> $teacherSubjects
     */
    public static function teacherSubjectAssigned(array $teacherSubjects, $teacherId, $subjectId, $classSectionId, $academicYearId): bool
    {
        foreach ($teacherSubjects as $ts) {
            if ((int) $ts['staff_id'] === (int) $teacherId
                && (int) $ts['subject_id'] === (int) $subjectId
                && (int) $ts['class_section_id'] === (int) $classSectionId
                && (int) $ts['academic_year_id'] === (int) $academicYearId) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<int, array<string, mixed>> $classSubjects
     */
    public static function classSubjectConfigured(array $classSubjects, $classId, $subjectId, $academicYearId): bool
    {
        foreach ($classSubjects as $cs) {
            if ((int) $cs['class_id'] === (int) $classId
                && (int) $cs['subject_id'] === (int) $subjectId
                && (int) $cs['academic_year_id'] === (int) $academicYearId) {
                return true;
            }
        }
        return false;
    }

    // ─── Override-aware conflict resolution ──────────────────────────────

    /**
     * Resolve the effective schedule for a specific week.
     * Base timetable rows are modified by overrides for that week:
     * - cancel: lesson is removed
     * - substitute: teacher/classroom swapped
     * - reschedule: day/period/teacher/classroom swapped
     *
     * @return array<int, array<string, mixed>> Effective timetable rows for the week
     */
    public static function resolveEffectiveSchedule(int $academicYearId, int $termWeekId, ?int $ignoreTimetableId = null): array
    {
        $baseRows = Timetable::where('academic_year_id', $academicYearId)
            ->get()
            ->map(fn($t) => $t->toArray())
            ->toArray();

        $overrides = TimetableOverride::where('term_week_id', $termWeekId)->get();

        $cancelledIds = [];
        $substituted = [];
        $rescheduled = [];

        foreach ($overrides as $ov) {
            $tid = (int) $ov->timetable_id;
            if ($ignoreTimetableId !== null && $tid === (int) $ignoreTimetableId) {
                continue;
            }
            switch ($ov->override_type) {
                case 'cancel':
                    $cancelledIds[] = $tid;
                    break;
                case 'substitute':
                    $substituted[$tid] = $ov;
                    break;
                case 'reschedule':
                    $rescheduled[$tid] = $ov;
                    break;
            }
        }

        $effective = [];
        foreach ($baseRows as $row) {
            $tid = (int) $row['timetable_id'];

            if (in_array($tid, $cancelledIds)) {
                continue;
            }

            if (isset($substituted[$tid])) {
                $ov = $substituted[$tid];
                if ($ov->substitute_teacher_id) {
                    $row['teacher_id'] = (int) $ov->substitute_teacher_id;
                }
                if ($ov->substitute_classroom_id) {
                    $row['classroom_id'] = (int) $ov->substitute_classroom_id;
                }
                $row['_override_type'] = 'substitute';
                $row['_override_id'] = $ov->id;
            }

            if (isset($rescheduled[$tid])) {
                $ov = $rescheduled[$tid];
                if ($ov->new_day_of_week) {
                    $row['day_of_week'] = $ov->new_day_of_week;
                }
                if ($ov->new_period_id) {
                    $row['period_id'] = (int) $ov->new_period_id;
                }
                if ($ov->new_teacher_id) {
                    $row['teacher_id'] = (int) $ov->new_teacher_id;
                }
                if ($ov->new_classroom_id) {
                    $row['classroom_id'] = (int) $ov->new_classroom_id;
                }
                $row['_override_type'] = 'reschedule';
                $row['_override_id'] = $ov->id;
            }

            $effective[] = $row;
        }

        return $effective;
    }

    /**
     * Check teacher conflict on the effective (override-resolved) schedule for a week.
     * Returns conflict details or null if no conflict.
     *
     * @return array{conflict: bool, message: string, details: array}|null
     */
    public static function effectiveTeacherConflict(
        int $academicYearId,
        int $termWeekId,
        int $teacherId,
        string $day,
        int $periodId,
        ?int $ignoreTimetableId = null
    ): ?array {
        $effective = static::resolveEffectiveSchedule($academicYearId, $termWeekId, $ignoreTimetableId);

        foreach ($effective as $row) {
            if ((int) $row['teacher_id'] === (int) $teacherId
                && $row['day_of_week'] === $day
                && (int) $row['period_id'] === (int) $periodId) {
                $existingClass = $row['class_section_id'];
                return [
                    'conflict' => true,
                    'message' => "Teacher is already scheduled for class section {$existingClass} on {$day} period {$periodId}" . ($row['_override_type'] ?? '') ? " ({$row['_override_type']} override)" : '',
                    'details' => [
                        'existing_class_section_id' => (int) $row['class_section_id'],
                        'existing_subject_id' => (int) $row['subject_id'],
                        'existing_timetable_id' => (int) $row['timetable_id'],
                        'override_type' => $row['_override_type'] ?? null,
                    ],
                ];
            }
        }
        return null;
    }

    /**
     * Check class slot conflict on the effective schedule for a week.
     */
    public static function effectiveClassSlotTaken(
        int $academicYearId,
        int $termWeekId,
        int $classSectionId,
        string $day,
        int $periodId,
        ?int $ignoreTimetableId = null
    ): ?array {
        $effective = static::resolveEffectiveSchedule($academicYearId, $termWeekId, $ignoreTimetableId);

        foreach ($effective as $row) {
            if ((int) $row['class_section_id'] === (int) $classSectionId
                && $row['day_of_week'] === $day
                && (int) $row['period_id'] === (int) $periodId) {
                return [
                    'conflict' => true,
                    'message' => "Class section already has a lesson on {$day} period {$periodId}",
                    'details' => [
                        'existing_subject_id' => (int) $row['subject_id'],
                        'existing_teacher_id' => (int) $row['teacher_id'],
                        'existing_timetable_id' => (int) $row['timetable_id'],
                        'override_type' => $row['_override_type'] ?? null,
                    ],
                ];
            }
        }
        return null;
    }

    /**
     * Check classroom conflict on the effective schedule for a week.
     */
    public static function effectiveClassroomConflict(
        int $academicYearId,
        int $termWeekId,
        int $classroomId,
        string $day,
        int $periodId,
        ?int $ignoreTimetableId = null
    ): ?array {
        $effective = static::resolveEffectiveSchedule($academicYearId, $termWeekId, $ignoreTimetableId);

        foreach ($effective as $row) {
            if ((int) $row['classroom_id'] === (int) $classroomId
                && $row['day_of_week'] === $day
                && (int) $row['period_id'] === (int) $periodId) {
                return [
                    'conflict' => true,
                    'message' => "Classroom is already in use on {$day} period {$periodId}",
                    'details' => [
                        'existing_class_section_id' => (int) $row['class_section_id'],
                        'existing_teacher_id' => (int) $row['teacher_id'],
                        'existing_timetable_id' => (int) $row['timetable_id'],
                        'override_type' => $row['_override_type'] ?? null,
                    ],
                ];
            }
        }
        return null;
    }

    // ─── Teacher workload cap checks (warnings, not blocks) ─────────────

    /**
     * Check if adding a period would exceed teacher's workload caps.
     * Returns an array of warnings (empty array = no warnings).
     *
     * @return array<int, array{type: string, message: string, current: int, max: int}>
     */
    public static function checkTeacherWorkload(int $teacherId, int $academicYearId, string $day, ?int $ignoreTimetableId = null): array
    {
        $warnings = [];
        $teacher = Staff::find($teacherId);

        if (!$teacher) {
            return $warnings;
        }

        $baseTimetables = Timetable::where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->get()
            ->map(fn($t) => $t->toArray())
            ->toArray();

        // Find all term_weeks for this academic year to check workload across all weeks
        $termWeeks = TermWeek::where('academic_year_id', $academicYearId)->get();

        if ($termWeeks->isEmpty()) {
            // No term weeks defined — check base schedule only
            $dayLoad = 0;
            $weekLoad = 0;
            foreach ($baseTimetables as $t) {
                if ($ignoreTimetableId !== null && (int) $t['timetable_id'] === (int) $ignoreTimetableId) {
                    continue;
                }
                $weekLoad++;
                if ($t['day_of_week'] === $day) {
                    $dayLoad++;
                }
            }

            if ($teacher->max_periods_per_day && $dayLoad >= $teacher->max_periods_per_day) {
                $warnings[] = [
                    'type' => 'max_periods_per_day',
                    'message' => "{$teacher->full_name} already has {$dayLoad} period(s) on {$day}" .
                        ($dayLoad > $teacher->max_periods_per_day ? " (exceeds max of {$teacher->max_periods_per_day})" : " (at max of {$teacher->max_periods_per_day})"),
                    'current' => $dayLoad,
                    'max' => (int) $teacher->max_periods_per_day,
                ];
            }

            if ($teacher->max_periods_per_week && $weekLoad >= $teacher->max_periods_per_week) {
                $warnings[] = [
                    'type' => 'max_periods_per_week',
                    'message' => "{$teacher->full_name} already has {$weekLoad} period(s) per week" .
                        ($weekLoad > $teacher->max_periods_per_week ? " (exceeds max of {$teacher->max_periods_per_week})" : " (at max of {$teacher->max_periods_per_week})"),
                    'current' => $weekLoad,
                    'max' => (int) $teacher->max_periods_per_week,
                ];
            }

            return $warnings;
        }

        // Check workload per term week using effective schedule
        foreach ($termWeeks as $tw) {
            $effective = static::resolveEffectiveSchedule($academicYearId, $tw->id, $ignoreTimetableId);
            $dayLoad = 0;
            $weekLoad = 0;

            foreach ($effective as $row) {
                if ((int) $row['teacher_id'] !== (int) $teacherId) {
                    continue;
                }
                $weekLoad++;
                if ($row['day_of_week'] === $day) {
                    $dayLoad++;
                }
            }

            if ($teacher->max_periods_per_day && $dayLoad >= $teacher->max_periods_per_day) {
                $warnings[] = [
                    'type' => 'max_periods_per_day',
                    'message' => "{$teacher->full_name} has {$dayLoad} period(s) on {$day} in {$tw->label}" .
                        ($dayLoad > $teacher->max_periods_per_day ? " (exceeds max of {$teacher->max_periods_per_day})" : " (at max of {$teacher->max_periods_per_day})"),
                    'current' => $dayLoad,
                    'max' => (int) $teacher->max_periods_per_day,
                    'term_week' => $tw->label,
                ];
            }

            if ($teacher->max_periods_per_week && $weekLoad >= $teacher->max_periods_per_week) {
                $warnings[] = [
                    'type' => 'max_periods_per_week',
                    'message' => "{$teacher->full_name} has {$weekLoad} period(s) in {$tw->label}" .
                        ($weekLoad > $teacher->max_periods_per_week ? " (exceeds max of {$teacher->max_periods_per_week})" : " (at max of {$teacher->max_periods_per_week})"),
                    'current' => $weekLoad,
                    'max' => (int) $teacher->max_periods_per_week,
                    'term_week' => $tw->label,
                ];
            }
        }

        return $warnings;
    }
}
