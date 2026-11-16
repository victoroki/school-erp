<?php

namespace App\Services;

/**
 * Kenyan statutory deductions for payroll.
 *
 * These were three private methods on PayrollProcessingController, so there was
 * nowhere to check a rate, nothing tested them, and the two payroll screens in
 * the project had no way to agree on a figure.
 *
 * SHIF replaced NHIF
 * ------------------
 * The National Health Insurance Fund was wound up and its role taken over by
 * the Social Health Authority (SHA), which administers the Social Health
 * Insurance Fund (SHIF) under the Social Health Insurance Act 2023. The Act
 * took effect on 1 October 2024.
 *
 * This is not only a relabelled column, and it changes every net figure:
 *
 *   - NHIF charged a flat assessment table of KES 150 to 1,700 a month whatever
 *     you earned, and stopped at 1,700 above KES 100,000 gross.
 *   - SHIF is a percentage of gross with no upper cap, split between employer
 *     and employee. It was phased in:
 *
 *         From          Employer   Employee   Total
 *         1 Oct 2024       1.5%        1.5%      3%
 *         1 Jan 2025       2.5%        2.5%      5%
 *         1 Jun 2025         5%          5%     10%
 *
 * The rates below are the current ones. On KES 180,000 gross that is KES 18,000
 * against NHIF's KES 1,700, so net pay falls considerably and last month's
 * figures will not reconcile against this month. That is the law changing, not
 * a mistake in the arithmetic.
 */
class PayrollCalculator
{
    /**
     * SHIF employee and employer shares of gross, in force from 1 June 2025.
     */
    public const SHIF_EMPLOYEE_RATE = 0.05;
    public const SHIF_EMPLOYER_RATE = 0.05;
    public const SHIF_EFFECTIVE_FROM = '2025-06-01';

    /**
     * NSSF Tier I contribution base, monthly.
     */
    public const NSSF_TIER_ONE_LIMIT = 7000;

    /**
     * NSSF Tier II upper bound, monthly. Contributions stop here, so NSSF is
     * capped however much someone earns.
     */
    public const NSSF_TIER_TWO_LIMIT = 36000;

    /**
     * NSSF employee and employer shares, each 6% of the capped base.
     */
    public const NSSF_EMPLOYEE_RATE = 0.06;
    public const NSSF_EMPLOYER_RATE = 0.06;

    /**
     * Monthly personal relief, deducted from PAYE.
     */
    public const PERSONAL_RELIEF = 2400;

    /**
     * Every statutory figure on a payslip, to the nearest shilling cent.
     *
     * Employer contributions are returned separately because they are the
     * employer's cost, not a reduction in what the employee takes home. The old
     * calculation had no notion of them at all, so the true cost of a salary
     * was invisible.
     *
     * @return array{
     *     gross: float, paye: float,
     *     shif_employee: float, shif_employer: float, shif_total: float,
     *     nssf_employee: float, nssf_employer: float, nssf_total: float,
     *     statutory_employee: float, statutory_employer: float
     * }
     */
    public function statutory(float $gross): array
    {
        $paye = $this->paye($gross);

        $shifEmployee = round($gross * self::SHIF_EMPLOYEE_RATE, 2);
        $shifEmployer = round($gross * self::SHIF_EMPLOYER_RATE, 2);

        $nssfBase = min($gross, self::NSSF_TIER_TWO_LIMIT);
        $nssfEmployee = round($nssfBase * self::NSSF_EMPLOYEE_RATE, 2);
        $nssfEmployer = round($nssfBase * self::NSSF_EMPLOYER_RATE, 2);

        return [
            'gross' => round($gross, 2),
            'paye' => $paye,
            'shif_employee' => $shifEmployee,
            'shif_employer' => $shifEmployer,
            'shif_total' => round($shifEmployee + $shifEmployer, 2),
            'nssf_employee' => $nssfEmployee,
            'nssf_employer' => $nssfEmployer,
            'nssf_total' => round($nssfEmployee + $nssfEmployer, 2),
            'statutory_employee' => round($paye + $shifEmployee + $nssfEmployee, 2),
            'statutory_employer' => round($shifEmployer + $nssfEmployer, 2),
        ];
    }

    /**
     * PAYE under the Finance Act 2024 rates, monthly.
     *
     * The band constants are each band's full width taxed at that band's rate,
     * accumulated up the scale. Personal relief is then subtracted, which
     * cancels the first band — that is why it is written as a separate step
     * rather than baked into the first band.
     */
    public function paye(float $gross): float
    {
        if ($gross <= 24000) {
            $paye = $gross * 0.10;
        } elseif ($gross <= 32333) {
            $paye = 2400 + (($gross - 24000) * 0.25);
        } elseif ($gross <= 500000) {
            $paye = 2400 + 2083.25 + (($gross - 32333) * 0.30);
        } elseif ($gross <= 800000) {
            $paye = 2400 + 2083.25 + 140300.10 + (($gross - 500000) * 0.325);
        } else {
            $paye = 2400 + 2083.25 + 140300.10 + 97500 + (($gross - 800000) * 0.35);
        }

        return round(max(0, $paye - self::PERSONAL_RELIEF), 2);
    }

    /**
     * A description of the rates in force, for showing on the payroll screen.
     *
     * Payroll is one of the places a school gets asked "where did this number
     * come from?", so the basis belongs next to the figures.
     *
     * @return array<string, string>
     */
    public function ratesSummary(): array
    {
        return [
            'PAYE' => 'First KES 24,000 at 10%, then rising bands to 35%. Less KES '
                . number_format(self::PERSONAL_RELIEF, 0) . ' monthly personal relief.',
            'SHIF' => sprintf(
                '%.1f%% employee + %.1f%% employer of gross, in force since %s.',
                self::SHIF_EMPLOYEE_RATE * 100,
                self::SHIF_EMPLOYER_RATE * 100,
                '1 June 2025'
            ),
            'NSSF' => sprintf(
                '%.0f%% employee + %.0f%% employer, on gross capped at KES %s.',
                self::NSSF_EMPLOYEE_RATE * 100,
                self::NSSF_EMPLOYER_RATE * 100,
                number_format(self::NSSF_TIER_TWO_LIMIT, 0)
            ),
        ];
    }
}
