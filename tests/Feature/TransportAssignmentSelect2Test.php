<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Student;
use App\Models\StudentTransportAssignment;
use App\Models\AcademicYear;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The two defects behind "the dropdowns on Assign Student to Transport look wrong".
 *
 * 1. layouts/app.blade.php loaded the Select2 **Bootstrap 5** theme stylesheet
 *    while every field was initialised with `theme: 'bootstrap4'`. The app is
 *    Bootstrap 4.6.2, so the wrong theme's metrics were being applied to every
 *    Select2 in the ERP — wrong height, wrong arrow, wrong dropdown panel.
 *
 * 2. The stop selects were refilled over AJAX without telling Select2, which keeps
 *    its own copy of the option list. The dropdowns opened still showing the old
 *    "Select Stop" placeholder over an empty search index.
 */
class TransportAssignmentSelect2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    public function test_the_loaded_select2_theme_matches_the_bootstrap_major_in_use(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        // Both spellings ship a Bootstrap-4 Select2 theme:
        // select2-bootstrap-4-theme and @ttskch/select2-bootstrap4-theme.
        preg_match('/select2-bootstrap-?(\d+)-?theme/', $layout, $stylesheet);
        preg_match_all("/theme:\s*'bootstrap(\d+)'/", $layout, $scripts);

        $this->assertNotEmpty($stylesheet, 'No Select2 theme stylesheet found in the layout.');
        $this->assertNotEmpty($scripts[1], 'No Select2 theme passed to .select2() in the layout.');

        $this->assertSame(
            $scripts[1],
            array_unique($scripts[1]),
            'The layout initialises Select2 with more than one theme.'
        );

        $this->assertSame(
            $stylesheet[1],
            $scripts[1][0],
            "The layout loads the Select2 Bootstrap {$stylesheet[1]} theme stylesheet but initialises fields with "
                ."theme: 'bootstrap{$scripts[1][0]}'. The CSS and the widget disagree, so every Select2 is styled wrong."
        );

        $this->assertSame('4', $stylesheet[1], 'This app is Bootstrap 4.6.2.');
    }

    public function test_refilling_a_select2_always_refreshes_the_widget(): void
    {
        $cascade = file_get_contents(
            resource_path('views/student_transport_assignments/_stop_cascade.blade.php')
        );

        // new Option(...) is XSS-safe; string-concatenated <option> HTML is not.
        $this->assertStringNotContainsString(".append('<option", $cascade);
        $this->assertStringNotContainsString(".append(\"<option", $cascade);
        $this->assertStringContainsString('new Option(', $cascade);

        $this->assertMatchesRegularExpression(
            '/function refresh\([^)]*\)\s*\{[^}]*trigger\(.change.\)/s',
            $cascade,
            'The cascade must refresh Select2 after replacing options, or the dropdown renders the stale placeholder.'
        );
    }

    public function test_the_transport_views_do_not_reinitialise_select2_that_the_layout_owns(): void
    {
        $offenders = [];

        // Scoped to the transport views. layouts/app.blade.php initialises every
        // .select2 on the page once, so a view doing it again double-initialises and
        // can override the theme and width the layout settled on.
        foreach (['student_transport_assignments', 'route_stops'] as $feature) {
            foreach (glob(resource_path('views/'.$feature.'/*.blade.php')) as $file) {
                $contents = file_get_contents($file);

                if (preg_match('/\$\(\s*[\'"]\.select2[\'"]\s*\)\s*\.\s*select2\s*\(/', $contents)) {
                    $offenders[] = $feature.'/'.basename($file);
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "These views initialise Select2 themselves, on top of the layout's:\n  ".implode("\n  ", $offenders)
        );
    }

    public function test_the_create_screen_renders_with_guarded_stop_selects(): void
    {
        $route = Route::create(['name' => 'North Loop', 'start_point' => 'School', 'end_point' => 'Town']);
        RouteStop::create(['route_id' => $route->route_id, 'stop_name' => 'Main Gate', 'sequence' => 1]);
        Student::firstOrCreate(
            ['student_id' => 1],
            [
                'first_name' => 'Ada',
                'last_name' => 'Byron',
                'admission_no' => 'ADM-1',
                'date_of_birth' => '2013-04-01',
                'gender' => 'female',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
                'admission_date' => '2026-01-01',
            ]
        );
        AcademicYear::firstOrCreate(['name' => '2026', 'academic_year_id' => 1, 'start_date' => '2026-01-01', 'end_date' => '2026-12-31']);

        $html = $this->actingAs($this->admin())->get(route('student-transport-assignments.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="route_select"', $html);
        $this->assertStringContainsString('id="pickup_stop_select"', $html);
        $this->assertStringContainsString('id="drop_stop_select"', $html);
        $this->assertStringContainsString('id="pickup_stop_help"', $html);
        $this->assertStringContainsString('id="drop_stop_help"', $html);

        $this->assertMatchesRegularExpression(
            '/id="pickup_stop_select"[^>]*disabled|disabled[^>]*id="pickup_stop_select"/',
            $html,
            'Stop selects should start disabled until a route is chosen.'
        );

        $this->assertStringContainsString('Select a pickup stop', $html);
        $this->assertStringContainsString('Select a drop stop', $html);

        // The cascade must be wired up, not the old inline copy.
        $this->assertStringContainsString('select2:ready', $html);

        // Built from the named route. @json() escapes slashes, so compare against the
        // same encoding rather than the raw URL.
        $this->assertStringContainsString(
            json_encode(route('api.routes.stops', ['__ROUTE_ID__'])),
            $html,
            'The cascade should build its request URL from the named route.'
        );
        $this->assertStringNotContainsString("'/api/routes/' + routeId", $html);
    }

    public function test_the_edit_screen_preselects_the_saved_stops(): void
    {
        $route = Route::create(['name' => 'North Loop', 'start_point' => 'School', 'end_point' => 'Town']);
        $pickup = RouteStop::create([
            'route_id' => $route->route_id, 'stop_name' => 'Main Gate', 'sequence' => 1, 'stop_time' => '07:30',
        ]);
        $drop = RouteStop::create([
            'route_id' => $route->route_id, 'stop_name' => 'City Park', 'sequence' => 2, 'stop_time' => '08:00',
        ]);

        $student = Student::firstOrCreate(
            ['student_id' => 1],
            [
                'first_name' => 'Ada',
                'last_name' => 'Byron',
                'admission_no' => 'ADM-1',
                'date_of_birth' => '2013-04-01',
                'gender' => 'female',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
                'admission_date' => '2026-01-01',
            ]
        );
        AcademicYear::firstOrCreate(['name' => '2026', 'academic_year_id' => 1, 'start_date' => '2026-01-01', 'end_date' => '2026-12-31']);

        $assignment = StudentTransportAssignment::create([
            'student_id' => $student->student_id,
            'route_id' => $route->route_id,
            'pickup_stop_id' => $pickup->stop_id,
            'drop_stop_id' => $drop->stop_id,
            'assigned_date' => '2026-01-05',
            'status' => 'active',
        ]);

        $html = $this->actingAs($this->admin())
            ->get(route('student-transport-assignments.edit', $assignment->assignment_id))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/value="'.$drop->stop_id.'" selected/',
            $html,
            'The saved drop stop must be preselected before the cascade refetches.'
        );
        $this->assertStringContainsString('Main Gate  (07:30)', $html);
    }

    public function test_the_stops_endpoint_returns_only_what_the_selects_render(): void
    {
        $route = Route::create(['name' => 'North Loop', 'start_point' => 'School', 'end_point' => 'Town']);
        RouteStop::create([
            'route_id' => $route->route_id,
            'stop_name' => 'Main Gate',
            'sequence' => 1,
            'stop_fee' => 25.5,
        ]);

        $payload = $this->actingAs($this->admin())
            ->getJson(route('api.routes.stops', $route->route_id))
            ->assertOk()
            ->json();

        $this->assertCount(1, $payload);
        $this->assertSame(
            ['stop_id', 'stop_name', 'stop_time'],
            array_keys($payload[0]),
            'The endpoint should not ship columns the dropdowns never render.'
        );
    }

    private function admin(): User
    {
        $admin = User::firstOrCreate(
            ['email' => 'transport.select2@example.test'],
            ['name' => 'Transport Admin', 'password' => bcrypt('secret')]
        );

        DB::table('user_roles')->insert([
            'user_id' => $admin->id,
            'role_id' => Role::where('role_name', 'Admin')->value('role_id'),
        ]);

        return $admin->fresh()->load('roles.permissions');
    }
}
