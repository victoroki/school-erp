<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Academic years are referenced by name in a lot of places and the column is
 * unique, so the name has to be unique too or tests collide. The year is
 * derived from the current date unless overridden so a year is normally
 * "current", which most tests need.
 */
class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $start = now()->startOfYear();
        $end = $start->copy()->endOfYear();

        return [
            'name' => (string) $start->year . '-' . Str::random(4),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'is_current' => true,
        ];
    }

    public function current(): self
    {
        return $this->state(fn () => ['is_current' => true]);
    }

    public function archived(): self
    {
        return $this->state(fn () => ['is_current' => false]);
    }

    /**
     * A year that has already finished.
     */
    public function past(): self
    {
        return $this->state(fn () => [
            'name' => (string) now()->subYear()->year . '-' . Str::random(4),
            'start_date' => now()->subYear()->startOfYear()->toDateString(),
            'end_date' => now()->subYear()->endOfYear()->toDateString(),
            'is_current' => false,
        ]);
    }
}
