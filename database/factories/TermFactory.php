<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

class TermFactory extends Factory
{
    protected $model = Term::class;

    public function definition(): array
    {
        $start = now()->startOfYear();

        return [
            'academic_year_id' => AcademicYear::factory(),
            'name' => 'Term 1',
            'code' => 'T1',
            'start_date' => $start->toDateString(),
            'end_date' => $start->copy()->addMonths(3)->subDay()->toDateString(),
            'fee_due_date' => $start->copy()->addDays(14)->toDateString(),
            'status' => 'upcoming',
            'display_order' => 1,
        ];
    }

    public function active(): self
    {
        return $this->state(fn () => ['status' => 'active']);
    }

    public function upcoming(): self
    {
        return $this->state(fn () => ['status' => 'upcoming']);
    }

    public function completed(): self
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'start_date' => now()->subYear()->startOfYear()->toDateString(),
            'end_date' => now()->subYear()->startOfYear()->addMonths(3)->toDateString(),
        ]);
    }

    /**
     * A term that contains today, with a code to match.
     */
    public function current(int $code = 1, string $name = 'Term 1'): self
    {
        return $this->state(fn () => [
            'name' => $name,
            'code' => 'T' . $code,
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'status' => 'active',
            'display_order' => $code,
        ]);
    }
}
