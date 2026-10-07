<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;

/**
 * Library policy settings.
 *
 * The loan period and the fine rate were hardcoded in LibraryService: 14 days
 * and KES 50 per day. Both are policy decisions a school changes — a term-long
 * loan, a higher fine — and a value buried in a service method means a code
 * change and a deploy to adjust them. They are read from the `settings` table
 * now, with the old numbers kept as the defaults so behaviour is unchanged
 * until an administrator saves something different.
 */
class LibrarySettings
{
    public const FINE_PER_DAY = 'library.fine_per_day';
    public const LOAN_PERIOD_DAYS = 'library.loan_period_days';

    /** The values that were previously hardcoded, kept as fallbacks. */
    public const DEFAULT_FINE_PER_DAY = 50.0;
    public const DEFAULT_LOAN_PERIOD_DAYS = 14;

    public static function finePerDay(): float
    {
        return max(0.0, Setting::number(self::FINE_PER_DAY, self::DEFAULT_FINE_PER_DAY));
    }

    public static function loanPeriodDays(): int
    {
        return max(1, Setting::integer(self::LOAN_PERIOD_DAYS, self::DEFAULT_LOAN_PERIOD_DAYS));
    }

    /**
     * Every library setting, for the settings form.
     *
     * @return array{fine_per_day: float, loan_period_days: int}
     */
    public static function all(): array
    {
        return [
            'fine_per_day' => self::finePerDay(),
            'loan_period_days' => self::loanPeriodDays(),
        ];
    }

    /**
     * Fine for a book that is N days past its due date.
     */
    public static function fineFor(int $daysOverdue): float
    {
        return round(max(0, $daysOverdue) * self::finePerDay(), 2);
    }

    /**
     * How many days late a book is, as of now. Zero when it is not yet due.
     *
     * Carbon's diffInDays returns $other - $this when signed, so
     * `now()->diffInDays($dueDate, false)` is negative for an overdue book —
     * the opposite of what the name suggests. Only the absolute difference is
     * used here, and only after confirming the book is actually late, so the
     * sign cannot leak into a fine.
     */
    public static function daysOverdue($dueDate, ?Carbon $asOf = null): int
    {
        if (empty($dueDate)) {
            return 0;
        }

        $due = $dueDate instanceof Carbon ? $dueDate->copy() : Carbon::parse($dueDate);
        $asOf = $asOf ? $asOf->copy() : Carbon::now();

        if ($asOf->lessThanOrEqualTo($due)) {
            return 0;
        }

        return (int) $due->diffInDays($asOf);
    }

    /**
     * What a book currently owes, as of now.
     */
    public static function outstandingFine($dueDate, ?Carbon $asOf = null): float
    {
        return self::fineFor(self::daysOverdue($dueDate, $asOf));
    }
}
