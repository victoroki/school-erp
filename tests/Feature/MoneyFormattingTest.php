<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentFeeAssignment;
use App\Models\User;
use App\Support\Money;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Money display.
 *
 * Two defects, both in fee screens:
 *
 * 1. Six views printed money with number_format($amount, 0) — or with no
 *    precision argument at all, which also defaults to 0. Every amount column in
 *    this schema is DECIMAL(12,2), so the cents existed and were being rounded
 *    away on arrears totals, refund figures and a revenue report.
 *
 * 2. The views used two currencies for the same shilling: 106 "KSh" against 125
 *    "KES". No single screen mixed them, so each page looked right on its own
 *    while moving between two fee screens changed the symbol. The backend had
 *    already chosen: app/ writes "KES" 15 times against "KSh" 4, and
 *    Student::getBalanceFeeAttribute() writes 'KES '. KES is the ISO 4217 code.
 */
class MoneyFormattingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    /** The six fee screens that rounded money to whole shillings. */
    private const AFFECTED_VIEWS = [
        'fee_management/arrears/index.blade.php',
        'fee_management/dashboard.blade.php',
        'fee_management/index.blade.php',
        'fee_management/refunds/index.blade.php',
        'fee_management/reports/discount_summary.blade.php',
        'fee_management/reports/expected_revenue.blade.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'money-format@test.local']);
        $this->admin->roles()->sync(Role::where('role_name', 'Super Admin')->pluck('role_id'));
        $this->admin->load('roles.permissions');
    }

    public function test_format_keeps_two_decimals_and_groups_thousands(): void
    {
        $this->assertSame('KES 1,234.56', Money::format(1234.56));
        $this->assertSame('KES 0.05', Money::format(0.05));
        $this->assertSame('KES 1,000,000.00', Money::format(1000000));
    }

    public function test_format_does_not_round_cents_away(): void
    {
        // The exact failure: number_format($x, 0) turned 1234.56 into 1,235.
        $this->assertStringContainsString('.56', Money::format(1234.56));
        $this->assertStringNotContainsString('1,235', Money::format(1234.56));
    }

    public function test_a_negative_amount_keeps_the_symbol_first(): void
    {
        // Discount rows previously rendered "-KSh 500.00".
        $this->assertSame('KES -500.00', Money::format(-500));
    }

    public function test_the_six_fee_views_no_longer_round_money_or_hardcode_another_currency(): void
    {
        foreach (self::AFFECTED_VIEWS as $view) {
            $path = resource_path('views/' . $view);

            $this->assertFileExists($path);

            $blade = file_get_contents($path);

            // No raw number_format() left in the money screens: all money goes
            // through the formatter so precision and symbol are decided once.
            preg_match_all('/number_format\(([^()]*(?:\([^()]*\)[^()]*)*)\)/', $blade, $matches);

            $this->assertSame(
                [],
                $matches[0],
                "{$view} still calls number_format() directly: " . implode(', ', $matches[0])
            );

            // "KSh" may only survive inside an explanatory comment. Strip comments
            // first rather than testing each line: a {{-- --}} block spans several
            // lines and only its first line carries the opening marker.
            $withoutComments = preg_replace([
                '/\{\{--.*?--\}\}/s',      // Blade comments, multi-line
                '/\/\*.*?\*\//s',          // block comments
                '/^[ \t]*\/\/.*$/m',       // line comments
            ], '', $blade);

            $this->assertStringNotContainsString(
                'KSh',
                $withoutComments,
                "{$view} still displays KSh outside a comment."
            );
        }
    }

    /**
     * The two invariants, checked across every view in the application rather
     * than a hand-maintained list — the fee screens covered above are simply the
     * ones that were wrong. Any future view that reintroduces either defect fails
     * here.
     */
    public function test_no_view_displays_the_wrong_currency_or_rounds_money(): void
    {
        $wrongSymbol = [];
        $roundedMoney = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $blade = file_get_contents($file->getPathname());

            $stripped = preg_replace([
                '/\{\{--.*?--\}\}/s',   // Blade comments, multi-line
                '/\/\*.*?\*\//s',
                '/^[ \t]*\/\/.*$/m',
            ], '', $blade);

            if (str_contains($stripped, 'KSh')) {
                $wrongSymbol[] = $relative;
            }

            foreach (explode("\n", $blade) as $number => $line) {
                if (! str_contains($line, 'number_format(')) {
                    continue;
                }

                // Money-labelled lines only: a currency symbol adjacent to the call.
                if (! preg_match('/KES\s*\{\{|\}\}\s*KES/', $line)) {
                    continue;
                }

                foreach ($this->numberFormatArgs($line) as $args) {
                    $topLevelCommas = $this->topLevelCommas($args);

                    if ($topLevelCommas === 0 || preg_match('/,\s*0\s*$/', $args)) {
                        $roundedMoney[] =
                            $relative . ':' . ($number + 1) . '  number_format(' . trim($args) . ')';
                    }
                }
            }
        }

        $this->assertSame([], $wrongSymbol, 'These views display KSh: ' . implode(', ', $wrongSymbol));
        $this->assertSame(
            [],
            $roundedMoney,
            'These money figures round away the cents: ' . implode(' | ', $roundedMoney)
        );
    }

    /**
     * Arguments of every number_format() call on a line, handling nesting.
     *
     * @return list<string>
     */
    private function numberFormatArgs(string $line): array
    {
        $found = [];
        $offset = 0;

        while (($pos = strpos($line, 'number_format(', $offset)) !== false) {
            $depth = 0;
            $end = null;

            for ($c = $pos + strlen('number_format(') - 1; $c < strlen($line); $c++) {
                if ($line[$c] === '(') {
                    $depth++;
                } elseif ($line[$c] === ')') {
                    $depth--;
                    if ($depth === 0) {
                        $end = $c;
                        break;
                    }
                }
            }

            if ($end === null) {
                break;
            }

            $found[] = substr($line, $pos + strlen('number_format('), $end - $pos - strlen('number_format('));
            $offset = $pos + 1;
        }

        return $found;
    }

    private function topLevelCommas(string $args): int
    {
        $depth = 0;
        $commas = 0;

        for ($c = 0; $c < strlen($args); $c++) {
            if ($args[$c] === '(') {
                $depth++;
            } elseif ($args[$c] === ')') {
                $depth--;
            } elseif ($args[$c] === ',' && $depth === 0) {
                $commas++;
            }
        }

        return $commas;
    }

    public function test_the_fee_management_screen_renders_kes_with_cents(): void
    {
        $year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'Grade 5', 'numeric_value' => 5]);
        $section = Section::create(['name' => 'A']);
        $classSection = ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);

        $category = FeeCategory::create(['name' => 'Tuition-' . uniqid(), 'type' => 'mandatory']);

        $structure = FeeStructure::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'category_id' => $category->category_id,
            'amount' => 1234.56,
            'term' => 'T1',
            'payment_frequency' => 'termly',
            'due_date' => '2026-02-01',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $student = Student::create([
            'admission_no' => 'MF' . substr(uniqid(), -8),
            'first_name' => 'Money',
            'last_name' => 'Formatter',
            'date_of_birth' => '2014-01-01',
            'gender' => 'female',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
        ]);

        StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => $classSection->class_section_id,
            'academic_year_id' => $year->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);

        StudentFeeAssignment::create([
            'student_id' => $student->student_id,
            'fee_structure_id' => $structure->fee_structure_id,
            'academic_year_id' => $year->academic_year_id,
            'term' => 'T1',
            'amount' => 1234.56,
            'final_amount' => 1234.56,
            'paid_amount' => 0,
            'assigned_date' => now(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('fee-management.index'))
            ->assertOk();

        // Symbol and cents on the row.
        $response->assertSee('KES 1,234.56');

        // The old rendering.
        $response->assertDontSee('1,235');
        $response->assertDontSee('KSh 1,234.56');
    }
}
