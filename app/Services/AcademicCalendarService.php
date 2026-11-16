<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Term;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds and maintains the academic calendar.
 *
 * Terms used to be a fixed, hand-maintained set: AcademicYearSeeder hardcoded
 * 2025 and 2026 with hardcoded term dates, and AcademicYearController::store()
 * created a year with no terms at all. So adding an academic year through the
 * UI produced a year that could not be used — fee assignment refused it with
 * "No terms are defined for the selected academic year" and the fee structure
 * form had an empty term list. Once 2026-11-20 had passed there was no way to
 * reach 2027 without another code change and reseed.
 *
 * The calendar is now derived from the year:
 *
 *  1. Creating a year creates its terms. The pattern is taken from the most
 *     recent existing year and remapped onto the new year's dates, so a school
 *     that has three terms keeps three, and one that has deliberately moved to
 *     four keeps four with its own boundaries. With no earlier year to copy, the
 *     year is split into three equal terms.
 *
 *  2. A term's status follows its dates — past is completed, containing today
 *     is active, future is upcoming — so the term advances without anyone
 *     remembering to press a button. TermController::activate() remains for
 *     deliberately overriding this.
 *
 *  3. Exactly one academic year is current. `academic_years.is_current` has no
 *     unique constraint, and around fifteen call sites resolve the current year
 *     with where('is_current', true)->first(), so two current years meant an
 *     arbitrary one won.
 */
class AcademicCalendarService
{
    /** Terms assumed for a year with no earlier year to copy. */
    public const DEFAULT_TERM_COUNT = 3;

    /**
     * Create the terms for an academic year.
     *
     * Idempotent: a year that already has terms is left untouched, so this can
     * be called on every save without duplicating anything.
     *
     * @return Collection<int, Term>
     */
    public function createTermsFor(AcademicYear $year): Collection
    {
        if ($year->terms()->exists()) {
            return $year->terms()->ordered()->get();
        }

        $template = $this->templateYear($year);

        $definitions = $template
            ? $this->mapFromTemplate($template, $year)
            : $this->splitEvenly($year, self::DEFAULT_TERM_COUNT);

        foreach ($definitions as $definition) {
            Term::create($definition + ['academic_year_id' => $year->academic_year_id]);
        }

        return $year->terms()->ordered()->get();
    }

    /**
     * The most recent other year whose terms can be used as a pattern.
     *
     * Falls back to whichever year has terms if the chronologically previous one
     * has none, which keeps a school that has only just started using terms
     * from losing its pattern.
     */
    private function templateYear(AcademicYear $year): ?AcademicYear
    {
        $previous = AcademicYear::where('academic_year_id', '!=', $year->academic_year_id)
            ->where('start_date', '<', $year->start_date)
            ->orderByDesc('start_date')
            ->get()
            ->first(fn (AcademicYear $candidate) => $candidate->terms()->exists());

        return $previous;
    }

    /**
     * Remap a previous year's term boundaries onto the new year.
     *
     * Positions are taken as a fraction of the source year's length rather than
     * by adding a year, because a calendar year is not exactly 365 days: adding
     * one year to 2026-01-05..2026-04-03 would drift terms that run to the edge
     * of the year, and a leap year or an unusual term length would compound it.
     * Mapping by fraction keeps each term the same share of the year and keeps
     * every term inside the new year's boundaries.
     *
     * @return array<int, array<string, mixed>>
     */
    private function mapFromTemplate(AcademicYear $template, AcademicYear $year): array
    {
        $sourceStart = Carbon::parse($template->start_date)->startOfDay();
        $sourceEnd = Carbon::parse($template->end_date)->startOfDay();
        // Inclusive span, so a year and its terms are measured the same way.
        $sourceLength = max(1, (int) $sourceStart->diffInDays($sourceEnd) + 1);

        $targetStart = Carbon::parse($year->start_date)->startOfDay();
        $targetEnd = Carbon::parse($year->end_date)->startOfDay();
        $targetLength = max(1, (int) $targetStart->diffInDays($targetEnd) + 1);

        $definitions = [];

        foreach ($template->terms()->ordered()->get() as $templateTerm) {
            $offset = (int) round(
                $sourceStart->diffInDays(Carbon::parse($templateTerm->start_date)->startOfDay())
                / $sourceLength * $targetLength
            );

            $length = max(1, (int) round(
                $sourceStart->diffInDays(Carbon::parse($templateTerm->end_date)->startOfDay())
                / $sourceLength * $targetLength
            ) - $offset);

            $start = $targetStart->copy()->addDays(min($offset, $targetLength));
            $end = $start->copy()->addDays($length);

            // Never let a term spill past the end of the year it belongs to.
            if ($end->greaterThan($targetEnd)) {
                $end = $targetEnd->copy();
            }

            if ($end->lessThan($start)) {
                $end = $start->copy();
            }

            $definitions[] = [
                'name' => $templateTerm->name,
                'code' => $templateTerm->code,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                // Fee deadlines are set relative to when a term opens, so the
                // offset from the term start is carried across rather than the
                // absolute date.
                'fee_due_date' => $this->shiftFeeDueDate($templateTerm, $start),
                'display_order' => $templateTerm->display_order,
                // Deliberately not carried over: status is recalculated from the
                // new dates, so copying last year's "active" term forward would
                // start the new year with a term that is already finished.
                'status' => $this->statusFor($start, $end),
            ];
        }

        return $definitions;
    }

    private function shiftFeeDueDate(Term $templateTerm, Carbon $newStart): ?string
    {
        if (! $templateTerm->fee_due_date) {
            return null;
        }

        $offset = Carbon::parse($templateTerm->start_date)
            ->startOfDay()
            ->diffInDays(Carbon::parse($templateTerm->fee_due_date)->startOfDay());

        return $newStart->copy()->addDays((int) $offset)->toDateString();
    }

    /**
     * Split a year into equal terms. The starting point for a year with no
     * earlier year is the year itself rather than the calendar year, so a school
     * starting in, say, September still gets three whole terms.
     *
     * @return array<int, array<string, mixed>>
     */
    private function splitEvenly(AcademicYear $year, int $count): array
    {
        $count = max(1, $count);
        $start = Carbon::parse($year->start_date)->startOfDay();
        $end = Carbon::parse($year->end_date)->startOfDay();

        // diffInDays is the gap between two dates, but a term has to *cover*
        // both of them: 1 Jan to 31 Dec is 365 days across but 366 days long.
        // Without the +1 the last term stops a day short of the year end.
        $length = max($count, (int) $start->diffInDays($end) + 1);

        // Days do not divide evenly, so hand the remainder to the last term.
        $base = intdiv($length, $count);
        $definitions = [];

        for ($i = 0; $i < $count; $i++) {
            $termStart = $start->copy()->addDays($i * $base);
            $termLength = $i === $count - 1 ? $length - ($i * $base) : $base;
            $termEnd = $termStart->copy()->addDays(max(0, $termLength - 1));

            $definitions[] = [
                'name' => 'Term ' . ($i + 1),
                'code' => 'T' . ($i + 1),
                'start_date' => $termStart->toDateString(),
                'end_date' => $termEnd->toDateString(),
                // Two weeks into the term, matching the seeded calendar.
                'fee_due_date' => $termStart->copy()->addDays(14)->toDateString(),
                'display_order' => $i + 1,
                'status' => $this->statusFor($termStart, $termEnd),
            ];
        }

        return $definitions;
    }

    /**
     * The status a term should have given its dates.
     */
    public function statusFor($startDate, $endDate, ?Carbon $asOf = null): string
    {
        $asOf = ($asOf ?? Carbon::now())->copy()->startOfDay();
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($end->lessThan($asOf)) {
            return 'completed';
        }

        if ($start->greaterThan($asOf)) {
            return 'upcoming';
        }

        return 'active';
    }

    /**
     * Bring every term's status back in line with its dates.
     *
     * Only forward movement is applied. A term whose dates have passed becomes
     * completed, and a term containing today becomes active — which is what lets
     * the term advance without anyone pressing a button. A term is never dragged
     * backwards, so an administrator who activated a term early by hand (the one
     * thing TermController::activate() is for) keeps their choice.
     *
     * @return int number of terms changed
     */
    /**
     * What a sync would change, without changing it.
     *
     * @return array<int, array{term: Term, from: string, to: string}>
     */
    public function plannedStatusChanges(?int $academicYearId = null, ?Carbon $asOf = null): array
    {
        $asOf = $asOf ?? Carbon::now();
        $planned = [];

        $query = Term::query()
            ->orderBy('academic_year_id')
            ->orderBy('display_order');

        if ($academicYearId !== null) {
            $query->where('academic_year_id', $academicYearId);
        }

        foreach ($query->get() as $term) {
            $expected = $this->statusFor($term->start_date, $term->end_date, $asOf);

            if ($this->advancesTo($term->status, $expected)) {
                $planned[] = ['term' => $term, 'from' => $term->status, 'to' => $expected];
            }
        }

        return $planned;
    }

    /**
     * Bring every term's status back in line with its dates.
     *
     * Only forward movement is applied. A term whose dates have passed becomes
     * completed, and a term containing today becomes active — which is what lets
     * the term advance without anyone pressing a button. A term is never dragged
     * backwards, so an administrator who activated a term early by hand (the one
     * thing TermController::activate() is for) keeps their choice.
     *
     * @return int number of terms changed
     */
    public function syncTermStatuses(?int $academicYearId = null, ?Carbon $asOf = null): int
    {
        $asOf = $asOf ?? Carbon::now();
        $changed = 0;

        foreach ($this->plannedStatusChanges($academicYearId, $asOf) as $change) {
            $change['term']->update(['status' => $change['to']]);
            $changed++;
        }

        return $changed + $this->enforceSingleActiveTerm($academicYearId, $asOf);
    }

    /**
     * Whether moving from one status to another is forward progress.
     */
    private function advancesTo(string $current, string $expected): bool
    {
        $rank = ['upcoming' => 0, 'active' => 1, 'completed' => 2];

        return ($rank[$expected] ?? 0) > ($rank[$current] ?? 0);
    }

    /**
     * Leave at most one term active per year.
     *
     * Two can be active when an administrator activates a term by hand while
     * another is still running. "Current term" is read as
     * Term::active()->first() in several places, so the duplicate is resolved
     * here: the term today actually falls in keeps the flag, the other is put
     * back to the status its dates call for.
     */
    private function enforceSingleActiveTerm(?int $academicYearId, Carbon $asOf): int
    {
        $changed = 0;

        $query = Term::where('status', 'active');
        if ($academicYearId !== null) {
            $query->where('academic_year_id', $academicYearId);
        }

        foreach ($query->get()->groupBy('academic_year_id') as $terms) {
            if ($terms->count() < 2) {
                continue;
            }

            $keeper = $terms->first(
                fn (Term $t) => $this->statusFor($t->start_date, $t->end_date, $asOf) === 'active'
            ) ?? $terms->first();

            foreach ($terms as $term) {
                if ($term->id === $keeper->id) {
                    continue;
                }

                $term->update([
                    'status' => $this->statusFor($term->start_date, $term->end_date, $asOf),
                ]);
                $changed++;
            }
        }

        return $changed;
    }

    /**
     * Mark one year current and every other year not current.
     *
     * Without this, ticking 'is current' on a new year left the old one current
     * as well, and where('is_current', true)->first() then returned an
     * arbitrary row.
     */
    public function makeCurrent(AcademicYear $year): AcademicYear
    {
        AcademicYear::where('academic_year_id', '!=', $year->academic_year_id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        if (! $year->is_current) {
            $year->update(['is_current' => true]);
        }

        return $year;
    }

    /**
     * The year that should be treated as current.
     *
     * Prefers the flagged year, then falls back to the one whose dates contain
     * today, then to the latest by start date. The fallbacks matter: a school
     * that never set the flag still gets a sensible year rather than null.
     */
    public function currentYear(?Carbon $asOf = null): ?AcademicYear
    {
        $asOf = ($asOf ?? Carbon::now())->copy()->startOfDay();

        $flagged = AcademicYear::where('is_current', true)
            ->orderByDesc('start_date')
            ->first();

        if ($flagged) {
            return $flagged;
        }

        $covering = AcademicYear::whereDate('start_date', '<=', $asOf)
            ->whereDate('end_date', '>=', $asOf)
            ->orderByDesc('start_date')
            ->first();

        if ($covering) {
            return $covering;
        }

        return AcademicYear::orderByDesc('start_date')->first();
    }

    /**
     * The term that today falls in, for the current year.
     */
    public function currentTerm(?Carbon $asOf = null): ?Term
    {
        $asOf = ($asOf ?? Carbon::now())->copy()->startOfDay();
        $year = $this->currentYear($asOf);

        if (! $year) {
            return null;
        }

        $term = Term::where('academic_year_id', $year->academic_year_id)
            ->whereDate('start_date', '<=', $asOf)
            ->whereDate('end_date', '>=', $asOf)
            ->first();

        if ($term) {
            return $term;
        }

        return Term::where('academic_year_id', $year->academic_year_id)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Set up the academic year that follows the current one.
     *
     * This is the action that used to be impossible: the seeded calendar ended
     * in 2026, so a school had no way to reach 2027 without editing a seeder.
     * The new year spans the same dates one year on, terms come across
     * automatically, and the previous year is archived.
     */
    public function rollForward(?Carbon $asOf = null): AcademicYear
    {
        $asOf = $asOf ?? Carbon::now();
        $current = $this->currentYear($asOf);

        $start = $current
            ? Carbon::parse($current->end_date)->addDay()
            : Carbon::parse($asOf)->startOfYear();
        $end = $start->copy()->addYear()->subDay();

        // A year that has already been set up beyond the current one is adopted
        // rather than a further year being invented. Without this, pressing the
        // button twice walks the calendar forward two years.
        if ($current) {
            $later = AcademicYear::whereDate('start_date', '>', $current->end_date->toDateString())
                ->orderBy('start_date')
                ->first();

            if ($later) {
                $this->createTermsFor($later);
                $this->makeCurrent($later);

                return $later;
            }
        }

        $name = $start->year === $end->year
            ? (string) $start->year
            : $start->year . '/' . $end->year;

        $next = AcademicYear::firstOrCreate(
            ['name' => $name],
            [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'is_current' => true,
            ]
        );

        $this->createTermsFor($next);
        $this->makeCurrent($next);

        return $next;
    }
}
