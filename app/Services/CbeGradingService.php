<?php

namespace App\Services;

/**
 * Kenya CBE (Competency Based Education) performance levels.
 *
 * Mirrors the KNEC KJSEA grading released for Grade 9 (2025): four bands
 * split into two sub-levels each, giving an 8-point achievement scale where
 * 8 is the highest.
 *
 *   EE1  90-100%  8 pts  Exceeding Expectation — Excellent
 *   EE2  75-89%   7 pts  Exceeding Expectation — Very Good
 *   ME1  58-74%   6 pts  Meeting Expectation   — Good
 *   ME2  41-57%   5 pts  Meeting Expectation   — Fair / Satisfactory
 *   AE1  31-40%   4 pts  Approaching Expectation — Needs Improvement
 *   AE2  21-30%   3 pts  Approaching Expectation — Below Average
 *   BE1  11-20%   2 pts  Below Expectation     — Well Below Average
 *   BE2   0-10%   1 pt   Below Expectation     — Minimal
 */
class CbeGradingService
{
    public const LEVELS = [
        ['code' => 'EE1', 'points' => 8, 'band' => 'Exceeding Expectation', 'min' => 90.0, 'max' => 100.0, 'descriptor' => 'Excellent', 'color' => '#047857'],
        ['code' => 'EE2', 'points' => 7, 'band' => 'Exceeding Expectation', 'min' => 75.0, 'max' => 89.99, 'descriptor' => 'Very Good', 'color' => '#059669'],
        ['code' => 'ME1', 'points' => 6, 'band' => 'Meeting Expectation',   'min' => 58.0, 'max' => 74.99, 'descriptor' => 'Good', 'color' => '#2563eb'],
        ['code' => 'ME2', 'points' => 5, 'band' => 'Meeting Expectation',   'min' => 41.0, 'max' => 57.99, 'descriptor' => 'Satisfactory', 'color' => '#3b82f6'],
        ['code' => 'AE1', 'points' => 4, 'band' => 'Approaching Expectation', 'min' => 31.0, 'max' => 40.99, 'descriptor' => 'Needs Improvement', 'color' => '#d97706'],
        ['code' => 'AE2', 'points' => 3, 'band' => 'Approaching Expectation', 'min' => 21.0, 'max' => 30.99, 'descriptor' => 'Below Average', 'color' => '#f59e0b'],
        ['code' => 'BE1', 'points' => 2, 'band' => 'Below Expectation',   'min' => 11.0, 'max' => 20.99, 'descriptor' => 'Well Below Average', 'color' => '#dc2626'],
        ['code' => 'BE2', 'points' => 1, 'band' => 'Below Expectation',   'min' => 0.0,  'max' => 10.99, 'descriptor' => 'Minimal', 'color' => '#991b1b'],
    ];

    /**
     * Describe a percentage using the CBE 8-point achievement scale.
     *
     * @return array{code: string, points: int, band: string, descriptor: string, color: string}
     */
    public function describe(float $percentage): array
    {
        foreach (self::LEVELS as $level) {
            if ($percentage >= $level['min'] && $percentage <= $level['max']) {
                return [
                    'code'       => $level['code'],
                    'points'     => $level['points'],
                    'band'       => $level['band'],
                    'descriptor' => $level['descriptor'],
                    'color'      => $level['color'],
                ];
            }
        }

        return $this->describe(0);
    }

    /**
     * Overall performance description for a learner's mean percentage,
     * phrased as a full sentence suitable for a progress report.
     */
    public function overallBand(float $meanPercentage): array
    {
        $level = $this->describe($meanPercentage);

        return [
            'code'       => $level['code'],
            'points'     => $level['points'],
            'band'       => $level['band'],
            'descriptor' => $level['descriptor'],
            'color'      => $level['color'],
            'summary'    => sprintf('%s (%s) — %d points', $level['band'], $level['code'], $level['points']),
        ];
    }

    /**
     * Auto-generate a class teacher remark from the learner's mean
     * percentage and their weakest/strongest learning areas.
     *
     * @param float  $mean         Mean percentage
     * @param string|null $best    Name of best-performed learning area
     * @param string|null $worst   Name of weakest learning area
     */
    public function teacherRemark(float $mean, ?string $best = null, ?string $worst = null): string
    {
        $opening = match (true) {
            $mean >= 75 => 'An excellent performance this term.',
            $mean >= 58 => 'A good performance this term.',
            $mean >= 41 => 'A fair performance this term.',
            $mean >= 21 => 'Performance is below average this term.',
            default     => 'Performance needs serious improvement.',
        };

        $parts = [$opening];

        if ($best !== null && $mean < 75) {
            $parts[] = "Shows great promise in {$best}.";
        } elseif ($best !== null) {
            $parts[] = "Outstanding work in {$best} stands out.";
        }

        if ($worst !== null && $worst !== $best) {
            $parts[] = "Needs to put more effort in {$worst}.";
        }

        return implode(' ', $parts);
    }

    /**
     * Auto-generate a principal's remark from the mean percentage.
     */
    public function principalRemark(float $mean): string
    {
        return match (true) {
            $mean >= 75 => 'A disciplined and hardworking learner. Keep soaring high!',
            $mean >= 58 => 'Good results. With continued effort you will achieve even more.',
            $mean >= 41 => 'Satisfactory work. More effort is required next term.',
            $mean >= 21 => 'Below expectation. Parental support at home is encouraged.',
            default     => 'Must work much harder. Please see the class teacher.',
        };
    }
}
