<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Support\Facades\DB;

/**
 * Owns the rules for retiring a subject.
 *
 * Six tables point at `subjects.subject_id` with ON DELETE RESTRICT:
 * class_subjects, teacher_subjects, assignments, exam_results,
 * exam_schedules and timetable. Deleting a subject that has been taught,
 * examined or timetabled therefore always ends in MySQL error 1451, and
 * "fixing" that by dropping a foreign key or cascade-deleting rows would
 * destroy the academic record of a real learner.
 *
 * The rule this service enforces instead:
 *
 *   - a subject with no dependent rows is genuinely unused and may be
 *     deleted outright;
 *   - a subject with any dependent row is *archived* (is_active = false),
 *     which keeps every historical record intact and readable while taking
 *     the subject out of allocation and reporting lists.
 *
 * The controller asks this service rather than discovering the constraint
 * through an exception, so the refusal names the exact tables and counts
 * blocking the delete.
 */
class SubjectLifecycleService
{
    /**
     * Dependent tables, with the singular noun used in the refusal message
     * and a human label for the usage summary shown on the subject page.
     *
     * `nouns` produces e.g. "1 class-subject allocation and 2 timetable slots".
     */
    public const DEPENDENTS = [
        'class_subjects'  => ['noun' => 'class-subject allocation', 'label' => 'Class allocations'],
        'teacher_subjects' => ['noun' => 'teacher allocation', 'label' => 'Teacher allocations'],
        'assignments'     => ['noun' => 'assignment', 'label' => 'Assignments'],
        'exam_results'    => ['noun' => 'exam result', 'label' => 'Exam results'],
        'exam_schedules'  => ['noun' => 'exam sitting', 'label' => 'Exam sittings'],
        'timetable'       => ['noun' => 'timetable slot', 'label' => 'Timetable slots'],
    ];

    /**
     * Count every dependent row for a subject.
     *
     * @return array<string, int> table => count, only non-zero entries
     */
    public function usageCounts(Subject $subject): array
    {
        $counts = [];

        foreach (array_keys(self::DEPENDENTS) as $table) {
            $count = DB::table($table)->where('subject_id', $subject->subject_id)->count();

            if ($count > 0) {
                $counts[$table] = $count;
            }
        }

        return $counts;
    }

    /**
     * Usage counts keyed by the friendly label used in the UI, always
     * including every table so the summary grid has a stable shape.
     *
     * @return array<string, array{label: string, count: int}>
     */
    public function usageSummary(Subject $subject): array
    {
        $counts = $this->usageCounts($subject);
        $summary = [];

        foreach (self::DEPENDENTS as $table => $meta) {
            $summary[$table] = [
                'label' => $meta['label'],
                'count' => $counts[$table] ?? 0,
            ];
        }

        return $summary;
    }

    public function totalUsage(Subject $subject): int
    {
        return array_sum($this->usageCounts($subject));
    }

    /**
     * A subject with history must be archived rather than deleted.
     */
    public function hasHistory(Subject $subject): bool
    {
        return $this->totalUsage($subject) > 0;
    }

    /**
     * Human-readable explanation of why a subject cannot be deleted.
     */
    public function deletionBlockers(Subject $subject): array
    {
        $blocks = [];

        foreach ($this->usageCounts($subject) as $table => $count) {
            $noun = self::DEPENDENTS[$table]['noun'];
            $blocks[] = $count . ' ' . $noun . ($count === 1 ? '' : 's');
        }

        return $blocks;
    }

    /**
     * The full sentence shown to the administrator when a delete is refused.
     */
    public function deletionRefusalMessage(Subject $subject): string
    {
        $blocks = $this->deletionBlockers($subject);
        $list = implode(', ', $blocks);

        return '“' . $subject->name . '” cannot be deleted because it still has academic '
            . 'history (' . $list . '). Archive it instead — archiving keeps every mark, '
            . 'schedule and report intact while removing the subject from new allocations.';
    }

    /**
     * Archive a subject. Historical rows are left exactly as they are.
     */
    public function archive(Subject $subject): void
    {
        $subject->forceFill(['is_active' => false])->save();
    }

    public function restore(Subject $subject): void
    {
        $subject->forceFill(['is_active' => true])->save();
    }

    /**
     * Add a `{table}_count` sub-select to a subject query.
     *
     * Eloquent's `withCount()` cannot express these six counts: its array key
     * is resolved as a *relation* name, and `Subject` has relations for only
     * two of the six tables (assignments has no model at all). A correlated
     * sub-select keyed on `subject_id` states the same thing without inventing
     * a relation, and it costs one extra pass over the page — not one query
     * per row.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function withUsageCounts($query)
    {
        // Query\Builder::selectSub() *appends* to the select list — unlike
        // withCount() it does not fall back to `table.*` for you — so without
        // this the rows come back holding nothing but the six counts and
        // subject_id itself is missing.
        if (empty($query->getQuery()->columns)) {
            $query->select($query->getModel()->getTable() . '.*');
        }

        foreach (array_keys(self::DEPENDENTS) as $table) {
            // count(*) rather than `1`, so a subject with no rows in this
            // table reads 0 instead of NULL in the view.
            $query->selectSub(
                DB::table($table)
                    ->select(DB::raw('count(*)'))
                    ->whereColumn($table . '.subject_id', 'subjects.subject_id'),
                $table . '_count'
            );
        }

        return $query;
    }

    /**
     * Whether a subject may be offered in a picker (class allocation,
     * timetabling, report cards…). Archived subjects are excluded.
     */
    public function scopeSelectable($query)
    {
        return $query->where('subjects.is_active', true);
    }
}
