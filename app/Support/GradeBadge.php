<?php

namespace App\Support;

/**
 * Maps a grade / achievement code to a Bootstrap badge class.
 *
 * Previously each view carried its own ad-hoc list, and
 * exam_results/index.blade.php recognised only the four short CBE codes
 * (EE/ME/AE/BE). The application actually stores the 8-point achievement codes
 * (EE1, EE2, ME1, ME2, AE1, AE2, BE1, BE2), so every one of them fell through
 * to the final else — a top-performing learner's EE1 rendered in red.
 */
class GradeBadge
{
    public static function for(?string $code): string
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return 'badge-secondary';
        }

        // CBE achievement levels: EE1/EE2, ME1/ME2, AE1/AE2, BE1/BE2, and the
        // short EE/ME/AE/BE forms used by the four-level competency rating.
        if (preg_match('/^(EE|ME|AE|BE)\d?$/', $code, $matches)) {
            return match ($matches[1]) {
                'EE' => 'badge-success',
                'ME' => 'badge-primary',
                'AE' => 'badge-warning',
                default => 'badge-danger',
            };
        }

        // KCSE letters: A+, A, A-, B+, B, B-, C+, C, C-, D+, D, D-, E, F.
        if (preg_match('/^([A-F])/', $code, $matches)) {
            return match ($matches[1]) {
                'A', 'B' => 'badge-success',
                'C' => 'badge-primary',
                'D' => 'badge-warning',
                default => 'badge-danger',
            };
        }

        return 'badge-secondary';
    }

    /**
     * A short human label for a code, for use in tables and report cards.
     */
    public static function label(?string $code): string
    {
        $code = strtoupper(trim((string) $code));

        return $code === '' ? '—' : $code;
    }
}
