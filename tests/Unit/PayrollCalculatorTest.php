<?php

namespace Tests\Unit;

use App\Services\PayrollCalculator;
use Tests\TestCase;

/**
 * The statutory payroll rates.
 *
 * These were private methods on PayrollProcessingController with no coverage, so
 * a wrong band or a swapped employer/employee share would only ever surface as a
 * payslip that did not add up.
 */
class PayrollCalculatorTest extends TestCase
{
    private PayrollCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new PayrollCalculator();
    }

    // ─── SHIF replaced NHIF on 1 October 2024 ──────────────────────────────

    public function test_shif_is_five_percent_each_of_gross_in_force_since_june_2025(): void
    {
        $this->assertSame(0.05, PayrollCalculator::SHIF_EMPLOYEE_RATE);
        $this->assertSame(0.05, PayrollCalculator::SHIF_EMPLOYER_RATE);
        $this->assertSame('2025-06-01', PayrollCalculator::SHIF_EFFECTIVE_FROM);
    }

    public function test_shif_scales_with_gross_where_nhif_did_not(): void
    {
        $low = $this->calculator->statutory(50000);
        $high = $this->calculator->statutory(200000);

        $this->assertSame(2500.00, $low['shif_employee']);
        $this->assertSame(2500.00, $low['shif_employer']);

        $this->assertSame(10000.00, $high['shif_employee']);
        $this->assertSame(10000.00, $high['shif_employer']);
    }

    public function test_shif_is_ten_percent_in_total_and_has_no_upper_cap(): void
    {
        // The retired NHIF assessment table stopped at KES 1,700 a month above
        // KES 100,000 gross. SHIF has no ceiling, so the figure keeps climbing.
        $result = $this->calculator->statutory(5000000);

        $this->assertSame(250000.00, $result['shif_employee']);
        $this->assertSame(250000.00, $result['shif_employer']);
        $this->assertSame(500000.00, $result['shif_total']);
    }

    public function test_shif_on_zero_income_is_zero(): void
    {
        $result = $this->calculator->statutory(0);

        $this->assertSame(0.0, $result['shif_employee']);
        $this->assertSame(0.0, $result['shif_employer']);
        $this->assertSame(0.0, $result['shif_total']);
    }

    public function test_shif_keeps_cents_rather_than_rounding_to_whole_shillings(): void
    {
        // 5% of 33,333 is 1,666.65. The old NHIF table could only ever produce
        // whole shillings, so this is the first figure on the payslip that
        // genuinely needs two decimals.
        $result = $this->calculator->statutory(33333);

        $this->assertSame(1666.65, $result['shif_employee']);
    }

    // ─── Employer cost is separate from what the employee loses ────────────

    public function test_employer_contributions_are_reported_apart_from_employee_deductions(): void
    {
        $result = $this->calculator->statutory(100000);

        // SHIF has no ceiling, so the employer share is 5% of the whole 100,000.
        $this->assertSame(5000.00, $result['shif_employer']);

        // NSSF is not: its base stops at 36,000, so 6% of 36,000.
        $this->assertSame(2160.00, $result['nssf_employer']);
        $this->assertSame(7160.00, $result['statutory_employer']);

        // Employer money is not part of the employee's deduction.
        $this->assertSame(
            $result['paye'] + $result['shif_employee'] + $result['nssf_employee'],
            $result['statutory_employee']
        );
    }

    // ─── NSSF is capped, SHIF is not ───────────────────────────────────────

    public function test_nssf_stops_at_the_tier_two_ceiling(): void
    {
        $atCap = $this->calculator->statutory(36000);
        $above = $this->calculator->statutory(900000);

        $this->assertSame(2160.00, $atCap['nssf_employee']);
        $this->assertSame(2160.00, $above['nssf_employee'], 'NSSF must not rise above the Tier II ceiling.');

        // SHIF, by contrast, keeps rising.
        $this->assertSame(45000.00, $above['shif_employee']);
    }

    public function test_nssf_ignores_income_above_the_cap_but_shif_does_not(): void
    {
        $atCap = $this->calculator->statutory(36000);
        $above = $this->calculator->statutory(36001);

        $this->assertSame($atCap['nssf_total'], $above['nssf_total']);
        $this->assertGreaterThan($atCap['shif_total'], $above['shif_total']);
    }

    // ─── PAYE bands ────────────────────────────────────────────────────────

    public function test_paye_below_the_first_band_is_fully_relieved(): void
    {
        // 10% of 24,000 is 2,400, exactly the monthly personal relief.
        $this->assertSame(0.00, $this->calculator->paye(24000));
        $this->assertSame(0.00, $this->calculator->paye(10000));
    }

    public function test_paye_enters_the_second_band_above_24000(): void
    {
        // 2,400 (first band) + 25% of 1,000 = 2,650, less 2,400 relief.
        $this->assertSame(250.00, $this->calculator->paye(25000));
    }

    public function test_paye_is_continuous_across_the_second_band_edge(): void
    {
        // 2,400 + 25% of 8,333 = 4,483.25, less the 2,400 relief.
        $below = $this->calculator->paye(32333);
        $above = $this->calculator->paye(32334);

        $this->assertSame(2083.25, $below);
        $this->assertSame(2083.55, $above);
        // A band edge must not create a jump larger than one band of tax.
        $this->assertLessThan(0.31, $above - $below);
    }

    public function test_paye_enters_the_third_band_above_32333(): void
    {
        // 2,400 + 2,083.25 + 30% of 1,000 = 4,783.25, less 2,400 relief.
        $this->assertSame(2383.25, $this->calculator->paye(33333));
    }

    public function test_paye_at_the_top_band_matches_a_hand_calculation(): void
    {
        // 2,400 + 2,083.25 + 140,300.10 + 97,500 = 242,283.35, plus 35% of
        // 200,000 = 70,000, giving 312,283.35 less the 2,400 relief.
        $this->assertSame(309883.35, $this->calculator->paye(1000000));
    }

    public function test_paye_never_returns_a_negative_amount(): void
    {
        $this->assertSame(0.00, $this->calculator->paye(0));
        $this->assertGreaterThanOrEqual(0, $this->calculator->paye(24001));
    }

    public function test_paye_never_dips_as_gross_rises(): void
    {
        // Below KES 24,000 the whole first band is cancelled by personal relief,
        // so PAYE is flat at zero there rather than strictly rising.
        $previous = -1.0;
        foreach ([0, 12000, 24000, 24001, 32333, 32334, 50000, 120000, 500001, 900000] as $gross) {
            $paye = $this->calculator->paye($gross);
            $this->assertGreaterThanOrEqual($previous, $paye, "PAYE dipped at gross {$gross}.");
            $previous = $paye;
        }

        $this->assertSame(0.00, $this->calculator->paye(12000));
        $this->assertSame(0.00, $this->calculator->paye(24000));

        // Once past the relief threshold it must climb at every step.
        $this->assertGreaterThan(0, $this->calculator->paye(24001));
        $this->assertGreaterThan($this->calculator->paye(24001), $this->calculator->paye(24002));
    }

    // ─── The page can explain itself ───────────────────────────────────────

    public function test_rates_summary_names_every_statutory_deduction(): void
    {
        $summary = $this->calculator->ratesSummary();

        $this->assertSame(['PAYE', 'SHIF', 'NSSF'], array_keys($summary));

        // It has to name SHIF, not NHIF: that is the whole point of the change.
        $this->assertStringNotContainsString('NHIF', implode(' ', array_keys($summary)));
        $this->assertStringNotContainsString('NHIF', implode(' ', $summary));

        $this->assertStringContainsString('employer', $summary['SHIF']);
        $this->assertStringContainsString('employee', $summary['SHIF']);
        $this->assertStringContainsString('2025', $summary['SHIF']);
    }
}
