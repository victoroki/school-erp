<?php

namespace Tests\Feature\Hostel;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Hostel;
use App\Models\HostelRoom;
use App\Models\Permission;
use App\Models\Role;
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
 * Reports, PDF exports and who is allowed to do what.
 */
class HostelReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Hostel $hostel;

    private AcademicYear $year;

    private SchoolClass $class;

    private Section $section;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->hostel = Hostel::create([
            'name' => 'Simba Boys Dormitory',
            'type' => 'boys',
            'address' => 'Main Campus',
            'capacity' => 60,
        ]);

        $this->year = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-11-20',
            'is_current' => true,
        ]);

        $this->class = SchoolClass::create(['name' => 'Form 3', 'numeric_value' => 3]);
        $this->section = Section::create(['name' => 'East', 'class_id' => $this->class->class_id]);

        ClassSection::create([
            'class_id' => $this->class->class_id,
            'section_id' => $this->section->section_id,
            'capacity' => 30,
        ]);
    }

    private function room(array $attributes = []): HostelRoom
    {
        return HostelRoom::create(array_merge([
            'hostel_id' => $this->hostel->hostel_id,
            'room_number' => 'B-' . (HostelRoom::count() + 1),
            'room_type' => 'double',
            'capacity' => 2,
            'floor' => 'First',
            'status' => HostelRoom::STATUS_AVAILABLE,
        ], $attributes));
    }

    /**
     * A student with a current class/stream enrolment, so the class and stream
     * filters have something to match.
     */
    private function enrolledStudent(string $firstName, string $gender = 'male'): Student
    {
        $student = Student::factory()->create([
            'first_name' => $firstName,
            'gender' => $gender,
        ]);

        StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => ClassSection::first()->class_section_id,
            'is_current' => true,
        ]);

        return $student;
    }

    private function allocate(Student $student, HostelRoom $room): void
    {
        $this->actingAs($this->admin)->post(route('hostel-allocations.store'), [
            'student_id' => $student->student_id,
            'hostel_id' => $this->hostel->hostel_id,
            'room_id' => $room->room_id,
            'allocation_date' => '2026-01-10',
            'academic_year_id' => $this->year->academic_year_id,
            'status' => 'active',
        ]);
    }

    // --------------------------------------------------------------- reports

    public function test_the_report_hub_renders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('hostel.reports'))
            ->assertStatus(200)
            ->assertSee('Hostel Reports');
    }

    public function test_the_dashboard_counts_beds_from_the_allocation_rows(): void
    {
        $room = $this->room(['capacity' => 4]);
        $this->allocate($this->enrolledStudent('Amina'), $room);

        $this->actingAs($this->admin)
            ->get(route('hostel.dashboard'))
            ->assertStatus(200)
            ->assertViewHas('stats', function (array $stats) {
                // One resident, one bed taken, three free — all from the same
                // source, so the tiles cannot disagree with each other.
                $this->assertSame(1, $stats['total_students']);
                $this->assertSame(1, $stats['total_occupied']);
                $this->assertSame(4, $stats['total_capacity']);
                $this->assertSame(3, $stats['vacant_beds']);

                return true;
            });
    }

    public function test_the_report_hub_warns_about_a_stale_declared_capacity(): void
    {
        // capacity 60 is declared on the hostel but the single 2-bed room
        // disagrees, which the hub has to surface rather than hide.
        $this->room(['capacity' => 2]);

        $this->actingAs($this->admin)
            ->get(route('hostel.reports'))
            ->assertStatus(200)
            ->assertSee('Simba Boys Dormitory')
            ->assertSee('Declared capacity is 60');
    }

    public function test_the_vacancy_report_shows_free_and_used_beds(): void
    {
        $room = $this->room(['capacity' => 4]);
        $this->allocate($this->enrolledStudent('Amina'), $room);
        $this->room(['capacity' => 2, 'room_number' => 'EMPTY-1']);

        $this->actingAs($this->admin)
            ->get(route('hostel.vacancy-report'))
            ->assertStatus(200)
            ->assertSee('Vacancy Report')
            ->assertSee($room->room_number)
            ->assertSee('EMPTY-1');
    }

    public function test_the_vacancy_report_has_an_empty_state(): void
    {
        $this->actingAs($this->admin)
            ->get(route('hostel.vacancy-report'))
            ->assertStatus(200)
            ->assertSee('No free beds to report');
    }

    public function test_the_student_list_shows_the_class_and_stream(): void
    {
        $this->allocate($this->enrolledStudent('Amina'), $this->room());

        $this->actingAs($this->admin)
            ->get(route('hostel.student-list'))
            ->assertStatus(200)
            ->assertSee('Amina')
            ->assertSee('Form 3 - East');
    }

    public function test_the_student_list_can_be_filtered_by_class(): void
    {
        $otherClass = SchoolClass::create(['name' => 'Form 4', 'numeric_value' => 4]);
        $otherSection = Section::create(['name' => 'West', 'class_id' => $otherClass->class_id]);
        $otherClassSection = ClassSection::create([
            'class_id' => $otherClass->class_id,
            'section_id' => $otherSection->section_id,
            'capacity' => 30,
        ]);

        $this->allocate($this->enrolledStudent('FormThree'), $this->room());

        $formFour = Student::factory()->create(['first_name' => 'FormFour', 'gender' => 'male']);
        StudentClassEnrollment::create([
            'student_id' => $formFour->student_id,
            'class_section_id' => $otherClassSection->class_section_id,
            'is_current' => true,
        ]);
        $this->allocate($formFour, $this->room());

        $this->actingAs($this->admin)
            ->get(route('hostel.student-list', ['class_id' => $this->class->class_id]))
            ->assertStatus(200)
            ->assertSee('FormThree')
            ->assertDontSee('FormFour');

        $this->actingAs($this->admin)
            ->get(route('hostel.student-list', ['class_id' => $otherClass->class_id]))
            ->assertStatus(200)
            ->assertSee('FormFour')
            ->assertDontSee('FormThree');
    }

    public function test_the_student_list_can_be_filtered_by_stream(): void
    {
        $this->allocate($this->enrolledStudent('Amina'), $this->room());

        $this->actingAs($this->admin)
            ->get(route('hostel.student-list', ['section_id' => $this->section->section_id]))
            ->assertStatus(200)
            ->assertSee('Amina');

        $otherSection = Section::create(['name' => 'North', 'class_id' => $this->class->class_id]);

        $this->actingAs($this->admin)
            ->get(route('hostel.student-list', ['section_id' => $otherSection->section_id]))
            ->assertStatus(200)
            ->assertDontSee('Amina');
    }

    public function test_the_student_list_ignores_a_students_old_class(): void
    {
        $oldClass = SchoolClass::create(['name' => 'Form 1', 'numeric_value' => 1]);
        $oldSection = Section::create(['name' => 'Old', 'class_id' => $oldClass->class_id]);
        $oldClassSection = ClassSection::create([
            'class_id' => $oldClass->class_id,
            'section_id' => $oldSection->section_id,
            'capacity' => 30,
        ]);

        $student = $this->enrolledStudent('Amina');

        StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => $oldClassSection->class_section_id,
            'is_current' => false,
        ]);

        $this->allocate($student, $this->room());

        // A historical Form 1 enrolment must not pull the student into a Form 1
        // report.
        $this->actingAs($this->admin)
            ->get(route('hostel.student-list', ['class_id' => $oldClass->class_id]))
            ->assertStatus(200)
            ->assertDontSee('Amina');
    }

    // --------------------------------------------------------------- exports

    public function test_the_student_list_pdf_downloads(): void
    {
        $this->allocate($this->enrolledStudent('Amina'), $this->room());

        $response = $this->actingAs($this->admin)->get(route('hostel.student-list.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_the_allocation_export_downloads(): void
    {
        $this->allocate($this->enrolledStudent('Amina'), $this->room());

        $response = $this->actingAs($this->admin)->get(route('hostel-allocations.export'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_the_vacancy_report_exports_a_pdf(): void
    {
        $this->actingAs($this->admin)
            ->get(route('hostel.vacancy-report.pdf'))
            ->assertStatus(200);
    }

    // ---------------------------------------------------------- authorization

    private function viewer(): User
    {
        $role = Role::create(['role_name' => 'Hostel Clerk', 'description' => 'Read only']);

        foreach (['hostel.view'] as $permissionName) {
            $permission = Permission::where('permission_name', $permissionName)->first();

            if ($permission) {
                $role->permissions()->attach($permission->permission_id);
            }
        }

        $user = User::factory()->create();
        $user->assignRole('Hostel Clerk');

        return $user;
    }

    public function test_a_viewer_can_read_the_hostel_pages(): void
    {
        $viewer = $this->viewer();

        $this->assertTrue($viewer->fresh()->hasPermission('hostel.view'), 'viewer should hold hostel.view');

        $this->withoutExceptionHandling();
        $this->actingAs($viewer)->get(route('hostel-allocations.index'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('hostel-rooms.index'))->assertStatus(200);
        $this->actingAs($viewer)->get(route('hostel.reports'))->assertStatus(200);
    }

    public function test_a_viewer_cannot_reach_the_write_screens(): void
    {
        $viewer = $this->viewer();
        $room = $this->room();

        $this->actingAs($viewer)->get(route('hostel-allocations.create'))->assertStatus(403);
        $this->actingAs($viewer)->get(route('hostel-allocations.bulk-form'))->assertStatus(403);
        $this->actingAs($viewer)->get(route('hostel-rooms.create'))->assertStatus(403);
    }

    public function test_a_viewer_cannot_allocate_bulk_transfer_or_check_out(): void
    {
        $viewer = $this->viewer();
        $room = $this->room();
        $student = $this->enrolledStudent('Amina');

        $this->allocate($student, $room);
        $allocation = \App\Models\HostelAllocation::where('student_id', $student->student_id)->firstOrFail();

        $this->actingAs($viewer)->post(route('hostel-allocations.store'), [
            'student_id' => $this->enrolledStudent('Blocked', 'male')->student_id,
            'hostel_id' => $this->hostel->hostel_id,
            'room_id' => $room->room_id,
            'allocation_date' => '2026-01-10',
        ])->assertStatus(403);

        $this->actingAs($viewer)->post(route('hostel-allocations.bulk-store'), [
            'student_ids' => [$student->student_id],
            'hostel_id' => $this->hostel->hostel_id,
            'room_id' => $room->room_id,
            'allocation_date' => '2026-01-10',
        ])->assertStatus(403);

        $this->actingAs($viewer)->post(route('hostel-allocations.transfer-store', $allocation->allocation_id), [
            'room_id' => $this->room()->room_id,
        ])->assertStatus(403);

        $this->actingAs($viewer)
            ->post(route('hostel-allocations.checkout', $allocation->allocation_id))
            ->assertStatus(403);
    }

    public function test_a_guest_is_sent_to_the_login_screen(): void
    {
        $this->get(route('hostel-allocations.index'))->assertRedirect(route('login'));
        $this->get(route('hostel.reports'))->assertRedirect(route('login'));
    }

    public function test_the_module_is_guarded_by_hostel_permissions(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('hostel-allocations.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('hostel-rooms.index'))->assertStatus(403);
    }
}
