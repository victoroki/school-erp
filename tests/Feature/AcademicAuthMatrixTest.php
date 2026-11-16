<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The authorization matrix for Academic + Student Management, asserted from the
 * outside.
 *
 * The routes for these modules carry no permission middleware of their own —
 * everything is inside the controllers, as `$this->middleware('can:…')->only([…])`.
 * A method that is missing from an `only([…])` list therefore has NO gate at all,
 * and the only way to see that is to call it. These tests call it.
 *
 * Every user built here holds exactly the permissions named, so a 200 can only
 * mean the endpoint asked for nothing.
 */
class AcademicAuthMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicYear $yearA;

    protected AcademicYear $yearB;

    protected Term $termA;

    protected SchoolClass $class;

    protected ClassSection $classSectionA;

    protected ClassSection $classSectionB;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->yearA = AcademicYear::create([
            'name' => 'AY-A-' . uniqid(), 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true,
        ]);
        $this->yearB = AcademicYear::create([
            'name' => 'AY-B-' . uniqid(), 'start_date' => '2027-01-01', 'end_date' => '2027-12-31', 'is_current' => false,
        ]);

        $this->termA = Term::create([
            'academic_year_id' => $this->yearA->academic_year_id, 'name' => 'Term 1', 'code' => 'T1',
            'start_date' => '2026-01-05', 'end_date' => '2026-04-03', 'status' => 'active',
        ]);

        $this->class = SchoolClass::create(['name' => 'Grade 6', 'numeric_value' => 6]);
        $section = Section::create(['name' => 'A']);

        $this->classSectionA = ClassSection::create([
            'academic_year_id' => $this->yearA->academic_year_id,
            'class_id' => $this->class->class_id,
            'section_id' => $section->section_id,
        ]);
        $this->classSectionB = ClassSection::create([
            'academic_year_id' => $this->yearB->academic_year_id,
            'class_id' => $this->class->class_id,
            'section_id' => $section->section_id,
        ]);

        $this->student = Student::create([
            'admission_no' => 'AM' . substr(uniqid(), -8),
            'first_name' => 'Matrix', 'last_name' => 'Learner',
            'date_of_birth' => '2014-01-01', 'gender' => 'male',
            'city' => 'Nairobi', 'country' => 'Kenya',
            'admission_date' => now(), 'is_active' => true, 'status' => 'active',
        ]);

        StudentClassEnrollment::create([
            'student_id' => $this->student->student_id,
            'class_section_id' => $this->classSectionA->class_section_id,
            'academic_year_id' => $this->yearA->academic_year_id,
            'is_current' => true,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);
    }

    /**
     * A user holding exactly these permissions and nothing else.
     *
     * @param  array<int, string>  $permissions
     */
    private function userWith(array $permissions): User
    {
        // The role name and the user name both have to be unique: the RBAC
        // seeder already owns the real role names (Teacher, Parent, …) and
        // users.username is unique, so reusing either collides.
        $role = Role::create([
            'role_name' => 'Matrix Role ' . uniqid(),
            'description' => 'Isolation test role',
            'is_protected' => false,
            'is_hidden' => true,
        ]);

        $role->permissions()->sync(
            Permission::whereIn('permission_name', $permissions)->pluck('permission_id')
        );

        $user = User::factory()->create([
            'name' => 'Matrix User ' . uniqid(),
            'email' => 'matrix.' . uniqid() . '@test.local',
        ]);
        $user->roles()->sync([$role->role_id]);

        return $user->load('roles.permissions');
    }

    /**
     * Attach one of the roles the RBAC seeder already created, instead of a
     * synthetic one. This is what a real Teacher or Parent holds.
     */
    private function attachSeededRole(User $user, string $roleName): User
    {
        $role = Role::where('role_name', $roleName)->firstOrFail();

        $user->roles()->sync([$role->role_id]);

        return $user->load('roles.permissions');
    }

    /** A teacher with a staff record and NO class, subject or timetable assignment. */
    private function unassignedTeacher(): User
    {
        $user = $this->userWith(['academics.view', 'exams.results.view-own', 'exams.marks.enter-own'], 'Teacher');

        Staff::create([
            'user_id' => $user->id,
            'first_name' => 'Unassigned', 'middle_name' => null, 'last_name' => 'Teacher',
            'date_of_birth' => '1990-01-01', 'gender' => 'male',
            'phone_primary' => '0711999999',
            'work_email' => 'teacher.' . uniqid() . '@test.local',
            'personal_email' => null, 'current_address' => '', 'city' => '', 'country' => '',
            'employee_number' => null, 'tsc_number' => null, 'designation' => null,
            'qualification' => null, 'date_of_joining' => now()->toDateString(),
            'staff_type' => 'teaching', 'employment_type' => 'full_time', 'employment_status' => 'active',
        ]);

        return $user;
    }

    // ───────────────── 1. EVERY ACADEMIC/STUDENT ENDPOINT ASKS FOR SOMETHING ─────────────────

    /**
     * These two endpoints are reachable even though the controller that owns them
     * gates its sibling methods. A user with no academic permission at all must
     * not be able to read an attendance report or manage report-card templates.
     */
    public function test_the_attendance_report_requires_a_permission(): void
    {
        $nobody = $this->userWith([]);

        $route = Route::has('student-attendance.report') ? 'student-attendance.report' : null;
        $this->assertNotNull($route, 'The attendance report route should exist.');

        $response = $this->actingAs($nobody)->get(route($route, [
            'from_date' => now()->startOfMonth()->toDateString(),
            'to_date' => now()->endOfMonth()->toDateString(),
        ]));

        $this->assertContains(
            $response->getStatusCode(),
            [401, 403],
            'The attendance report returned ' . $response->getStatusCode()
                . ' to a user with no academic permissions. StudentAttendanceController gates index/show and '
                . 'store/update via only([...]) lists, so a method named in neither list — like report() — '
                . 'has no gate at all.'
        );
    }

    /**
     * Report-card templates are routed but not implemented.
     *
     * `ReportCardTemplateController` is an empty stub — the whole class body is
     * `//` — while `routes/web.php` registers a full `Route::resource` against it.
     * All seven routes therefore point at methods that do not exist and return
     * 500 for every user, whatever permissions they hold. Gating it would be the
     * wrong fix: there is nothing behind the gate yet.
     *
     * This pins that state so it is not mistaken for a permission bug, and fails
     * the moment someone implements the controller — at which point real coverage
     * (and the permission gate) is owed.
     */
    public function test_the_report_card_template_routes_point_at_an_empty_controller(): void
    {
        $class = \App\Http\Controllers\ReportCardTemplateController::class;

        $declared = array_filter(
            (new \ReflectionClass($class))->getMethods(),
            fn ($method) => $method->class === $class
        );

        $this->assertSame(
            [],
            array_values($declared),
            'ReportCardTemplateController now declares methods, so the report-card-template routes may work. '
                . 'Replace this test with real behaviour coverage and a permission gate.'
        );

        foreach (['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'] as $method) {
            $this->assertFalse(
                method_exists($class, $method),
                'routes/web.php registers Route::resource("report-card-templates") against a controller with no '
                    . $method . '() method, so that route returns 500 for everyone regardless of permission. '
                    . 'Implement the controller or remove the route.'
            );
        }
    }

    /**
     * A route that points at a method which does not exist answers 500 to
     * everybody, and no permission check can fix that. This scans the whole route
     * table so the next one is caught the day it is registered rather than in
     * production.
     */
    /**
     * Academic + Student routes must all resolve.
     *
     * The scan covers the whole route table, because the point is to catch a
     * newly-registered route that points nowhere, but only Academic/Student
     * controllers can fail this test. Library, Inventory and Finance currently
     * hold broken routes owned by other sessions; they are listed in the failure
     * message as context, never as a reason for this module to go red.
     */
    public function test_no_academic_or_student_route_points_at_a_missing_controller_method(): void
    {
        // Owned by this module: implement or remove. Nothing new may be added.
        $knownStubs = [\App\Http\Controllers\ReportCardTemplateController::class];

        $mine = [];
        $theirs = [];

        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();

            if (! str_contains($action, '@')) {
                continue;
            }

            [$class, $method] = explode('@', $action, 2);

            if (in_array($class, $knownStubs, true)) {
                continue;
            }

            if (class_exists($class) && method_exists($class, $method)) {
                continue;
            }

            $line = $route->uri() . ' -> ' . $action;

            if ($this->isAcademicOrStudentController($class)) {
                $mine[] = $line;
            } else {
                $theirs[] = $line;
            }
        }

        $context = $theirs === []
            ? ''
            : "\n\nReported, not failed — broken routes in other modules (other sessions):\n  "
                . implode("\n  ", $theirs);

        $this->assertSame(
            [],
            $mine,
            "Academic/Student routes point at controller methods that do not exist, so they return 500 for "
                . "every user:\n  " . implode("\n  ", $mine) . $context
        );
    }

    /** Does this controller belong to Academic or Student Management? */
    private function isAcademicOrStudentController(string $class): bool
    {
        $basename = class_basename($class);

        foreach ([
            'Student', 'Academic', 'Attendance', 'Exam', 'Mark', 'Grade', 'Cbc', 'Competency',
            'Class', 'Section', 'Subject', 'Teacher', 'Promotion', 'Transfer', 'Enrol', 'Enrollment',
            'ReportCard', 'Term', 'Strand', 'LearningArea', 'Period', 'Classroom', 'Guardian',
            'Parent', 'Document', 'Emergency', 'Timetable',
        ] as $marker) {
            if (str_contains($basename, $marker)) {
                return true;
            }
        }

        return false;
    }

    // ───────────────── 2. THE GATES THAT DO EXIST, VERIFIED ─────────────────

    public function test_attendance_capture_needs_the_attendance_permission_not_just_view(): void
    {
        $viewer = $this->userWith(['academics.view']);

        $this->actingAs($viewer)
            ->post(route('student-attendance.store'), [
                'class_section_id' => $this->classSectionA->class_section_id,
                'date' => now()->toDateString(),
                'attendance' => [],
            ])
            ->assertForbidden();

        $capturer = $this->userWith(['academics.attendance.manage']);

        $this->actingAs($capturer)
            ->post(route('student-attendance.store'), [
                'class_section_id' => $this->classSectionA->class_section_id,
                'date' => now()->toDateString(),
                'attendance' => [
                    $this->student->student_id => 'present',
                ],
            ])
            ->assertStatus(302);
    }

    public function test_marks_approval_needs_the_approval_permission(): void
    {
        $this->actingAs($this->userWith(['exams.marks.enter-own']))
            ->get(route('marks-approval.index'))
            ->assertForbidden();

        $this->actingAs($this->userWith(['exams.approve']))
            ->get(route('marks-approval.index'))
            ->assertOk();
    }

    public function test_bulk_report_card_export_needs_the_export_permission(): void
    {
        $this->actingAs($this->userWith(['exams.results.view-own']))
            ->get(route('exam-reports.bulk'))
            ->assertForbidden();

        // The permission is sufficient. With no filters supplied the page
        // redirects rather than rendering, which is correct behaviour and not a
        // denial — so this asserts the absence of a refusal, not a 200.
        // (The fully-filtered happy path needs an exam/class/subject fixture and
        // is not covered yet.)
        $this->actingAs($this->userWith(['exams.report-cards.export']))
            ->get(route('exam-reports.bulk'))
            ->assertStatus(302);
    }

    public function test_viewing_students_needs_students_view_and_editing_needs_manage(): void
    {
        $viewer = $this->userWith(['students.view']);

        $this->actingAs($viewer)->get(route('students.index'))->assertOk();
        $this->actingAs($viewer)->get(route('students.edit', $this->student->student_id))->assertForbidden();

        $manager = $this->userWith(['students.manage']);

        $this->actingAs($manager)
            ->put(route('students.update', $this->student->student_id), [
                'first_name' => 'Renamed', 'last_name' => 'Learner',
                'gender' => 'male', 'date_of_birth' => '2014-01-01',
                'admission_no' => $this->student->admission_no,
            ])
            ->assertStatus(302);
    }

    // ───────────────── 3. TEACHER SCOPE — DIRECT URL BYPASS ─────────────────

    /**
     * A teacher with no assignment owns no class, so each class-scoped screen
     * must refuse them regardless of the id in the URL.
     *
     * These are three separate tests on purpose: an earlier version asserted all
     * three inside one loop and reported only "an array contains 200", which said
     * a leak existed but not where. A security regression test has to name the
     * endpoint it is protecting.
     */
    public function test_a_teacher_with_no_class_cannot_open_mARK_SHEETSfor_another_class(): void
    {
        $this->assertClassScopedScreenRefusesAnUnassignedTeacher('mark-sheets.index');
    }

    public function test_a_teacher_with_no_class_cannot_open_the_grade_book_for_another_class(): void
    {
        $this->assertClassScopedScreenRefusesAnUnassignedTeacher('grade-book.index');
    }

    public function test_a_teacher_with_no_class_cannot_open_exam_results_for_another_class(): void
    {
        $this->assertClassScopedScreenRefusesAnUnassignedTeacher('exam-results.index');
    }

    /**
     * @param  string  $routeName  the class-scoped screen under test
     */
    private function assertClassScopedScreenRefusesAnUnassignedTeacher(string $routeName): void
    {
        if (! Route::has($routeName)) {
            $this->markTestSkipped($routeName . ' is not registered.');
        }

        $teacher = $this->unassignedTeacher();
        $sectionId = $this->classSectionA->class_section_id;

        $response = $this->actingAs($teacher)->get(route($routeName, ['class_section_id' => $sectionId]));

        $this->assertContains(
            $response->getStatusCode(),
            [403, 404],
            "{$routeName} returned " . $response->getStatusCode() . " to a teacher who has no class, subject or "
                . "timetable assignment, for class_section {$sectionId}. Owning nothing must mean seeing nothing: "
                . 'a 200 here — even with an empty table — exposes the class-scoped screen to any teacher.'
        );
    }

    public function test_a_teacher_with_no_class_cannot_post_attendance_for_another_class(): void
    {
        $teacher = $this->unassignedTeacher();

        $this->actingAs($teacher)
            ->post(route('student-attendance.store'), [
                'class_section_id' => $this->classSectionA->class_section_id,
                'date' => now()->toDateString(),
                'attendance' => [$this->student->student_id => 'absent'],
            ])
            ->assertForbidden();
    }

    // ───────────────── 4. PORTAL / PARENT IDOR ─────────────────

    /**
     * The portal group is behind `auth` only, so isolation is entirely the
     * controller's job. A user with no link to this learner must not receive
     * their data.
     */
    public function test_a_portal_user_cannot_read_another_students_report_card(): void
    {
        $stranger = $this->userWith([], 'Parent');

        if (! Route::has('portal.report-cards')) {
            $this->markTestSkipped('portal.report-cards is not registered.');
        }

        $response = $this->actingAs($stranger)->get(route('portal.report-cards'));

        $this->assertNotSame(200, $response->getStatusCode(), 'A portal user with no linked child reached the report-card index.');

        // And nothing about the learner leaks into whatever they did receive.
        $response->assertDontSee($this->student->admission_no, false);
    }

    // ───────────────── 5. PROMOTION ─────────────────

    private function promote(array $overrides = [])
    {
        return $this->actingAs($this->userWith(['students.manage']))
            ->post(route('student-promotion.store'), array_merge([
                'from_class_section_id' => $this->classSectionA->class_section_id,
                'to_class_section_id' => $this->classSectionB->class_section_id,
                'academic_year_id' => $this->yearB->academic_year_id,
                'student_ids' => [$this->student->student_id],
            ], $overrides));
    }

    public function test_promotion_moves_the_learner_and_preserves_the_old_enrollment(): void
    {
        $this->promote()->assertStatus(302);

        $this->assertSame(2, StudentClassEnrollment::where('student_id', $this->student->student_id)->count());

        $old = StudentClassEnrollment::where('student_id', $this->student->student_id)
            ->where('class_section_id', $this->classSectionA->class_section_id)->first();
        $new = StudentClassEnrollment::where('student_id', $this->student->student_id)
            ->where('class_section_id', $this->classSectionB->class_section_id)->first();

        $this->assertNotNull($new, 'A new enrollment must be created for the target class.');
        $this->assertFalse((bool) $old->is_current, 'The old enrollment must stop being current.');
        $this->assertSame('completed', $old->status);
        $this->assertTrue((bool) $new->is_current);
        $this->assertSame(
            1,
            StudentClassEnrollment::where('student_id', $this->student->student_id)->where('is_current', true)->count(),
            'Exactly one enrollment may be current.'
        );
    }

    public function test_a_crafted_post_cannot_promote_from_an_unrelated_class(): void
    {
        $other = SchoolClass::create(['name' => 'Grade 7', 'numeric_value' => 7]);
        $otherSection = Section::create(['name' => 'B']);
        $unrelated = ClassSection::create([
            'academic_year_id' => $this->yearA->academic_year_id,
            'class_id' => $other->class_id,
            'section_id' => $otherSection->section_id,
        ]);

        $response = $this->promote(['from_class_section_id' => $unrelated->class_section_id]);

        $this->assertSame(
            1,
            StudentClassEnrollment::where('student_id', $this->student->student_id)->count(),
            'from_class_section_id is validated as required but never used, so a POST naming an unrelated '
                . 'class still moved the learner (status ' . $response->getStatusCode() . ').'
        );
    }

    public function test_the_target_class_section_must_belong_to_the_target_year(): void
    {
        // Target year B, but the class-section belongs to year A.
        $response = $this->promote([
            'to_class_section_id' => $this->classSectionA->class_section_id,
        ]);

        $mismatched = StudentClassEnrollment::where('student_id', $this->student->student_id)
            ->where('academic_year_id', $this->yearB->academic_year_id)
            ->where('class_section_id', $this->classSectionA->class_section_id)
            ->count();

        $this->assertSame(
            0,
            $mismatched,
            'An enrollment was written whose academic_year disagrees with the class_section year '
                . '(status ' . $response->getStatusCode() . ').'
        );
    }

    public function test_promotion_is_idempotent(): void
    {
        $this->promote();
        $this->promote();

        $this->assertSame(
            2,
            StudentClassEnrollment::where('student_id', $this->student->student_id)->count(),
            'Re-submitting the same promotion created an extra enrollment: the second run deactivated the row '
                . 'the first run created and inserted another.'
        );
    }

    public function test_the_same_student_cannot_be_promoted_twice_in_one_submission(): void
    {
        $this->promote(['student_ids' => [$this->student->student_id, $this->student->student_id]]);

        $this->assertSame(
            2,
            StudentClassEnrollment::where('student_id', $this->student->student_id)->count(),
            'A duplicated student id in one request produced duplicate enrollments.'
        );
    }

    public function test_promotion_records_per_student_traceability(): void
    {
        $this->promote();

        $entry = \App\Models\AuditTrail::where('module', 'Student')->where('action', 'PROMOTE')->latest('id')->first();

        $this->assertNotNull($entry, 'Promotion must leave an audit entry.');
        $this->assertNotNull(
            $entry->record_id,
            'The audit entry has a null record_id and only a count, so which learner moved where is not '
                . 'reconstructible.'
        );
    }
}
