<?php

namespace App\Services;

use App\Models\Student;

/**
 * The curriculum the school actually runs.
 *
 * There is no school-level curriculum column (`schools` holds only a name), so
 * it is derived from the student body: the education system the majority of
 * learners are recorded under. Curriculum is per-student on this schema
 * (`students.education_system`), and in practice a school runs one of them.
 *
 * Used wherever a sensible default is needed, so that a CBC school stops
 * defaulting to 8-4-4.
 *
 * Resolved once per instance so repeated lookups in one request are free.
 */
class CurriculumService
{
    /**
     * Used when no learner records exist yet (a fresh install), so the default
     * matches the schema's own students.education_system default.
     */
    public const FALLBACK = 'CBC';

    public const SYSTEMS = ['8-4-4', 'CBC'];

    protected ?string $resolved = null;

    /**
     * The school's curriculum: 'CBC' or '8-4-4'.
     */
    public function current(): string
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $dominant = Student::query()
            ->whereNotNull('education_system')
            ->where('education_system', '!=', '')
            ->selectRaw('education_system, COUNT(*) as total')
            ->groupBy('education_system')
            // Deterministic tie-break so the result never depends on row order.
            ->orderByDesc('total')
            ->orderBy('education_system')
            ->value('education_system');

        return $this->resolved = ($dominant ?: self::FALLBACK);
    }

    public function isCbe(): bool
    {
        return strtoupper($this->current()) === 'CBC';
    }

    /**
     * Normalise an arbitrary value to a supported system, falling back to the
     * school's own curriculum.
     */
    public function normalise(?string $system): string
    {
        $system = trim((string) $system);

        return in_array($system, self::SYSTEMS, true) ? $system : $this->current();
    }
}
