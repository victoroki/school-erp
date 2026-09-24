<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamType;
use App\Models\Parents;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentParentRelationship;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The web portal report-card page: ownership rules and the 500 that used to
 * stand in for them.
 *
 * `PortalReportCardController::index()` returned
 * `view('portal.report-cards', ['exams' => …, 'message' => …])` — without
 * `$student` — whenever no learner resolved, and the view reads
 * `$student->full_name` in its own header. Every account with no linked learner
 * therefore got "Undefined variable $student" instead of an answer, and because
 * the debug error page echoes request context it read like a data leak. It was
 * never one: the results query was always filtered by the resolved learner.
 *
 * These tests hold both halves: no 500, and no learner the viewer has no link to.
 */
class PortalReportCardIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicYear $year;

    protected Exam $exam;

    protected Student $child;

    protected Student $stranger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $this->child = $this->learner('Linked', 'Pupil');
        $this->stranger = $this->learner('Unlinked', 'Pupil');

        $examType = ExamType::create(['name' => 'End Term ' . uniqid()]);

        $this->exam = Exam::create([
            'name' => 'Termly Exam ' . uniqid(),
            'exam_type_id' => $examType->exam_type_id,
            'academic_year_id' => $this->year->academic_year_id,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->subDays(5)->toDateString(),
            'status' => 'completed',
        ]);

        $subject = Subject::create(['name' => 'Mathematics ' . uniqid(), 'subject_code' => 'M' . rand(100, 999)]);

        // A result for each learner, so a leak would be visible as a status or a name.
        ExamResult::create([
            'exam_id' => $this->exam->exam_id,
            'student_id' => $this->child->student_id,
            'subject_id' => $subject->subject_id,
            'marks_obtained' => 80,
        ]);
        ExamResult::create([
            'exam_id' => $this->exam->exam_id,
            'student_id' => $this->stranger->student_id,
            'subject_id' => $subject->subject_id,
            'marks_obtained' => 55,
        ]);
    }

    private function learner(string $first, string $last): Student
    {
        return Student::create([
            'admission_no' => 'PRC' . substr(uniqid(), -8),
            'first_name' => $first,
            'last_name' => $last,
            'date_of_birth' => '2014-01-01',
            'gender' => 'male',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    /** A portal user holding one seeded role. */
    private function portalUser(string $roleName): User
    {
        $user = User::factory()->create([
            'name' => $roleName . ' ' . uniqid(),
            'email' => strtolower($roleName) . '.' . uniqid() . '@test.local',
        ]);

        $user->roles()->sync([Role::where('role_name', $roleName)->firstOrFail()->role_id]);

        return $user->load('roles.permissions');
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

    private function link(Parents $parent, Student $student): void
    {
        StudentParentRelationship::create([
            'student_id' => $student->student_id,
            'parent_id' => $parent->parent_id,
            'is_primary_contact' => true,
        ]);
    }

    // ───────────────── the 500 ─────────────────

    public function test_the_portal_report_card_page_renders_for_a_student_account(): void
    {
        $user = $this->portalUser('Student');
        $this->child->update(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('portal.report-cards'))
            ->assertOk()
            ->assertSee($this->child->full_name, false);
    }

    public function test_the_portal_report_card_page_renders_for_a_linked_parent(): void
    {
        $parent = $this->parentOf($this->portalUser('Parent'));
        $this->link($parent, $this->child);

        $this->actingAs($parent->user)
            ->get(route('portal.report-cards'))
            ->assertOk()
            ->assertSee($this->child->full_name, false);
    }

    // ───────────────── ownership ─────────────────

    public function test_an_account_with_no_linked_learner_is_denied_rather_than_broken(): void
    {
        // Used to be the undefined-$student 500.
        $this->actingAs($this->portalUser('Parent'))
            ->get(route('portal.report-cards'))
            ->assertForbidden();

        // And an unrelated authenticated user with no portal role at all.
        $nobody = User::factory()->create([
            'name' => 'Unrelated ' . uniqid(),
            'email' => 'unrelated.' . uniqid() . '@test.local',
        ]);

        $this->actingAs($nobody)
            ->get(route('portal.report-cards'))
            ->assertForbidden();
    }

    public function test_a_parent_cannot_open_another_learners_report_card(): void
    {
        $parent = $this->parentOf($this->portalUser('Parent'));
        $this->link($parent, $this->child);

        // An exam the parent's own child did NOT sit, but the other learner did.
        // Asking for it must fail: the learner is resolved from the session, so
        // there is nothing for this parent to see.
        //
        // (Asking for the shared exam above is a different question and the
        // answer is 200 — that is the parent's own child's report card, which is
        // correct. Testing against the shared exam would have proved nothing.)
        $otherOnly = Exam::create([
            'name' => 'Other Class Exam ' . uniqid(),
            'exam_type_id' => $this->exam->exam_type_id,
            'academic_year_id' => $this->year->academic_year_id,
            'start_date' => now()->subDays(4)->toDateString(),
            'end_date' => now()->subDays(2)->toDateString(),
            'status' => 'completed',
        ]);

        ExamResult::create([
            'exam_id' => $otherOnly->exam_id,
            'student_id' => $this->stranger->student_id,
            'subject_id' => ExamResult::where('exam_id', $this->exam->exam_id)->first()->subject_id,
            'marks_obtained' => 61,
        ]);

        $response = $this->actingAs($parent->user)
            ->get(route('portal.report-cards.show', $otherOnly->exam_id));

        $this->assertNotSame(
            200,
            $response->getStatusCode(),
            'A parent reached a report card for a learner they are not linked to.'
        );
        $response->assertDontSee($this->stranger->admission_no, false);
        $response->assertDontSee($this->stranger->full_name, false);
    }

    public function test_a_student_account_can_open_its_own_report_card(): void
    {
        $user = $this->portalUser('Student');
        $this->child->update(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('portal.report-cards.show', $this->exam->exam_id))
            ->assertOk()
            ->assertSee($this->child->full_name, false);
    }

    public function test_a_student_account_cannot_swap_in_an_unrelated_learner(): void
    {
        $user = $this->portalUser('Student');
        $this->child->update(['user_id' => $user->id]);

        // No learner id is accepted from the request in the first place — the
        // learner always comes from the session. This pins that: whatever is
        // put in the URL, the other learner's data must not appear.
        $response = $this->actingAs($user)->get(
            route('portal.report-cards.show', $this->exam->exam_id) . '?student_id=' . $this->stranger->student_id
        );

        $response->assertDontSee($this->stranger->admission_no, false);
        $response->assertDontSee($this->stranger->full_name, false);

        // And a learner the viewer has no link to never appears in the index.
        $this->actingAs($user)
            ->get(route('portal.report-cards'))
            ->assertDontSee($this->stranger->full_name, false);
    }
}
