<?php

namespace App\Services;

use App\Models\Classroom;
use Illuminate\Support\Facades\DB;

/**
 * Owns the rules for retiring a classroom.
 *
 * Four tables point at `classrooms.classroom_id` with ON DELETE RESTRICT:
 * class_sections (ibfk_4), timetable (ibfk_5), exam_schedules (ibfk_4) — plus
 * timetable_overrides, which points here twice with ON DELETE SET NULL.
 *
 * A classroom that hosts a class section, a timetable slot or an exam sitting
 * is part of the academic record, so deleting it must be refused and the room
 * archived instead: the row and its references stay intact but the room stops
 * being offered for new sections, timetabling and exam allocation. A classroom
 * with no dependents at all is genuinely unused and may be deleted outright.
 */
class ClassroomLifecycleService
{
    /**
     * Dependent tables, with the singular noun used in the refusal message and
     * a human label for the usage summary on the show page. `set_null` marks
     * the tables that are detached (column set NULL) rather than blocking:
     * timetable_overrides is scheduling metadata, not academic history.
     */
    public const DEPENDENTS = [
        'class_sections'    => ['noun' => 'class section', 'label' => 'Class sections', 'blocks' => true, 'column' => 'classroom_id'],
        'timetable'         => ['noun' => 'timetable slot', 'label' => 'Timetable slots', 'blocks' => true, 'column' => 'classroom_id'],
        'exam_schedules'    => ['noun' => 'exam sitting', 'label' => 'Exam sittings', 'blocks' => true, 'column' => 'room_id'],
        'timetable_overrides' => ['noun' => 'timetable override', 'label' => 'Timetable overrides', 'blocks' => false, 'column' => 'substitute_classroom_id'],
    ];

    /**
     * Count every dependent row for a classroom.
     *
     * @return array<string, int> table => count, only non-zero entries
     */
    public function usageCounts(Classroom $classroom): array
    {
        $counts = [];

        foreach (self::DEPENDENTS as $table => $meta) {
            $count = DB::table($table)
                ->where(function ($q) use ($table, $meta, $classroom) {
                    // timetable_overrides references the room through two
                    // columns, both of which must be checked. Every other
                    // table names its column in DEPENDENTS (exam_schedules
                    // uses room_id, not classroom_id).
                    if ($table === 'timetable_overrides') {
                        $q->where('substitute_classroom_id', $classroom->classroom_id)
                            ->orWhere('new_classroom_id', $classroom->classroom_id);
                    } else {
                        $q->where($meta['column'], $classroom->classroom_id);
                    }
                })
                ->count();

            if ($count > 0) {
                $counts[$table] = $count;
            }
        }

        return $counts;
    }

    /**
     * Usage counts keyed by table, always including every entry so the summary
     * grid on the show page has a stable shape.
     *
     * @return array<string, array{label: string, count: int}>
     */
    public function usageSummary(Classroom $classroom): array
    {
        $counts = $this->usageCounts($classroom);
        $summary = [];

        foreach (self::DEPENDENTS as $table => $meta) {
            $summary[$table] = [
                'label' => $meta['label'],
                'count' => $counts[$table] ?? 0,
            ];
        }

        return $summary;
    }

    /**
     * A classroom with any blocking dependent row must be archived, not deleted.
     */
    public function hasHistory(Classroom $classroom): bool
    {
        foreach ($this->usageCounts($classroom) as $table => $count) {
            if ($count > 0 && self::DEPENDENTS[$table]['blocks']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Human-readable list of what blocks the delete, e.g.
     * "2 class sections, 14 timetable slots".
     */
    public function deletionBlockers(Classroom $classroom): array
    {
        $blocks = [];

        foreach ($this->usageCounts($classroom) as $table => $count) {
            if (! self::DEPENDENTS[$table]['blocks']) {
                continue;
            }

            $noun = self::DEPENDENTS[$table]['noun'];
            $blocks[] = $count . ' ' . $noun . ($count === 1 ? '' : 's');
        }

        return $blocks;
    }

    /**
     * The full sentence shown to the administrator when a delete is refused.
     */
    public function deletionRefusalMessage(Classroom $classroom): string
    {
        $blocks = $this->deletionBlockers($classroom);

        if ($blocks === []) {
            return '"' . $classroom->room_number . '" cannot be deleted because it is still referenced by academic records.';
        }

        $list = implode(', ', $blocks);

        return '"' . $classroom->room_number . '" cannot be deleted because it is still in use ('
            . $list . '). Archive it instead — archiving keeps every class allocation, '
            . 'timetable entry and exam record intact while removing the room from new allocations.';
    }

    /**
     * Archive a classroom. All dependent rows are left exactly as they are.
     */
    public function archive(Classroom $classroom): void
    {
        $classroom->forceFill(['is_active' => false])->save();
    }

    public function restore(Classroom $classroom): void
    {
        $classroom->forceFill(['is_active' => true])->save();
    }
}
