<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Pins workflow breakages found by crawling every screen and export:
 * routes into missing methods, exports that 500ed, a PHP file whose BOM
 * prefixed every response, and a mobile endpoint reading columns that do
 * not exist.
 */
class WorkflowRegressionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every registered controller action must exist. A missing one answers
     * 500 to everyone; the fix is to implement it or not register the route.
     */
    public function test_no_route_points_at_a_missing_controller_method(): void
    {
        $missing = [];

        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();
            if (! str_contains($action, '@')) {
                continue;
            }

            [$class, $method] = explode('@', $action);
            if (! class_exists($class) || ! method_exists($class, $method)) {
                $missing[] = implode('|', $route->methods()).' '.$route->uri().' -> '.$action;
            }
        }

        $this->assertSame([], $missing, "Routes pointing at missing methods:\n".implode("\n", $missing));
    }

    /**
     * A UTF-8 BOM before `<?php` in any file loaded per request is emitted
     * ahead of every response body (HTML, JSON, PDF, CSV).
     */
    public function test_no_bootstrapped_php_file_starts_with_a_byte_order_mark(): void
    {
        $offenders = [];

        foreach (['app', 'bootstrap', 'config', 'routes', 'database'] as $dir) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path($dir), \FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if ($file->getExtension() === 'php' && file_get_contents($file->getPathname(), false, null, 0, 3) === "\xEF\xBB\xBF") {
                    $offenders[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
                }
            }
        }

        $this->assertSame([], $offenders, 'These files start with a BOM, which is sent before every response.');
    }

    public function test_fee_status_report_and_both_exports_render(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('student-reports.fee-status'))->assertOk();

        $csv = $this->get(route('student-reports.fee-status.csv'));
        $csv->assertOk();
        $this->assertStringContainsString('text/csv', $csv->headers->get('Content-Type'));

        $pdf = $this->get(route('student-reports.fee-status.pdf'));
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('Content-Type'));
    }

    public function test_every_student_report_pdf_renders(): void
    {
        $this->actingAs($this->admin());

        foreach (['age-distribution', 'enrollment-trends', 'medical', 'transport-hostel'] as $report) {
            $response = $this->get(route("student-reports.{$report}.pdf"));
            $response->assertOk();
            $this->assertSame('application/pdf', $response->headers->get('Content-Type'), $report);
        }
    }

    public function test_hostel_allocation_form_opens_for_a_new_allocation(): void
    {
        $this->actingAs($this->admin())
            ->get(route('hostel-allocations.create'))
            ->assertOk();
    }

    public function test_fee_portal_refuses_an_account_with_no_learner_instead_of_crashing(): void
    {
        $this->actingAs($this->admin())
            ->get(route('portal.fees'))
            ->assertForbidden();
    }

    public function test_staff_transport_overview_on_mobile_reads_real_route_columns(): void
    {
        Sanctum::actingAs($this->admin(), ['mobile:access']);

        $this->getJson('/api/mobile/transport')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    private function admin(): User
    {
        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $user = User::firstOrCreate(
            ['email' => 'workflow.regression@example.test'],
            ['name' => 'Workflow Regression', 'password' => bcrypt('secret')]
        );
        $user->roles()->syncWithoutDetaching(Role::where('role_name', 'Admin')->firstOrFail());

        return $user->load('roles.permissions');
    }
}
