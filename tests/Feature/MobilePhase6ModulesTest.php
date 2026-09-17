<?php

namespace Tests\Feature;

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\Book;
use App\Models\BookIssue;
use App\Models\ClassSection;
use App\Models\Hostel;
use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use App\Models\LibraryMember;
use App\Models\MedicalIncident;
use App\Models\Parents;
use App\Models\Role;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentParentRelationship;
use App\Models\Term;
use App\Models\TransportRegistration;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE 6 — REMAINING MODULES.
 *
 * Transport + Hostel: read-only real data, server-owned identity
 * (Student → own, Parent → linked ?student_id= child, foreign children
 * denied). Medical: explicit medical.view/manage permissions instead of the
 * students.* proxy. Library: current loans + return history, parent per-child
 * member read. Calendar: school events + current-year term dates.
 */
class MobilePhase6ModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    // ── Fixtures ────────────────────────────────────────────────────────────

    private function userWithRole(string $roleName, string $name = 'Tester'): User
    {
        $user = User::factory()->create(['name' => $name]);
        $user->roles()->sync(Role::where('role_name', $roleName)->pluck('role_id'));

        return $user->load('roles.permissions');
    }

    private function mobileToken(User|Student $account): string
    {
        $this->app['auth']->forgetGuards();
        $user = $account instanceof Student ? User::findOrFail($account->user_id) : $account;

        return $user->createToken('mobile', ['mobile:access'])->plainTextToken;
    }

    private function year(): AcademicYear
    {
        return AcademicYear::firstOrCreate(
            ['name' => '2026'],
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true]
        );
    }

    private function section(string $className = 'Form 4', string $sectionName = 'A'): array
    {
        $year = $this->year();
        $class = SchoolClass::create(['name' => $className, 'numeric_value' => 1]);
        $section = Section::create(['class_id' => $class->class_id, 'name' => $sectionName]);
        $cs = ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);

        return ['year' => $year, 'class' => $class, 'cs' => $cs];
    }

    private function makeStudent(string $first, array $ctx, ?User $user = null): Student
    {
        $student = Student::create([
            'admission_no' => 'ADM-' . substr(md5($first . uniqid('', true)), 0, 12),
            'first_name' => $first,
            'last_name' => 'Doe',
            'date_of_birth' => '2010-01-01',
            'gender' => 'female',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
            'user_id' => $user?->id,
        ]);
        StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => $ctx['cs']->class_section_id,
            'academic_year_id' => $ctx['year']->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);

        return $student;
    }

    private function parentOf(array $ctx, Student $student, string $name = 'Pat Parent'): array
    {
        $user = $this->userWithRole('Parent', $name);
        $parent = Parents::create([
            'user_id' => $user->id,
            'first_name' => 'Pat',
            'last_name' => 'Parent',
            'relationship' => 'mother',
            'phone' => '0711111111',
        ]);
        StudentParentRelationship::create([
            'student_id' => $student->student_id,
            'parent_id' => $parent->parent_id,
            'is_primary_contact' => true,
        ]);

        return ['user' => $user, 'parent' => $parent];
    }

    private function makeRoute(array $ctx, string $name = 'Westlands'): Route
    {
        return Route::create([
            'name' => $name,
            'route_code' => 'WST',
            'description' => 'Morning/evening route',
            'start_point' => 'School',
            'end_point' => 'Westlands',
            'vehicle_name' => 'Scania',
            'vehicle_number' => 'KDD 123A',
            'driver_name' => 'John Driver',
            'driver_contact' => '0712222222',
            'morning_start_time' => '06:30:00',
            'morning_end_time' => '07:45:00',
            'evening_start_time' => '16:30:00',
            'evening_end_time' => '17:45:00',
            'route_fee' => 4500,
            'status' => 'active',
            'academic_year_id' => $ctx['year']->academic_year_id,
        ]);
    }

    private function makeRouteRegistration(array $ctx, Student $student, Route $route): RouteStop
    {
        $stop = RouteStop::create([
            'route_id' => $route->route_id,
            'stop_name' => 'Westlands Mall',
            'stop_time' => '07:00:00',
            'sequence' => 1,
            'status' => 'active',
        ]);
        TransportRegistration::create([
            'student_id' => $student->student_id,
            'route_id' => $route->route_id,
            'stop_id' => $stop->stop_id,
            'fee_amount' => 4500,
            'payment_status' => 'paid',
            'academic_year_id' => $ctx['year']->academic_year_id,
        ]);

        return $stop;
    }

    private function makeHostelAllocation(array $ctx, Student $student): HostelAllocation
    {
        $hostel = Hostel::create(['name' => 'Green House', 'type' => 'girls', 'address' => 'Campus', 'capacity' => 40]);
        $room = HostelRoom::create([
            'hostel_id' => $hostel->hostel_id,
            'room_number' => 'B2',
            'room_type' => 'double',
            'capacity' => 2,
            'occupied' => 1,
            'floor' => '1',
            'status' => 'available',
        ]);

        return HostelAllocation::create([
            'student_id' => $student->student_id,
            'hostel_id' => $hostel->hostel_id,
            'room_id' => $room->room_id,
            'bed_number' => 1,
            'allocation_date' => now()->toDateString(),
            'status' => 'active',
            'academic_year_id' => $ctx['year']->academic_year_id,
        ]);
    }

    private function makeIncident(Student $student, User $marker, string $symptoms = 'Fever'): MedicalIncident
    {
        return MedicalIncident::create([
            'student_id' => $student->student_id,
            'incident_date' => now()->toDateString(),
            'symptoms' => $symptoms,
            'notified_parents' => true,
            'marked_by' => $marker->id,
        ]);
    }

    // ── Transport ───────────────────────────────────────────────────────────

    public function test_student_sees_own_transport_allocation(): void
    {
        $ctx = $this->section('Form 4', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $route = $this->makeRoute($ctx);
        $this->makeRouteRegistration($ctx, $student, $route);

        $this->withToken($this->mobileToken($student))
            ->getJson('/api/mobile/logistics/transport')
            ->assertStatus(200)
            ->assertJsonPath('route', 'Westlands')
            ->assertJsonPath('stop', 'Westlands Mall')
            ->assertJsonPath('stop_time', '07:00:00')
            ->assertJsonPath('fee', 4500)
            ->assertJsonPath('vehicle.number', 'KDD 123A')
            ->assertJsonPath('driver.name', 'John Driver')
            ->assertJsonPath('morning.start', '06:30:00');
    }

    public function test_parent_sees_linked_child_allocation_but_not_foreign_child(): void
    {
        $ctx = $this->section('Form 4', 'A');
        $myStudent = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $foreignStudent = $this->makeStudent('Zoe', $ctx, $this->userWithRole('Student', 'Zoe'));
        $route = $this->makeRoute($ctx);
        // Both children ride the same route — but only the linked child is visible.
        $this->makeRouteRegistration($ctx, $myStudent, $route);
        $this->makeRouteRegistration($ctx, $foreignStudent, $route);

        $parent = $this->parentOf($ctx, $myStudent, 'Pat A');

        $this->withToken($this->mobileToken($parent['user']))
            ->getJson('/api/mobile/logistics/transport?student_id=' . $myStudent->student_id)
            ->assertStatus(200)
            ->assertJsonPath('route', 'Westlands');

        // A child that is not yours — denied even though data exists.
        $this->withToken($this->mobileToken($parent['user']))
            ->getJson('/api/mobile/logistics/transport?student_id=' . $foreignStudent->student_id)
            ->assertStatus(403);
    }

    public function test_single_child_parent_resolves_without_student_id(): void
    {
        $ctx = $this->section('Form 4', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $route = $this->makeRoute($ctx);
        $this->makeRouteRegistration($ctx, $student, $route);
        $parent = $this->parentOf($ctx, $student, 'Pat');

        $this->withToken($this->mobileToken($parent['user']))
            ->getJson('/api/mobile/logistics/transport')
            ->assertStatus(200)
            ->assertJsonPath('route', 'Westlands');
    }

    public function test_transport_staff_needs_permission_and_scope(): void
    {
        $ctx = $this->section('Form 4', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $route = $this->makeRoute($ctx);
        $this->makeRouteRegistration($ctx, $student, $route);

        $accountant = $this->userWithRole('Accountant', 'Eve');
        $this->withToken($this->mobileToken($accountant))
            ->getJson('/api/mobile/logistics/transport?student_id=' . $student->student_id)
            ->assertStatus(403);
    }

    // ── Hostel ──────────────────────────────────────────────────────────────

    public function test_student_sees_own_hostel_allocation(): void
    {
        $ctx = $this->section('Form 4', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $allocation = $this->makeHostelAllocation($ctx, $student);

        $this->withToken($this->mobileToken($student))
            ->getJson('/api/mobile/logistics/hostel')
            ->assertStatus(200)
            ->assertJsonPath('hostel', 'Green House')
            ->assertJsonPath('hostel_type', 'girls')
            ->assertJsonPath('room', 'B2')
            ->assertJsonPath('room_type', 'double')
            ->assertJsonPath('bed', 1)
            ->assertJsonPath('date', $allocation->allocation_date->toDateString());
    }

    public function test_hostel_parent_scope_and_foreign_child_denied(): void
    {
        $ctx = $this->section('Form 4', 'A');
        $mine = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $foreign = $this->makeStudent('Zoe', $ctx, $this->userWithRole('Student', 'Zoe'));
        $this->makeHostelAllocation($ctx, $mine);
        $this->makeHostelAllocation($ctx, $foreign);

        $parent = $this->parentOf($ctx, $mine, 'Pat');

        $this->withToken($this->mobileToken($parent['user']))
            ->getJson('/api/mobile/logistics/hostel?student_id=' . $mine->student_id)
            ->assertStatus(200)
            ->assertJsonPath('hostel', 'Green House');

        $this->withToken($this->mobileToken($parent['user']))
            ->getJson('/api/mobile/logistics/hostel?student_id=' . $foreign->student_id)
            ->assertStatus(403);
    }

    public function test_no_allocation_is_an_honest_404(): void
    {
        $ctx = $this->section('Form 4', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));

        $this->withToken($this->mobileToken($student))
            ->getJson('/api/mobile/logistics/hostel')
            ->assertStatus(404);
    }

    // ── Medical RBAC ────────────────────────────────────────────────────────

    public function test_medical_requires_explicit_permission_not_role_fallthrough(): void
    {
        $ctx = $this->section('Form 4', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $admin = $this->userWithRole('Admin', 'Ada Admin');
        $this->makeIncident($student, $admin, 'Fever');

        // Admin holds medical.view → sees the log.
        $this->withToken($this->mobileToken($admin))
            ->getJson('/api/mobile/medical')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.symptoms', 'Fever');

        // Teacher has teaching permissions but NOT medical.view → 403.
        $teacher = $this->userWithRole('Teacher', 'Tom');
        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/medical')
            ->assertStatus(403);

        // Neither does the Accountant → 403.
        $accountant = $this->userWithRole('Accountant', 'Eve');
        $this->withToken($this->mobileToken($accountant))
            ->getJson('/api/mobile/medical')
            ->assertStatus(403);
    }

    // ── Library ─────────────────────────────────────────────────────────────

    public function test_library_my_books_includes_history_and_parent_can_read_child(): void
    {
        $ctx = $this->section('Form 4', 'A');
        $studentUser = $this->userWithRole('Student', 'Ada');
        $student = $this->makeStudent('Ada', $ctx, $studentUser);
        $book = Book::create([
            'title' => 'Physics Today',
            'author' => 'N. Kern',
            'quantity' => 3,
            'available_quantity' => 2,
            'added_date' => now()->toDateString(),
        ]);
        $member = LibraryMember::create([
            'user_id' => $studentUser->id,
            'member_type' => 'student',
            'reference_id' => $student->student_id,
            'membership_date' => now()->toDateString(),
            'status' => 'active',
            'max_allowed_books' => 3,
        ]);
        BookIssue::create([
            'book_id' => $book->book_id,
            'member_id' => $member->member_id,
            'issue_date' => now()->subDays(5)->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
            'status' => 'issued',
        ]);
        BookIssue::create([
            'book_id' => $book->book_id,
            'member_id' => $member->member_id,
            'issue_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDays(10)->toDateString(),
            'return_date' => now()->subDays(8)->toDateString(),
            'status' => 'returned',
        ]);

        $studentResponse = $this->withToken($this->mobileToken($studentUser))
            ->getJson('/api/mobile/library/my-books');
        $studentResponse->assertStatus(200)
            ->assertJsonPath('borrowed_books.0.book_title', 'Physics Today')
            ->assertJsonCount(1, 'history')
            ->assertJsonPath('history.0.status', 'returned');

        // Parent reads the same child-keyed view.
        $parent = $this->parentOf($ctx, $student, 'Pat');
        $parentResponse = $this->withToken($this->mobileToken($parent['user']))
            ->getJson('/api/mobile/library/my-books?student_id=' . $student->student_id);
        $parentResponse->assertStatus(200)
            ->assertJsonPath('borrowed_books.0.book_title', 'Physics Today');
    }

    public function test_library_missing_child_member_is_an_honest_404(): void
    {
        $ctx = $this->section('Form 4', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $parent = $this->parentOf($ctx, $student, 'Pat');

        $this->withToken($this->mobileToken($parent['user']))
            ->getJson('/api/mobile/library/my-books?student_id=' . $student->student_id)
            ->assertStatus(404);
    }

    // ── Calendar ────────────────────────────────────────────────────────────

    public function test_calendar_returns_events_and_term_dates(): void
    {
        $year = $this->year();
        AcademicEvent::create([
            'title' => 'Mid-Term Break',
            'event_type' => 'holiday',
            'start_date' => now()->addDays(10)->toDateString(),
            'is_public' => true,
            'academic_year_id' => $year->academic_year_id,
        ]);
        Term::create([
            'academic_year_id' => $year->academic_year_id,
            'name' => 'Term 1',
            'code' => 'T1',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => 'active',
            'display_order' => 1,
        ]);

        $user = $this->userWithRole('Student', 'Ada');
        $response = $this->withToken($this->mobileToken($user))
            ->getJson('/api/mobile/calendar');
        $response->assertStatus(200)
            ->assertJsonPath('academic_year', '2026')
            ->assertJsonPath('events.0.title', 'Mid-Term Break')
            ->assertJsonPath('terms.0.name', 'Term 1')
            ->assertJsonPath('terms.0.status', 'active');
    }

    public function test_calendar_without_current_year_is_an_honest_404(): void
    {
        $user = $this->userWithRole('Student', 'Ada');
        $this->withToken($this->mobileToken($user))
            ->getJson('/api/mobile/calendar')
            ->assertStatus(404)
            ->assertJsonPath('message', 'No current academic year set.');
    }
}