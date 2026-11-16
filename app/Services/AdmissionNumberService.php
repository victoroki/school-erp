<?php

namespace App\Services;

use App\Models\Student;

/**
 * Generates admission numbers.
 *
 * Previously nothing generated one: the web form relied on the operator typing
 * a value and only the CSV import template showed the expected shape
 * (ADM-2025-001). This produces the same format so both paths agree.
 *
 * The number is scoped to the admission year and derived from the highest
 * existing sequence for that year rather than a COUNT(), because a count
 * collides as soon as any learner in that year is removed.
 */
class AdmissionNumberService
{
    public const PREFIX = 'ADM';

    /** Sequence width, so ADM-2026-007 sorts naturally alongside ADM-2026-042. */
    public const SEQUENCE_WIDTH = 3;

    /**
     * The next free admission number, e.g. ADM-2026-007.
     */
    public function next(?int $year = null): string
    {
        $year = $year ?: (int) date('Y');
        $prefix = self::PREFIX . '-' . $year . '-';

        $highest = 0;

        foreach ($this->existingForYear($prefix) as $admissionNo) {
            $sequence = (int) substr($admissionNo, strlen($prefix));

            if ($sequence > $highest) {
                $highest = $sequence;
            }
        }

        $next = $highest + 1;

        // Guard against a gap-free assumption being wrong (manually typed
        // numbers, or a row deleted outside the app).
        do {
            $candidate = $prefix . str_pad((string) $next, self::SEQUENCE_WIDTH, '0', STR_PAD_LEFT);
            $next++;
        } while (Student::where('admission_no', $candidate)->exists());

        return $candidate;
    }

    /**
     * @return array<int, string>
     */
    protected function existingForYear(string $prefix): array
    {
        return Student::where('admission_no', 'like', $prefix . '%')
            ->pluck('admission_no')
            ->all();
    }
}
