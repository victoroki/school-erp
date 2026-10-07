<?php

namespace Tests\Feature;

use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\Staff;
use App\Models\StaffAllowance;
use App\Models\StaffDeduction;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * POST /payroll-processing/calculate — the SHIF payroll breakdown.
 *
 * The page used to deduct NHIF: a flat KES 150-1,700 assessment table with no
 * relation to earnings, capped at 1,700 above KES 100,000 gross. The Social
 * Health Insurance Act 2023 replaced it with SHIF, administered by the Social
 * Health Authority, at 5% employee plus 5% employer of gross.
 */
class PayrollCalculationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');
    }

    private function staffWithSalary(string $employeeNumber, float $basic): Staff
    {
        // staff has twelve NOT NULL columns with no default, so a bare save()
        // fails on current_address/city/country unless they are supplied.
        $staff = new Staff([
            'employee_number' => $employeeNumber,
            'first_name' => 'Amina',
            'last_name' => 'Wanjiru',
            'date_of_birth' => '1990-04-17',
            'date_of_joining' => '2024-01-08',
            'gender' => 'female',
            'staff_type' => 'teaching',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'work_email' => strtolower($employeeNumber) . '@school.org',
            'phone_primary' => '0712345678',
            'current_address' => 'Kileleshwa, Nairobi',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'basic_salary' => $basic,
        ]);
        $staff->save();

        return $staff;
    }

    private function calculate(array $overrides = [])
    {
        return $this->actingAs($this->admin)->post(route('payroll-processing.calculate'), array_merge([
            'month' => 9,
            'year' => 2026,
        ], $overrides));
    }

    // ─── SHIF replaced NHIF ────────────────────────────────────────────────

    public function test_the_breakdown_shows_shif_and_no_longer_shows_nhif(): void
    {
        $this->staffWithSalary('T-5001', 100000);

        $response = $this->calculate();

        $response->assertStatus(200);
        $response->assertSee('SHIF');
        $response->assertDontSee('NHIF');
    }

    public function test_shif_is_charged_at_five_percent_of_gross(): void
    {
        $this->staffWithSalary('T-5002', 100000);

        $response = $this->calculate();

        // Asserted on the view data, not on rendered digits: at 100,000 gross
        // the employee and employer shares are both 5,000.00, so a string
        // match cannot tell the two columns apart.
        $response->assertViewHas('payrollData', function (array $rows): bool {
            $row = $rows[0];

            return $row['shif_employee'] === 5000.00
                && $row['shif_employer'] === 5000.00
                && $row['shif_employee'] + $row['shif_employer'] === 10000.00;
        });

        $response->assertSee('SHIF');
    }

    public function test_shif_keeps_rising_where_the_old_nhif_table_stopped(): void
    {
        // NHIF capped out at KES 1,700 a month above 100,000 gross. SHIF has no
        // ceiling, so a 300,000 salary is 15,000 rather than 1,700.
        $this->staffWithSalary('T-5003', 300000);

        $this->calculate()->assertViewHas('payrollData', function (array $rows): bool {
            return $rows[0]['shif_employee'] === 15000.00
                && $rows[0]['shif_employer'] === 15000.00;
        });
    }

    // ─── Employer cost is now visible ──────────────────────────────────────

    public function test_the_page_reports_the_employer_side_of_shif_and_nssf(): void
    {
        $this->staffWithSalary('T-5004', 100000);

        $response = $this->calculate();

        $response->assertViewHas('totals', function (array $totals): bool {
            // SHIF employer is uncapped at 5% of 100,000; NSSF employer is 6% of
            // its 36,000 ceiling, so the two are not the same calculation.
            return $totals['shif_employer'] === 5000.00
                && $totals['nssf_employer'] === 2160.00
                && $totals['statutory_employer'] === 7160.00;
        });

        $response->assertSee('Employer contributions on top of salaries');
        $response->assertSee('7,160.00');
    }

    // ─── The figures have to add up ────────────────────────────────────────

    public function test_net_pay_is_gross_less_deductions(): void
    {
        $this->staffWithSalary('T-5005', 100000);

        $this->calculate()
            ->assertViewHas('payrollData', function (array $rows): bool {
                $row = $rows[0];

                // 100,000 gross; PAYE 22,383.35 after the 2,400 relief, SHIF
                // 5,000, NSSF 2,160 capped at its 36,000 base.
                if ($row['paye'] !== 22383.35) {
                    return false;
                }

                $expectedDeductions = round(
                    $row['paye'] + $row['shif_employee'] + $row['nssf_employee'] + $row['other_deductions'],
                    2
                );

                return $row['total_deductions'] === $expectedDeductions
                    && $row['net_salary'] === round($row['gross_salary'] - $expectedDeductions, 2);
            });
    }

    public function test_allowances_and_staff_deductions_are_included(): void
    {
        $staff = $this->staffWithSalary('T-5006', 80000);

        $allowance = new StaffAllowance([
            'staff_id' => $staff->staff_id,
            'allowance_name' => 'House allowance',
            'amount' => 20000,
            'is_taxable' => true,
            'status' => 'active',
        ]);
        // allowance_type and start_date are NOT NULL with no default, and
        // allowance_type is not mass-assignable, so it is set directly.
        $allowance->allowance_type = 'fixed';
        $allowance->start_date = '2024-01-08';
        $allowance->save();

        $deduction = new StaffDeduction([
            'staff_id' => $staff->staff_id,
            'deduction_name' => 'Salary advance',
            'deduction_type' => 'advance',
            'monthly_amount' => 5000,
            'status' => 'active',
        ]);
        $deduction->start_date = '2024-01-08';
        $deduction->save();

        $this->calculate()
            ->assertViewHas('payrollData', function (array $rows): bool {
                $row = $rows[0];

                return $row['allowances'] === 20000.00
                    && $row['gross_salary'] === 100000.00   // 80,000 basic + 20,000
                    && $row['other_deductions'] === 5000.00;
            });
    }

    public function test_inactive_staff_are_left_out_of_the_run(): void
    {
        $this->staffWithSalary('T-5007', 100000);

        $exited = $this->staffWithSalary('T-5008', 200000);
        $exited->employment_status = 'exited';
        $exited->save();

        $response = $this->calculate();

        $response->assertSee('T-5007');
        $response->assertDontSee('T-5008');
    }

    // ─── The screen itself ─────────────────────────────────────────────────

    public function test_the_page_explains_the_rates_it_used(): void
    {
        $this->staffWithSalary('T-5009', 100000);

        $this->calculate()
            ->assertSee('Statutory basis in force')
            ->assertSee('personal relief')
            ->assertSee('1 June 2025');
    }

    public function test_the_page_names_the_period_that_was_calculated(): void
    {
        $this->staffWithSalary('T-5010', 100000);

        $this->calculate(['month' => 3, 'year' => 2026])
            ->assertSee('March 2026');
    }

    public function test_money_is_written_with_cents_rather_than_rounded_away(): void
    {
        // 33,333 gross puts SHIF at 1,666.65. The old NHIF table could only
        // produce whole shillings, so this figure needs the cents shown.
        $this->staffWithSalary('T-5011', 33333);

        $this->calculate()->assertSee('1,666.65');
    }

    public function test_the_page_does_not_claim_to_have_processed_the_payroll(): void
    {
        $this->staffWithSalary('T-5012', 100000);

        // finalize() opens a transaction, commits an empty body and flashes
        // "Payroll processed successfully", so staff appeared paid while no
        // payroll row was written. The page must not repeat that claim.
        $this->calculate()
            ->assertSee('nothing is written to the payroll ledger');
    }

    // ─── Edge cases ────────────────────────────────────────────────────────

    public function test_a_run_with_no_active_staff_renders_an_empty_state_not_an_error(): void
    {
        $response = $this->calculate();

        $response->assertStatus(200);
        $response->assertSee('No active staff to pay');
    }

    public function test_totals_across_several_employees_are_the_sum_of_the_rows(): void
    {
        $this->staffWithSalary('T-5013', 100000);
        $this->staffWithSalary('T-5014', 50000);

        $this->calculate()
            ->assertViewHas('totals', function (array $totals): bool {
                return $totals['staff'] === 2
                    && $totals['gross_salary'] === 150000.00
                    && $totals['shif_employee'] === 7500.00;   // 5% of each salary
            })
            ->assertSee('Totals (2 staff)');
    }

    public function test_the_period_is_validated(): void
    {
        $this->actingAs($this->admin)
            ->from(route('payroll-processing.index'))
            ->post(route('payroll-processing.calculate'), ['month' => 13, 'year' => 2026])
            ->assertSessionHasErrors('month');
    }

    public function test_the_review_step_redirects_instead_of_throwing_on_an_undefined_variable(): void
    {
        // The view renders $payrollData and $period, which only exist as locals
        // of calculate(), so /review/{id} used to 500.
        $this->actingAs($this->admin)
            ->get(route('payroll-processing.review', 1))
            ->assertRedirect(route('payroll-processing.create'));
    }

    public function test_finalize_no_longer_reports_a_success_it_never_achieved(): void
    {
        $this->actingAs($this->admin)
            ->post(route('payroll-processing.finalize', 1))
            ->assertRedirect(route('payroll-processing.index'))
            ->assertSessionHas('flash_notification');
    }

    // ─── Where a SHIF figure would be stored ───────────────────────────────

    public function test_shif_has_its_own_column_rather_than_being_written_to_nhif(): void
    {
        // The ledger has an nhif_deduction column. Writing a SHIF percentage
        // into it would report a statutory contribution under a retired name.
        $this->assertTrue(Schema::hasColumn('payroll_details', 'sha_deduction'));
        $this->assertTrue(Schema::hasColumn('payroll_details', 'nhif_deduction'));

        $this->assertContains('sha_deduction', (new PayrollDetail())->getFillable());
        $this->assertArrayHasKey('sha_deduction', (new PayrollDetail())->getCasts());
        $this->assertArrayHasKey('sha_deduction', (new Payroll())->getCasts());
    }
}
