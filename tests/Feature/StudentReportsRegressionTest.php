<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Classroom;
use App\Models\Route;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Student report regressions.
 *
 * Pins three reported defects:
 *  - Age Distribution fataled with "Call to a member function max() on array"
 *    because Blade called ->max() on a plain PHP array.
 *  - Transport & Hostel fataled with "Collection::with does not exist" because
 *    ->with(['route']) was chained after ->get().
 *  - Enrollment Trends rendered a blank page with no no-data handling.
 */
class StudentReportsRegressionTest extends TestCase
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

    private function makeStudent(array $overrides = []): Student
    {
        static $seq = 0;
        $seq++;

        // admission_no is varchar(15) in this schema.
        return Student::create(array_merge([
            'admission_no' => 'R' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT) . substr(uniqid(), -6),
            'first_name' => 'Report',
            'last_name' => 'Student' . $seq,
            'date_of_birth' => '2012-05-10',
            'gender' => 'female',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => '2026-01-10',
            'status' => 'active',
        ], $overrides));
    }

    private function enroll(Student $student): StudentClassEnrollment
    {
        static $seq = 0;
        $seq++;

        $class = SchoolClass::create([
            'name' => 'ReportForm ' . $seq . '-' . uniqid(),
            'numeric_value' => $seq,
        ]);
        $section = Section::create([
            'class_id' => $class->class_id,
            'name' => 'S' . $seq,
            'capacity' => 40,
        ]);

        $year = AcademicYear::first() ?: AcademicYear::create([
            'name' => 'AY-REPORT-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        return StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => ClassSection::create([
                'class_id' => $class->class_id,
                'section_id' => $section->section_id,
                'academic_year_id' => $year->academic_year_id,
                'classroom_id' => Classroom::create([
                    'room_number' => 'RR-' . $seq . '-' . substr(uniqid(), -4),
                    'building' => 'Block R',
                    'floor' => 1,
                    'capacity' => 40,
                ])->classroom_id,
            ])->class_section_id,
            'academic_year_id' => $year->academic_year_id,
            'is_current' => true,
            'enrollment_date' => '2026-01-10',
            'status' => 'active',
        ]);
    }

    // ─── Age distribution ────────────────────────────────────────────────

    public function test_age_distribution_renders_with_students(): void
    {
        $this->makeStudent(['date_of_birth' => '2015-03-01']);  // 5-7
        $this->makeStudent(['date_of_birth' => '2015-06-01']);  // 5-7
        $this->makeStudent(['date_of_birth' => '2010-01-01']);  // 14-16

        $response = $this->actingAs($this->admin)->get(route('student-reports.age-distribution'));

        $response->assertOk();
        // The largest group must be computed server-side and named correctly —
        // 5-7 holds two of the three students.
        $response->assertSee('5-7');
        $response->assertSee('Largest Age Group');
    }

    public function test_age_distribution_handles_an_empty_population(): void
    {
        $response = $this->actingAs($this->admin)->get(route('student-reports.age-distribution'));

        $response->assertOk();
        // Empty state instead of a fatal on max() of an empty array.
        $response->assertSee('Total Active Students');
    }

    public function test_age_distribution_names_the_largest_group_not_the_first(): void
    {
        // Deliberately make a later bucket the biggest one: the old
        // ->keys()->first() logic always reported the first bucket.
        $this->makeStudent(['date_of_birth' => '2000-01-01']);  // 20+
        $this->makeStudent(['date_of_birth' => '2001-01-01']);  // 20+
        $this->makeStudent(['date_of_birth' => '2002-01-01']);  // 20+
        $this->makeStudent(['date_of_birth' => '2015-01-01']);  // 5-7

        $view = $this->actingAs($this->admin)
            ->get(route('student-reports.age-distribution'))
            ->viewData('largestGroup');

        $this->assertSame('20+', $view);
    }

    public function test_age_distribution_csv_still_exports(): void
    {
        $this->makeStudent();

        $response = $this->actingAs($this->admin)->get(route('student-reports.age-distribution.csv'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    // ─── Transport & hostel ──────────────────────────────────────────────

    public function test_transport_hostel_report_renders_with_a_route_named(): void
    {
        $route = Route::create([
            'name' => 'Van Route ' . uniqid(),
            'start_point' => 'Town A',
            'end_point' => 'School',
        ]);

        $student = $this->makeStudent(['uses_transport' => true, 'route_id' => $route->route_id]);
        $this->enroll($student);

        $response = $this->actingAs($this->admin)->get(route('student-reports.transport-hostel'));

        $response->assertOk();
        $response->assertSee($route->name);
    }

    public function test_transport_hostel_report_counts_are_accurate(): void
    {
        $routed = $this->makeStudent(['uses_transport' => true]);
        $hostel = $this->makeStudent(['is_hosteller' => true]);
        $this->enroll($routed);
        $this->enroll($hostel);

        $data = $this->actingAs($this->admin)
            ->get(route('student-reports.transport-hostel'))
            ->viewData('totalTransport');

        $this->assertSame(1, $data);

        $data = $this->actingAs($this->admin)
            ->get(route('student-reports.transport-hostel'))
            ->viewData('totalHostel');

        $this->assertSame(1, $data);
    }

    public function test_transport_hostel_report_handles_no_data(): void
    {
        $response = $this->actingAs($this->admin)->get(route('student-reports.transport-hostel'));

        $response->assertOk();
    }

    // ─── Enrollment trends ───────────────────────────────────────────────

    public function test_enrollment_trends_counts_enrolled_students(): void
    {
        $year = AcademicYear::create([
            'name' => 'AY-TREND-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $student = $this->makeStudent();
        $class = SchoolClass::create(['name' => 'TrendForm-' . uniqid(), 'numeric_value' => 1]);
        $section = Section::create(['class_id' => $class->class_id, 'name' => 'T1', 'capacity' => 40]);

        StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => ClassSection::create([
                'class_id' => $class->class_id,
                'section_id' => $section->section_id,
                'academic_year_id' => $year->academic_year_id,
                'classroom_id' => Classroom::create([
                    'room_number' => 'TR-' . substr(uniqid(), -5),
                    'building' => 'B',
                    'floor' => 1,
                    'capacity' => 40,
                ])->classroom_id,
            ])->class_section_id,
            'academic_year_id' => $year->academic_year_id,
            'is_current' => true,
            'enrollment_date' => '2026-01-10',
            'status' => 'active',
        ]);

        $view = $this->actingAs($this->admin)
            ->get(route('student-reports.enrollment-trends'))
            ->viewData('trends');

        $row = $view->firstWhere('year_id', $year->academic_year_id);
        $this->assertNotNull($row);
        $this->assertSame(1, $row->total);
    }

    public function test_enrollment_trends_shows_an_empty_state_with_no_years(): void
    {
        AcademicYear::query()->update(['is_current' => false]);
        AcademicYear::query()->delete();

        $response = $this->actingAs($this->admin)->get(route('student-reports.enrollment-trends'));

        $response->assertOk();
        $response->assertSee('No enrollment records found');
    }

    // ─── Attendance summary ──────────────────────────────────────────────

    public function test_attendance_summary_shows_percentages(): void
    {
        $student = $this->makeStudent();

        Schema::getConnection()->table('student_attendance')->insert([
            ['student_id' => $student->student_id, 'date' => '2026-09-01', 'status' => 'present'],
            ['student_id' => $student->student_id, 'date' => '2026-09-02', 'status' => 'present'],
            ['student_id' => $student->student_id, 'date' => '2026-09-03', 'status' => 'absent'],
        ]);

        $response = $this->actingAs($this->admin)->get(route('student-reports.attendance'));

        $response->assertOk();
        // 2/3 present → 66.7%
        $response->assertSee('66.7%');
    }

    public function test_attendance_summary_handles_no_data(): void
    {
        $response = $this->actingAs($this->admin)->get(route('student-reports.attendance'));

        $response->assertOk();
        $response->assertSee('No attendance data recorded yet.');
    }
}
