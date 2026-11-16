<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Homework;
use App\Models\Parents;
use App\Models\Period;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use App\Models\StudentFeeAssignment;
use App\Models\StudentParentRelationship;
use App\Models\Term;
use App\Models\Timetable;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE 4 — PARENT/STUDENT DATA ISOLATION (STEP 28/29/30).
 *
 * The non-negotiable rule: mobile navigation is NOT a security boundary —
 * Laravel owns authorization. Every endpoint that takes a student_id must
 * verify server-side that the caller is actually linked to that student.
 * These tests pin that down with the exact scenario from the brief:
 * Parent A changes the ID to Parent B's child and gets 403, across child
 * overview, attendance, timetable, fees, results and homework.
 */
class MobilePortalDataIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    // ── Fixtures ────────────────────────────────────────────────────────────

    private function portalUser(string $roleName, ?string $name = null): User
    {
        $user = User::factory()->create(['name' => $name ?? ($roleName . ' Tester')]);
        $user->roles()->sync(Role::where('role_name', $roleName)->pluck('role_id'));

        return $user->load('roles.permissions');
    }

    private function mobileToken(User $user): string
    {
        // Sanctum's guard caches the first resolved user per app instance;
        // multiple identities in one test method need a fresh resolution.
        $this->app['auth']->forgetGuards();

        return $user->createToken('mobile', ['mobile:access'])->plainTextToken;
    }

    private function year(): AcademicYear
    {
        return AcademicYear::firstOrCreate(
            ['name' => '2026'],
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true]
        );
    }

    /** @return array{year: AcademicYear, class: SchoolClass, cs: ClassSection} */
    private function section(string $className = 'Form 1', string $sectionName = 'A'): array
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

    private function makeStudent(string $first, array $ctx): Student
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

    private function parentOf(User $user): Parents
    {
        return Parents::create([
            'user_id' => $user->id,
            'first_name' => 'Grace',
            'last_name' => 'Tester',
            'relationship' => 'mother',
            'phone' => '0712345678',
        ]);
    }

    private function link(Parents $parent, Student $student, bool $primary = true): void
    {
        StudentParentRelationship::create([
            'student_id' => $student->student_id,
            'parent_id' => $parent->parent_id,
            'is_primary_contact' => $primary,
        ]);
    }

    /** Fee assignments for a student (all "Term 1", active). */
    private function assignFees(Student $student, float ...$amounts): void
    {
        foreach ($amounts as $i => $amount) {
            $structure = FeeStructure::create([
                'academic_year_id' => $this->year()->academic_year_id,
                'class_id' => $this->sectionOf($student)->class_id,
                'category_id' => FeeCategory::create(['name' => 'Cat-' . uniqid() . $i, 'type' => 'mandatory'])->category_id,
                'amount' => $amount,
                'term' => 'Term 1',
                'payment_frequency' => 'termly',
                'due_date' => now()->addDays(30),
                'status' => 'active',
            ]);
            StudentFeeAssignment::create([
                'student_id' => $student->student_id,
                'fee_structure_id' => $structure->fee_structure_id,
                'academic_year_id' => $this->year()->academic_year_id,
                'term' => 'Term 1',
                'amount' => $amount,
                'final_amount' => $amount,
                'paid_amount' => 0,
                'assigned_date' => now()->subDays(40),
                'status' => 'active',
            ]);
        }
    }

    private function sectionOf(Student $student): ?ClassSection
    {
        $enrollment = StudentClassEnrollment::where('student_id', $student->student_id)
            ->where('status', 'active')->first();

        return $enrollment?->classSection;
    }

    private function activeTerm(): Term
    {
        return Term::create([
            'academic_year_id' => $this->year()->academic_year_id,
            'name' => 'Term 1',
            'code' => 'T1',
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addDays(80)->toDateString(),
            'status' => 'active',
        ]);
    }

    // ── STEP 3/28: /child/{id} — the child overview is ownership-gated ──────

    public function test_parent_can_open_their_own_childs_overview(): void
    {
        $ctx = $this->section();
        $mine = $this->makeStudent('Alice', $ctx);
        $parent = $this->portalUser('Parent', 'Parent A');
        $this->link($this->parentOf($parent), $mine);

        $this->withToken($this->mobileToken($parent))
            ->getJson("/api/mobile/child/{$mine->student_id}")
            ->assertOk()
            ->assertJsonPath('student_id', $mine->student_id)
            ->assertJsonPath('name', 'Alice Doe')
            ->assertJsonPath('class', 'Form 1 A');
    }

    public function test_parent_a_cannot_open_parent_bs_child_by_changing_the_id(): void
    {
        $ctx = $this->section();
        $childA = $this->makeStudent('Alice', $ctx);
        $childB = $this->makeStudent('Bea', $ctx);

        $parentA = $this->portalUser('Parent', 'Parent A');
        $parentB = $this->portalUser('Parent', 'Parent B');
        $this->link($this->parentOf($parentA), $childA);
        $this->link($this->parentOf($parentB), $childB);

        $token = $this->mobileToken($parentA);

        $this->withToken($token)->getJson("/api/mobile/child/{$childA->student_id}")->assertOk();
        $this->withToken($token)->getJson("/api/mobile/child/{$childB->student_id}")->assertForbidden();
    }

    public function test_attendance_history_follows_the_same_ownership_rule_and_omits_staff_fields(): void
    {
        $ctx = $this->section();
        $childA = $this->makeStudent('Alice', $ctx);
        $childB = $this->makeStudent('Bea', $ctx);
        $parentA = $this->portalUser('Parent', 'Parent A');
        $parentB = $this->portalUser('Parent', 'Parent B');
        $this->link($this->parentOf($parentA), $childA);
        $this->link($this->parentOf($parentB), $childB);

        StudentAttendance::create([
            'student_id' => $childB->student_id,
            'class_section_id' => $ctx['cs']->class_section_id,
            'date' => now()->toDateString(),
            'status' => 'absent',
            'remarks' => 'Not in class',
        ]);

        $token = $this->mobileToken($parentA);
        $this->withToken($token)->getJson("/api/mobile/child/{$childB->student_id}/attendance")->assertForbidden();

        StudentAttendance::create([
            'student_id' => $childA->student_id,
            'class_section_id' => $ctx['cs']->class_section_id,
            'date' => now()->subDay()->toDateString(),
            'status' => 'present',
            'remarks' => 'On time',
        ]);
        $res = $this->withToken($token)->getJson("/api/mobile/child/{$childA->student_id}/attendance")
            ->assertOk()
            ->assertJsonPath('student_id', $childA->student_id)
            ->assertJsonCount(1, 'records');
        // Read-only projection: date/status/remark ONLY — never marked_by etc.
        $this->assertSame(['date', 'status', 'remark'], array_keys($res->json('records.0')));
    }

    // ── STEP 11: timetable for parents — explicit linked student_id only ────

    public function test_parent_timetable_requires_a_linked_student_id(): void
    {
        $ctx = $this->section();
        $childA = $this->makeStudent('Alice', $ctx);
        $childB = $this->makeStudent('Bea', $ctx);
        $parentA = $this->portalUser('Parent', 'Parent A');
        $parentB = $this->portalUser('Parent', 'Parent B');
        $this->link($this->parentOf($parentA), $childA);
        $this->link($this->parentOf($parentB), $childB);

        $token = $this->mobileToken($parentA);

        // No student_id at all → refuse rather than dump some default class.
        $this->withToken($token)->getJson('/api/mobile/timetable')->assertForbidden();
        // Someone else's child → refuse.
        $this->withToken($token)->getJson("/api/mobile/timetable?student_id={$childB->student_id}")->assertForbidden();
        $this->withToken($token)->getJson("/api/mobile/timetable/today?student_id={$childB->student_id}")->assertForbidden();
        // Own child → the child's section schedule.
        $this->withToken($token)->getJson("/api/mobile/timetable?student_id={$childA->student_id}")
            ->assertOk()->assertJsonPath('academic_year', '2026');
    }

    // ── STEP 5/6: fees — portal reads allowed for own scope only ────────────

    public function test_parent_fee_reads_cover_children_and_nothing_else(): void
    {
        $ctx = $this->section();
        $childA = $this->makeStudent('Alice', $ctx);
        $childB = $this->makeStudent('Bea', $ctx);
        $this->assignFees($childA, 10000, 6000);
        $this->assignFees($childB, 5000);

        $parentA = $this->portalUser('Parent', 'Parent A');
        $this->link($this->parentOf($parentA), $childA);

        $token = $this->mobileToken($parentA);

        // Summary: exactly one row — own child — over ALL active assignments.
        $res = $this->withToken($token)->getJson('/api/mobile/fees/summary')
            ->assertOk()->assertJsonCount(1);
        $this->assertSame($childA->student_id, (int) $res->json('0.student_id'));
        $this->assertSame(16000.0, (float) $res->json('0.total_assigned'));
        $this->assertSame(16000.0, (float) $res->json('0.balance'));

        $this->withToken($token)->getJson("/api/mobile/fees/student/{$childA->student_id}")->assertOk();
        $this->withToken($token)->getJson("/api/mobile/fees/student/{$childB->student_id}")->assertForbidden();
        $this->withToken($token)->getJson("/api/mobile/fees/student/{$childB->student_id}/history")->assertForbidden();
    }

    // ── STEP 10: results — cards carry student_id; no other student's data ──

    public function test_report_cards_carry_student_id_and_are_scoped_to_the_caller(): void
    {
        $ctx = $this->section();
        $childA = $this->makeStudent('Alice', $ctx);
        $childB = $this->makeStudent('Bea', $ctx);

        $exam = Exam::create([
            'name' => 'End of Term 1',
            'academic_year_id' => $this->year()->academic_year_id,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDays(4)->toDateString(),
            'publish_result' => true,
        ]);
        $subject = \App\Models\Subject::create(['subject_code' => 'MTH', 'name' => 'Mathematics', 'is_elective' => false]);
        foreach ([$childA, $childB] as $i => $s) {
            ExamResult::create([
                'exam_id' => $exam->exam_id,
                'student_id' => $s->student_id,
                'subject_id' => $subject->subject_id,
                'marks_obtained' => 90 - $i * 10,
            ]);
        }

        $parentA = $this->portalUser('Parent', 'Parent A');
        $this->link($this->parentOf($parentA), $childA);

        $res = $this->withToken($this->mobileToken($parentA))->getJson('/api/mobile/reports/report-cards')
            ->assertOk();
        // Parent A sees ONLY Alice's card — and every card carries the id so
        // the app filters by identity, never by name.
        $this->assertNotEmpty($res->json());
        foreach ($res->json() as $card) {
            $this->assertArrayHasKey('student_id', $card);
            $this->assertSame($childA->student_id, (int) $card['student_id']);
        }

        // A Student user sees only their own card.
        $studentUser = $this->portalUser('Student', 'Bea Doe');
        $childB->update(['user_id' => $studentUser->id]);
        $resB = $this->withToken($this->mobileToken($studentUser))->getJson('/api/mobile/reports/report-cards')->assertOk();
        foreach ($resB->json() as $card) {
            $this->assertSame($childB->student_id, (int) $card['student_id']);
        }
    }

    public function test_exam_roster_is_staff_only(): void
    {
        $ctx = $this->section();
        $childA = $this->makeStudent('Alice', $ctx);
        $exam = Exam::create([
            'name' => 'End of Term 1',
            'academic_year_id' => $this->year()->academic_year_id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
        ]);
        $schedule = ExamSchedule::create([
            'exam_id' => $exam->exam_id,
            'class_id' => $ctx['class']->class_id,
            'exam_date' => now()->addDays(3)->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'max_marks' => 100,
            'passing_marks' => 40,
        ]);

        // The roster names classmates — a mark-entry tool, not a portal view.
        $parentA = $this->portalUser('Parent', 'Parent A');
        $this->link($this->parentOf($parentA), $childA);
        $this->withToken($this->mobileToken($parentA))
            ->getJson("/api/mobile/exams/{$schedule->schedule_id}/students")->assertForbidden();

        $studentUser = $this->portalUser('Student', 'Alice Doe');
        $childA->update(['user_id' => $studentUser->id]);
        $this->withToken($this->mobileToken($studentUser))
            ->getJson("/api/mobile/exams/{$schedule->schedule_id}/students")->assertForbidden();

        // Staff behaviour unchanged: Admin still gets the roster.
        $admin = $this->portalUser('Admin');
        $admin->staff()->create([
            'first_name' => 'Ada', 'last_name' => 'Admin', 'date_of_birth' => '1980-01-01',
            'gender' => 'female', 'phone_primary' => '0712349999',
            'work_email' => 'admin.' . uniqid() . '@test.local', 'personal_email' => null,
            'current_address' => '', 'city' => '', 'country' => '',
            'employee_number' => null, 'tsc_number' => null, 'designation' => null,
            'qualification' => null, 'date_of_joining' => now()->toDateString(),
            'staff_type' => 'non-teaching', 'employment_type' => 'full_time', 'employment_status' => 'active',
        ]);
        $this->withToken($this->mobileToken($admin))
            ->getJson("/api/mobile/exams/{$schedule->schedule_id}/students")->assertOk();
    }

    // ── STEP 9/12: homework + notices stay scoped ───────────────────────────

    public function test_parent_homework_is_limited_to_the_childs_class(): void
    {
        $otherCtx = $this->section('Form 2', 'B');
        $ctx = $this->section();
        $childA = $this->makeStudent('Alice', $ctx);
        $childB = $this->makeStudent('Bea', $otherCtx);
        $parentA = $this->portalUser('Parent', 'Parent A');
        $this->link($this->parentOf($parentA), $childA);

        $teacher = $this->portalUser('Teacher');
        foreach ([
            ['title' => 'Algebra set', 'class' => 'Form 1 A'],      // child's class
            ['title' => 'Chem lab report', 'class' => 'Form 2 B'],  // another class
            ['title' => 'Reading week', 'class' => null],           // school-wide
        ] as $hw) {
            Homework::create([
                'created_by' => $teacher->id,
                'title' => $hw['title'],
                'description' => 'd',
                'subject' => 'Math',
                'class_name' => $hw['class'],
                'due_date' => now()->addDays(2)->toDateString(),
                'status' => 'active',
            ]);
        }

        $titles = collect($this->withToken($this->mobileToken($parentA))
            ->getJson('/api/mobile/homework')->assertOk()->json())
            ->pluck('title')->all();

        $this->assertContains('Algebra set', $titles);
        $this->assertContains('Reading week', $titles);
        $this->assertNotContains('Chem lab report', $titles);
    }

    // ── STEP 14–20: student dashboard is SELF-only and fixed ────────────────

    public function test_student_dashboard_ignores_any_device_supplied_student_id(): void
    {
        $ctx = $this->section();
        $self = $this->makeStudent('Alice', $ctx);
        $other = $this->makeStudent('Bea', $ctx);
        // Two active assignments — the old bug summed only one of them.
        $this->assignFees($self, 10000, 6000);

        $studentUser = $this->portalUser('Student', 'Alice Doe');
        $self->update(['user_id' => $studentUser->id]);

        $res = $this->withToken($this->mobileToken($studentUser))
            ->getJson("/api/mobile/dashboard?student_id={$other->student_id}")
            ->assertOk();

        $this->assertSame($self->student_id, (int) $res->json('student_id'));
        $this->assertSame(16000.0, (float) $res->json('fees.balance'));
        $this->assertSame('Pending', $res->json('fees.status'));
        $this->assertSame('Form 1 A', $res->json('current_class'));
        $this->assertArrayHasKey('attendance', $res->json());
        $this->assertArrayHasKey('today', $res->json());
        $this->assertArrayHasKey('upcoming_exams', $res->json());
    }

    public function test_student_dashboard_has_no_false_zeroes_without_term_or_timetable(): void
    {
        $ctx = $this->section();
        $self = $this->makeStudent('Alice', $ctx);
        $studentUser = $this->portalUser('Student', 'Alice Doe');
        $self->update(['user_id' => $studentUser->id]);

        $res = $this->withToken($this->mobileToken($studentUser))->getJson('/api/mobile/dashboard')->assertOk();
        // No active term → attendance summary is absent (null), not zero-filled.
        $this->assertNull($res->json('attendance.term'));
        $this->assertNull($res->json('attendance.today'));
        // No timetable rows → empty lesson list, no invented "next class".
        $this->assertSame([], $res->json('today.lessons'));
        $this->assertNull($res->json('next_class'));
        $this->assertNull($res->json('latest_result'));
    }

    public function test_next_lesson_comes_from_the_real_today_timetable(): void
    {
        $ctx = $this->section();
        $self = $this->makeStudent('Alice', $ctx);
        $studentUser = $this->portalUser('Student', 'Alice Doe');
        $self->update(['user_id' => $studentUser->id]);

        $subject = \App\Models\Subject::create(['subject_code' => 'BIO', 'name' => 'Biology', 'is_elective' => false]);
        $start = now()->addHour();
        // Guard the midnight-crossing edge: only meaningful while "after now" holds.
        if ($start->format('H:i:s') > now()->format('H:i:s')) {
            $period = Period::create([
                'name' => 'Later', 'start_time' => $start->format('H:i:s'),
                'end_time' => $start->addHour()->format('H:i:s'), 'type' => 'period',
            ]);
            Timetable::create([
                'class_section_id' => $ctx['cs']->class_section_id,
                'day_of_week' => strtolower(now()->format('l')),
                'period_id' => $period->period_id,
                'subject_id' => $subject->subject_id,
                'academic_year_id' => $this->year()->academic_year_id,
            ]);

            $res = $this->withToken($this->mobileToken($studentUser))->getJson('/api/mobile/dashboard')->assertOk();
            $this->assertSame('Biology', $res->json('next_class.subject'));
            $this->assertNotNull($res->json('attendance'));
        } else {
            $this->markTestSkipped('within one hour of midnight — next-lesson assertion is time-relative');
        }
    }

    public function test_parent_and_staff_cannot_hit_the_student_dashboard(): void
    {
        $ctx = $this->section();
        $childA = $this->makeStudent('Alice', $ctx);
        $parentA = $this->portalUser('Parent', 'Parent A');
        $this->link($this->parentOf($parentA), $childA);

        $this->withToken($this->mobileToken($parentA))->getJson('/api/mobile/dashboard')->assertForbidden();
        $this->withToken($this->mobileToken($this->portalUser('Accountant')))->getJson('/api/mobile/dashboard')->assertForbidden();
    }

    // ── STEP 2: parent dashboard rollup ─────────────────────────────────────

    public function test_parent_dashboard_returns_children_with_server_computed_overviews(): void
    {
        $ctx = $this->section();
        $this->activeTerm();
        $childA = $this->makeStudent('Alice', $ctx);
        $childB = $this->makeStudent('Bea', $ctx);
        $this->assignFees($childA, 10000);
        $this->assignFees($childB, 4000);
        StudentAttendance::create([
            'student_id' => $childA->student_id,
            'class_section_id' => $ctx['cs']->class_section_id,
            'date' => now()->toDateString(),
            'status' => 'late',
            'remarks' => 'Bus delay',
        ]);

        $parentA = $this->portalUser('Parent', 'Parent A');
        $p = $this->parentOf($parentA);
        $this->link($p, $childA);
        $this->link($p, $childB, false);

        $res = $this->withToken($this->mobileToken($parentA))->getJson('/api/mobile/parent/dashboard')->assertOk();

        $this->assertSame('Parent A', $res->json('parent_name'));
        $res->assertJsonCount(2, 'children');
        $res->assertJsonCount(2, 'child_overviews');
        $this->assertSame(14000.0, (float) $res->json('family_stats.total_balance_due'));
        $this->assertSame('Payment Pending', $res->json('family_stats.balance_status'));

        $overviewA = collect($res->json('child_overviews'))->firstWhere('student_id', $childA->student_id);
        $this->assertSame('late', $overviewA['attendance']['today']['status']);
        $this->assertSame('Bus delay', $overviewA['attendance']['today']['remark']);
        $this->assertSame('Term 1', $overviewA['attendance']['term']['term']);
        $this->assertSame(1, $overviewA['attendance']['term']['late']);
        $this->assertSame(10000.0, (float) $overviewA['fees']['balance']);
        $this->assertSame('Unpaid', $overviewA['fees']['status']);
        $this->assertSame('Term 1', $overviewA['fees']['current_term']['name']);
    }

    public function test_parent_dashboard_404s_without_a_parent_record(): void
    {
        $stranger = $this->portalUser('Parent');
        $this->withToken($this->mobileToken($stranger))
            ->getJson('/api/mobile/parent/dashboard')->assertNotFound();
    }

    public function test_parent_with_no_linked_children_gets_empty_lists_not_errors(): void
    {
        $lonely = $this->portalUser('Parent', 'No Kids');
        $this->parentOf($lonely);

        $res = $this->withToken($this->mobileToken($lonely))
            ->getJson('/api/mobile/parent/dashboard')->assertOk();
        $this->assertSame([], $res->json('children'));
        $this->assertSame([], $res->json('child_overviews'));
        $this->assertSame(0.0, (float) $res->json('family_stats.total_balance_due'));
        $this->assertSame('Clear', $res->json('family_stats.balance_status'));
    }

    public function test_multi_role_teacher_parent_gets_children_in_visible_scope(): void
    {
        $ctx = $this->section();
        $child = $this->makeStudent('Alice', $ctx);
        $teacherParent = $this->portalUser('Parent', 'Mr Teacher');
        $teacherParent->roles()->attach(Role::where('role_name', 'Teacher')->pluck('role_id'));
        $this->link($this->parentOf($teacherParent), $child);

        // A Teacher with no class assignments still sees their own child.
        $this->withToken($this->mobileToken($teacherParent))
            ->getJson("/api/mobile/child/{$child->student_id}")->assertOk();
    }

    // ── STEP 28: portal roles cannot WRITE (attendance/homework/fees/admin) ─

    public function test_portal_roles_cannot_write_staff_operations(): void
    {
        $ctx = $this->section();
        $childA = $this->makeStudent('Alice', $ctx);
        $parent = $this->portalUser('Parent', 'Parent A');
        $this->link($this->parentOf($parent), $childA);
        $studentUser = $this->portalUser('Student', 'Alice Doe');
        $childA->update(['user_id' => $studentUser->id]);

        $attendancePayload = ['records' => [['student_id' => $childA->student_id, 'status' => 'present']], 'date' => now()->toDateString()];

        foreach ([$parent, $studentUser] as $user) {
            $token = $this->mobileToken($user);
            $this->withToken($token)->postJson('/api/mobile/attendance', $attendancePayload)->assertForbidden();
            $this->withToken($token)->postJson('/api/mobile/fees/collect', [
                'student_id' => $childA->student_id, 'amount' => 500, 'method' => 'cash', 'client_uuid' => 'p4-' . $user->id,
            ])->assertForbidden();
            $this->withToken($token)->postJson('/api/mobile/homework', [
                'title' => 'x', 'subject' => 'Math', 'class_name' => 'Form 1 A', 'due_date' => now()->addDay()->toDateString(),
            ])->assertForbidden();
        }

        $this->assertSame(0, StudentAttendance::count());
        $this->assertSame(0, (int) StudentFeeAssignment::sum('paid_amount'));

        // No admin surface for portal roles.
        $this->withToken($this->mobileToken($parent))->getJson('/api/mobile/admin/dashboard')->assertForbidden();
        $this->withToken($this->mobileToken($studentUser))->getJson('/api/mobile/finance/summary')->assertForbidden();
    }

    // ── STEP 34: staff behaviour unchanged ──────────────────────────────────

    public function test_accountant_and_admin_still_read_all_students(): void
    {
        $ctx = $this->section();
        $childA = $this->makeStudent('Alice', $ctx);
        $childB = $this->makeStudent('Bea', $ctx);
        $this->assignFees($childA, 10000);
        $this->assignFees($childB, 5000);

        $accountant = $this->portalUser('Accountant');
        $accountant->staff()->create([
            'first_name' => 'Ada', 'last_name' => 'Accounts', 'date_of_birth' => '1980-01-01',
            'gender' => 'female', 'phone_primary' => '0712348888',
            'work_email' => 'acc.' . uniqid() . '@test.local', 'personal_email' => null,
            'current_address' => '', 'city' => '', 'country' => '',
            'employee_number' => null, 'tsc_number' => null, 'designation' => null,
            'qualification' => null, 'date_of_joining' => now()->toDateString(),
            'staff_type' => 'non-teaching', 'employment_type' => 'full_time', 'employment_status' => 'active',
        ]);

        // Accountant sees both children in summary and can open either overview.
        $token = $this->mobileToken($accountant);
        $this->withToken($token)->getJson('/api/mobile/fees/summary')->assertOk()->assertJsonCount(2);
        $this->withToken($token)->getJson("/api/mobile/child/{$childB->student_id}")->assertOk();
    }
}
