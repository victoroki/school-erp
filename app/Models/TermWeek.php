<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TermWeek extends Model
{
    public $table = 'term_weeks';

    public $fillable = [
        'term_id',
        'academic_year_id',
        'week_number',
        'start_date',
        'end_date',
        'label',
        'is_exam_week',
        'is_half_term',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'week_number' => 'integer',
        'is_exam_week' => 'boolean',
        'is_half_term' => 'boolean',
    ];

    public static array $rules = [
        'term_id' => 'required|exists:terms,id',
        'academic_year_id' => 'required|exists:academic_years,academic_year_id',
        'week_number' => 'required|integer|min:1',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ];

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function overrides()
    {
        return $this->hasMany(TimetableOverride::class, 'term_week_id');
    }

    /**
     * Auto-generate weeks for a term based on its start/end dates.
     * Skips weeks that overlap with academic holidays.
     */
    public static function generateForTerm(Term $term): array
    {
        $weeks = [];
        $weekNumber = 1;
        $current = $term->start_date->copy()->startOfWeek();
        $end = $term->end_date->copy()->endOfWeek();

        $holidays = \App\Models\AcademicEvent::where('academic_year_id', $term->academic_year_id)
            ->where('event_type', 'holiday')
            ->where('start_date', '<=', $end)
            ->where('end_date', '>=', $current)
            ->get();

        while ($current->lte($end)) {
            $weekEnd = $current->copy()->endOfWeek();

            $weekStart = $current->copy()->max($term->start_date);
            $effectiveEnd = $weekEnd->copy()->min($term->end_date);

            $overlapsHoliday = $holidays->contains(function ($holiday) use ($weekStart, $effectiveEnd) {
                return $holiday->start_date <= $effectiveEnd && ($holiday->end_date ?? $holiday->start_date) >= $weekStart;
            });

            $overlapsExam = \App\Models\AcademicEvent::where('academic_year_id', $term->academic_year_id)
                ->where('event_type', 'exam')
                ->where('start_date', '<=', $effectiveEnd)
                ->where('end_date', '>=', $weekStart)
                ->exists();

            $label = 'Week ' . $weekNumber;
            if ($overlapsHoliday) {
                $label .= ' (Holiday)';
            } elseif ($overlapsExam) {
                $label .= ' (Exam)';
            }

            $weeks[] = [
                'term_id' => $term->id,
                'academic_year_id' => $term->academic_year_id,
                'week_number' => $weekNumber,
                'start_date' => $weekStart->toDateString(),
                'end_date' => $effectiveEnd->toDateString(),
                'label' => $label,
                'is_exam_week' => $overlapsExam,
                'is_half_term' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $current->addWeek();
            $weekNumber++;
        }

        return $weeks;
    }
}
