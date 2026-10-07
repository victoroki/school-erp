<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Services\AcademicCalendarService;
use Illuminate\Console\Command;

/**
 * Keeps the academic calendar honest without anyone having to press a button.
 *
 * Term status used to be whatever was last set by hand. A year that reached its
 * final term and stopped still reported that term as active, and a year that had
 * been set up ahead of time left every term 'upcoming' until somebody visited
 * the terms page. Fee assignment reads status to decide which term to charge
 * against, so a stale status means fees land on the wrong term.
 *
 * Each term's status is a function of its own dates, so this recomputes them:
 * a term that has ended becomes completed, the term containing today becomes
 * active, and the rest stay upcoming. Movement is forward only, so a term
 * activated early by hand is respected.
 */
class SyncAcademicTerms extends Command
{
    protected $signature = 'academic:sync-terms
                            {--year= : Limit to one academic_year_id}
                            {--dry-run : Report what would change without writing}';

    protected $description = 'Advance academic term statuses to match their dates, and flag a year with no terms';

    public function handle(AcademicCalendarService $calendar): int
    {
        $yearId = $this->option('year') !== null ? (int) $this->option('year') : null;
        $dryRun = (bool) $this->option('dry-run');

        if ($yearId !== null && ! AcademicYear::find($yearId)) {
            $this->error("No academic year with id {$yearId}.");

            return self::FAILURE;
        }

        // Read the plan first so --dry-run can report it without writing. Calling
        // the sync directly here would have made "dry run" a misnomer.
        $planned = $calendar->plannedStatusChanges($yearId);

        if ($dryRun) {
            $this->info('Dry run: nothing has been written.');

            foreach ($planned as $change) {
                $this->line(sprintf(
                    '  %s: %s -> %s',
                    $change['term']->name,
                    $change['from'],
                    $change['to']
                ));
            }

            $this->line(sprintf('%d term status update(s) would be applied.', count($planned)));
        } else {
            foreach ($planned as $change) {
                $this->line(sprintf(
                    '  %s: %s -> %s',
                    $change['term']->name,
                    $change['from'],
                    $change['to']
                ));
            }

            $changed = $calendar->syncTermStatuses($yearId);
            $this->info("Advanced {$changed} term status(es).");
        }

        // A year whose terms have all finished has no active term left, which is
        // what stops fees being assigned at all. It is worth saying so rather
        // than leaving it to be discovered on a fee report.
        $current = $calendar->currentYear();

        if ($current) {
            $terms = $current->terms()->count();

            if ($terms === 0) {
                $this->warn(sprintf(
                    'Academic year %s has no terms. Fees cannot be assigned to it — open the academic year and save it again.',
                    $current->name
                ));
            } elseif ($current->terms()->where('status', 'active')->doesntExist()) {
                $this->warn(sprintf(
                    'Academic year %s has no active term, so no fees will be assigned until one is active.',
                    $current->name
                ));
            }
        }

        if (! $dryRun) {
            $this->line('Current term: ' . ($calendar->currentTerm()?->name ?? 'none'));
        }

        return self::SUCCESS;
    }
}
