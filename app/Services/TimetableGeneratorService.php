<?php

namespace App\Services;

use App\Services\TimetableGenerationResult;
use App\Services\TimetableConflictService;

/**
 * Builds a collision-free weekly timetable from class-section, subject,
 * teacher and period data. Pure array logic (no DB) so it is unit-testable.
 */
class TimetableGeneratorService
{
    public const DEFAULT_DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

    /**
     * @param array<string, mixed> $input
     *  - academic_year_id  int
     *  - class_sections    list of ['id','class_id','classroom_id','label']
     *  - periods           list of ['id','start_time','name'] (teaching periods only)
     *  - days              list of day keys (optional, defaults to Mon-Fri)
     *  - class_subjects    list of ['class_id','subject_id','academic_year_id','periods_per_week']
     *  - teacher_subjects  list of ['staff_id','subject_id','class_section_id','academic_year_id']
     *  - subjects          [subject_id => name]
     *  - teachers          [staff_id => name]
     *  - classrooms        [classroom_id => room_number]
     *  - existing          optional list of already-scheduled rows to avoid (class_section_id, day_of_week, period_id, teacher_id, classroom_id, academic_year_id)
     */
    public function generate(array $input): TimetableGenerationResult
    {
        $result = new TimetableGenerationResult();

        $academicYearId = (int) ($input['academic_year_id'] ?? 0);
        $classSections  = $input['class_sections'] ?? [];
        $periods        = $input['periods'] ?? [];
        $days           = $input['days'] ?? self::DEFAULT_DAYS;
        $classSubjects  = $input['class_subjects'] ?? [];
        $teacherSubjects = $input['teacher_subjects'] ?? [];
        $subjects       = $input['subjects'] ?? [];
        $teachers       = $input['teachers'] ?? [];
        $classrooms     = $input['classrooms'] ?? [];
        $existing       = $input['existing'] ?? [];

        if (count($classSections) === 0 || count($periods) === 0) {
            return $result;
        }

        // Work list of placements we have committed to so far (seeded with existing lessons).
        $placed = array_map(static function ($row) {
            return $row;
        }, $existing);

        $requirements = [];
        foreach ($classSections as $cs) {
            $classId = (int) $cs['class_id'];
            $classroomId = $cs['classroom_id'] ?? null;

            // Subjects configured for this class in this academic year.
            $subjectIds = [];
            $subjectPeriods = []; // subject_id => periods per week
            foreach ($classSubjects as $csj) {
                if ((int) $csj['class_id'] === $classId && (int) $csj['academic_year_id'] === $academicYearId) {
                    $subjectId = (int) $csj['subject_id'];
                    $subjectIds[] = $subjectId;
                    $subjectPeriods[$subjectId] = max(1, (int) ($csj['periods_per_week'] ?? 1));
                }
            }
            $subjectIds = array_values(array_unique($subjectIds));

            foreach ($subjectIds as $subjectId) {
                // Teachers assigned to teach this subject to this class section.
                $candidates = [];
                foreach ($teacherSubjects as $ts) {
                    if ((int) $ts['class_section_id'] === (int) $cs['id']
                        && (int) $ts['subject_id'] === $subjectId
                        && (int) $ts['academic_year_id'] === $academicYearId) {
                        $candidates[] = (int) $ts['staff_id'];
                    }
                }
                $candidates = array_values(array_unique($candidates));

                $requirements[] = [
                    'class_section' => $cs,
                    'subject_id' => $subjectId,
                    'classroom_id' => $classroomId,
                    'teacher_candidates' => $candidates,
                    'periods_per_week' => $subjectPeriods[$subjectId] ?? 1,
                ];
            }
        }

        // Most constrained first (fewest possible teachers) gives the best packing.
        usort($requirements, static function ($a, $b) {
            return count($a['teacher_candidates']) <=> count($b['teacher_candidates']);
        });

        // Load-balanced teacher ordering helper.
        $teacherLoad = static function ($teacherId) use ($placed) {
            $load = 0;
            foreach ($placed as $p) {
                if ((int) $p['teacher_id'] === (int) $teacherId) {
                    $load++;
                }
            }
            return $load;
        };

        foreach ($requirements as $req) {
            $cs = $req['class_section'];
            $classSectionId = (int) $cs['id'];
            $subjectId = (int) $req['subject_id'];
            $classroomId = $req['classroom_id'] !== null ? (int) $req['classroom_id'] : null;
            $candidates = $req['teacher_candidates'];

            $subjectName = $subjects[$subjectId] ?? "Subject #{$subjectId}";
            $csLabel = $cs['label'] ?? 'Section #' . $classSectionId;
            $periodsPerWeek = max(1, (int) ($req['periods_per_week'] ?? 1));

            if (count($candidates) === 0) {
                $result->unplaced[] = [
                    'class_section' => $csLabel,
                    'subject' => $subjectName,
                    'teachers' => [],
                    'reason' => 'No teacher is assigned to teach this subject to this class section.',
                ];
                continue;
            }

            if ($classroomId === null || !isset($classrooms[$classroomId])) {
                $result->unplaced[] = [
                    'class_section' => $csLabel,
                    'subject' => $subjectName,
                    'teachers' => $this->teacherNames($candidates, $teachers),
                    'reason' => 'The class section has no classroom configured.',
                ];
                continue;
            }

            // At most ceil(periodsPerWeek / days) lessons of the same subject per day,
            // so a subject with 5 weekly periods spreads across the week instead of
            // stacking all 5 on Monday.
            $maxPerDay = (int) ceil($periodsPerWeek / max(1, count($days)));

            $placedForSubject = 0;

            for ($lesson = 0; $lesson < $periodsPerWeek; $lesson++) {
                $slotFound = false;
                foreach ($candidates as $teacherId) {
                    if ($slotFound) {
                        break;
                    }
                    foreach ($days as $day) {
                        if ($slotFound) {
                            break;
                        }

                        // Don't exceed the per-day cap for this subject on this class.
                        $sameDayCount = 0;
                        foreach ($placed as $p) {
                            if ((int) ($p['class_section_id'] ?? 0) === $classSectionId
                                && (int) ($p['subject_id'] ?? 0) === $subjectId
                                && ($p['day_of_week'] ?? null) === $day) {
                                $sameDayCount++;
                            }
                        }
                        if ($sameDayCount >= $maxPerDay) {
                            continue;
                        }

                        foreach ($periods as $period) {
                            $periodId = (int) $period['id'];

                            if (TimetableConflictService::classSlotTaken($placed, $classSectionId, $academicYearId, $day, $periodId)) {
                                continue;
                            }
                            if (TimetableConflictService::teacherConflict($placed, $teacherId, $academicYearId, $day, $periodId)) {
                                continue;
                            }
                            if (TimetableConflictService::classroomConflict($placed, $classroomId, $academicYearId, $day, $periodId)) {
                                continue;
                            }

                            $row = [
                                'class_section_id' => $classSectionId,
                                'day_of_week' => $day,
                                'period_id' => $periodId,
                                'subject_id' => $subjectId,
                                'teacher_id' => $teacherId,
                                'classroom_id' => $classroomId,
                                'academic_year_id' => $academicYearId,
                            ];

                            $result->placements[] = $row;
                            $placed[] = $row;
                            $slotFound = true;
                            $placedForSubject++;
                            break;
                        }
                    }
                }

                if (!$slotFound) {
                    break; // can't fit any more of this subject
                }
            }

            if ($placedForSubject < $periodsPerWeek) {
                $result->unplaced[] = [
                    'class_section' => $csLabel,
                    'subject' => $subjectName,
                    'teachers' => $this->teacherNames($candidates, $teachers),
                    'reason' => $placedForSubject > 0
                        ? "Placed {$placedForSubject} of {$periodsPerWeek} weekly periods; no free slot remains for the rest."
                        : 'No free slot exists where the class section, every candidate teacher and the classroom are all available.',
                ];
            }
        }

        return $result;
    }

    /**
     * @param array<int, int> $teacherIds
     * @param array<int, string> $teachers
     * @return array<int, string>
     */
    private function teacherNames(array $teacherIds, array $teachers): array
    {
        $names = [];
        foreach ($teacherIds as $id) {
            $names[] = $teachers[$id] ?? "Teacher #{$id}";
        }
        return $names;
    }
}
