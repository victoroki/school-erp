<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\Parents;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentParentRelationship;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * PHASE 6 — HOMEWORK SUBMISSIONS + TEACHER REVIEW.
 *
 * Pins the whole lifecycle with Laravel as the only authorization boundary:
 *  - the server resolves the student from the token (a device-sent
 *    student_id is ignored), class eligibility is enforced, one row per
 *    (homework, student) with replaceable resubmission.
 *  - lateness is server-derived from due_date; reviewed is locked.
 *  - teachers review only homework they created (admins with homework.manage
 *    may manage any); feedback persists; scores intentionally absent
 *    (homeworks has no max_marks concept).
 *  - parents read their own child's submission status; foreign children are
 *    denied.
 *  - attachments land on the private local disk and download only for the
 *    owner student / their parent / the reviewing teacher.
 */
class MobileHomeworkSubmissionTest extends TestCase
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

    private function section(string $className = 'Form 2', string $sectionName = 'A'): array
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

    private function scopedTeacher(string $name = 'Tariq Teacher', ?array $ctx = null): array
    {
        $ctx = $ctx ?? $this->section('Form 2', 'A');
        $user = $this->userWithRole('Teacher', $name);
        $staff = Staff::create([
            'user_id' => $user->id,
            'first_name' => 'Tariq',
            'last_name' => 'Tester',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone_primary' => '0712345678',
            'work_email' => 'tariq.' . uniqid() . '@test.local',
            'current_address' => '',
            'city' => '',
            'country' => '',
            'date_of_joining' => now()->toDateString(),
            'staff_type' => 'teaching',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
        ]);
        $ctx['cs']->update(['class_teacher_id' => $staff->staff_id]);

        return ['user' => $user->load('roles.permissions'), 'staff' => $staff, 'ctx' => $ctx];
    }

    /** Link a parent user to a student via Parents + the pivot. */
    private function linkParent(User $parentUser, Student $student, array $ctx): Parents
    {
        // parents.user_id is unique — reuse the row when one parent already
        // has several children.
        $parent = Parents::firstOrCreate(
            ['user_id' => $parentUser->id],
            [
                'first_name' => 'Pat',
                'last_name' => 'Parent',
                'relationship' => 'mother',
                'phone' => '0711111111',
            ]
        );
        StudentParentRelationship::firstOrCreate(
            ['student_id' => $student->student_id, 'parent_id' => $parent->parent_id],
            ['is_primary_contact' => true]
        );

        return $parent;
    }

    private function homework(int $createdBy, string $classLabel = 'Form 2 A', ?string $due = null): Homework
    {
        return Homework::create([
            'created_by' => $createdBy,
            'title' => 'Algebra Worksheet',
            'description' => 'Solve problems 1–20, show all working.',
            'subject' => 'Mathematics',
            'class_name' => $classLabel,
            'due_date' => $due ?: now()->addDays(3)->toDateString(),
            'status' => 'active',
        ]);
    }

    private function submitAs(User|Student $studentUser, int $homeworkId, array $payload = ['content' => 'My working.'])
    {
        return $this->withToken($this->mobileToken($studentUser))
            ->postJson("/api/mobile/homework/{$homeworkId}/submit", $payload);
    }

    private function reviewAs(User $teacherUser, int $submissionId, array $payload)
    {
        return $this->withToken($this->mobileToken($teacherUser))
            ->patchJson("/api/mobile/homework/submissions/{$submissionId}/review", $payload);
    }

    // ── Student submission ──────────────────────────────────────────────────

    public function test_assigned_student_can_submit(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $studentUser = $this->userWithRole('Student', 'Ada Student');
        $student = $this->makeStudent('Ada', $ctx, $studentUser);
        $other = $this->makeStudent('Zoe', $ctx); // same class, no account
        $teacher = $this->scopedTeacher('Tariq', $ctx);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        $response = $this->submitAs($studentUser, $hw->id, ['content' => 'Q1 solved.']);

        $response->assertStatus(200)
            ->assertJsonPath('data.student_id', $student->student_id)
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.content', 'Q1 solved.')
            ->assertJsonPath('data.homework_id', $hw->id);

        $this->assertDatabaseHas('homework_submissions', [
            'homework_id' => $hw->id,
            'student_id' => $student->student_id,
            'status' => 'submitted',
        ]);
    }

    public function test_student_outside_the_class_cannot_submit(): void
    {
        $ctxA = $this->section('Form 2', 'A');
        $ctxB = $this->section('Form 3', 'B');
        $studentA = $this->makeStudent('Ada', $ctxA, $this->userWithRole('Student', 'Ada'));
        $studentB = $this->makeStudent('Bo', $ctxB, $this->userWithRole('Student', 'Bo'));
        $teacher = $this->scopedTeacher('Tariq', $ctxA);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        $this->submitAs($studentB, $hw->id)->assertStatus(403);

        $this->assertDatabaseMissing('homework_submissions', [
            'homework_id' => $hw->id,
            'student_id' => $studentB->student_id,
        ]);
    }

    public function test_student_cannot_spoof_student_id(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $studentA = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $studentB = $this->makeStudent('Bo', $ctx, $this->userWithRole('Student', 'Bo'));
        $teacher = $this->scopedTeacher('Tariq', $ctx);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        // Device tries to reroute the submission to another student — ignored.
        $response = $this->submitAs($studentA, $hw->id, ['content' => 'Mine.', 'student_id' => $studentB->student_id]);

        $response->assertStatus(200)->assertJsonPath('data.student_id', $studentA->student_id);
        $this->assertDatabaseHas('homework_submissions', [
            'homework_id' => $hw->id,
            'student_id' => $studentA->student_id,
        ]);
        $this->assertDatabaseMissing('homework_submissions', [
            'homework_id' => $hw->id,
            'student_id' => $studentB->student_id,
        ]);
    }

    public function test_unknown_homework_is_rejected(): void
    {
        $studentUser = $this->userWithRole('Student', 'Ada');
        $ctx = $this->section('Form 2', 'A');
        $this->makeStudent('Ada', $ctx, $studentUser);

        $this->submitAs($studentUser, 999999, ['content' => 'x'])->assertStatus(404);
    }

    public function test_submitting_after_due_date_marks_late(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $teacher = $this->scopedTeacher('Tariq', $ctx);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A', now()->subDay()->toDateString());

        $this->submitAs($student, $hw->id, ['content' => 'Late but done.'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'late');
    }

    public function test_resubmission_upserts_one_row_and_reviewed_is_locked(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $teacher = $this->scopedTeacher('Tariq', $ctx);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        $first = $this->submitAs($student, $hw->id, ['content' => 'First attempt.']);
        $first->assertStatus(200);
        $submissionId = $first->json('data.id');

        $second = $this->submitAs($student, $hw->id, ['content' => 'Revised answer.']);
        $second->assertStatus(200)
            ->assertJsonPath('data.id', $submissionId)
            ->assertJsonPath('data.content', 'Revised answer.');
        $this->assertDatabaseCount('homework_submissions', 1);

        $this->reviewAs($teacher['user'], $submissionId, ['status' => 'reviewed', 'feedback' => 'Marked.'])
            ->assertStatus(200);

        // Reviewed is locked — no further edits.
        $this->submitAs($student, $hw->id, ['content' => 'Change after review.'])
            ->assertStatus(422);
    }

    public function test_returned_invites_resubmission(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $teacher = $this->scopedTeacher('Tariq', $ctx);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        $first = $this->submitAs($student, $hw->id, ['content' => 'First attempt.']);
        $submissionId = $first->json('data.id');

        $this->reviewAs($teacher['user'], $submissionId, ['status' => 'returned', 'feedback' => 'Redo Q2.'])
            ->assertStatus(200);

        $this->submitAs($student, $hw->id, ['content' => 'Redone.'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'submitted');
        // Same row, status back to submitted, old review stamps cleared.
        $this->assertDatabaseCount('homework_submissions', 1);
    }

    // ── Teacher review ──────────────────────────────────────────────────────

    public function test_authorized_teacher_can_review_and_feedback_persists(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $this->makeStudent('Zoe', $ctx); // enrolled, hasn't submitted → missing
        $teacher = $this->scopedTeacher('Tariq', $ctx);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        $this->submitAs($student, $hw->id, ['content' => 'My working.']);
        $submissionId = HomeworkSubmission::first()->id;

        // Teacher list shows real aggregates before review.
        $list = $this->withToken($this->mobileToken($teacher['user']))->getJson('/api/mobile/homework');
        $list->assertStatus(200);
        $row = collect($list->json())->firstWhere('id', $hw->id);
        $this->assertEquals(1, $row['submission_counts']['submitted']);
        $this->assertEquals(0, $row['submission_counts']['reviewed']);
        // roster: 2 enrollments in "Form 2 A", 1 submitted → 1 missing.
        $this->assertEquals(2, $row['submission_counts']['assigned']);
        $this->assertEquals(1, $row['submission_counts']['missing']);

        $this->reviewAs($teacher['user'], $submissionId, ['status' => 'reviewed', 'feedback' => 'Well done.'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'reviewed')
            ->assertJsonPath('data.teacher_feedback', 'Well done.');

        $feed = $this->withToken($this->mobileToken($teacher['user']))
            ->getJson("/api/mobile/homework/{$hw->id}/submissions");
        $feed->assertStatus(200)
            ->assertJsonPath('counts.reviewed', 1)
            ->assertJsonPath('submissions.0.status', 'reviewed')
            ->assertJsonPath('submissions.0.teacher_feedback', 'Well done.')
            ->assertJsonPath('submissions.0.student_name', 'Ada Doe');
    }

    public function test_unrelated_teacher_cannot_review_or_list(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $teacherA = $this->scopedTeacher('Tariq', $ctx);
        $teacherB = $this->scopedTeacher('Ben', $ctx);
        $hw = $this->homework($teacherA['user']->id, 'Form 2 A');

        $this->submitAs($student, $hw->id, ['content' => 'x']);
        $submissionId = HomeworkSubmission::first()->id;

        $this->withToken($this->mobileToken($teacherB['user']))
            ->getJson("/api/mobile/homework/{$hw->id}/submissions")
            ->assertStatus(403);

        $this->reviewAs($teacherB['user'], $submissionId, ['status' => 'reviewed'])
            ->assertStatus(403);

        $this->assertDatabaseHas('homework_submissions', ['id' => $submissionId, 'status' => 'submitted']);
    }

    public function test_student_cannot_list_or_detail_review_data(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $teacher = $this->scopedTeacher('Tariq', $ctx);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        $this->withToken($this->mobileToken($student))
            ->getJson("/api/mobile/homework/{$hw->id}/submissions")
            ->assertStatus(403);
    }

    // ── Parent visibility ───────────────────────────────────────────────────

    public function test_parent_sees_own_child_submission_status(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $studentUser = $this->userWithRole('Student', 'Ada');
        $student = $this->makeStudent('Ada', $ctx, $studentUser);
        $parentUser = $this->userWithRole('Parent', 'Pat Parent');
        $this->linkParent($parentUser, $student, $ctx);
        $teacher = $this->scopedTeacher('Tariq', $ctx);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        // Not yet submitted → my_submission null, status honest.
        $this->withToken($this->mobileToken($parentUser))
            ->getJson("/api/mobile/homework/{$hw->id}?student_id={$student->student_id}")
            ->assertStatus(200)
            ->assertJsonPath('my_submission', null);

        $this->submitAs($studentUser, $hw->id, ['content' => 'Child answer.']);
        $this->reviewAs($teacher['user'], HomeworkSubmission::first()->id, ['status' => 'reviewed', 'feedback' => 'Great.']);

        $this->withToken($this->mobileToken($parentUser))
            ->getJson("/api/mobile/homework/{$hw->id}?student_id={$student->student_id}")
            ->assertStatus(200)
            ->assertJsonPath('my_submission.status', 'reviewed')
            ->assertJsonPath('my_submission.teacher_feedback', 'Great.');
    }

    public function test_parent_cannot_read_foreign_child_or_wrong_student_id(): void
    {
        $ctxA = $this->section('Form 2', 'A');
        $ctxB = $this->section('Form 3', 'B');
        $childA = $this->makeStudent('Ada', $ctxA, $this->userWithRole('Student', 'Ada'));
        $childB = $this->makeStudent('Bo', $ctxB, $this->userWithRole('Student', 'Bo'));
        $parentA = $this->userWithRole('Parent', 'Pat A');
        $this->linkParent($parentA, $childA, $ctxA);
        $this->linkParent($parentA, $childB, $ctxB);
        $teacher = $this->scopedTeacher('Tariq', $ctxA);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        // student_id is their own child but in a different class → denied.
        $this->withToken($this->mobileToken($parentA))
            ->getJson("/api/mobile/homework/{$hw->id}?student_id={$childB->student_id}")
            ->assertStatus(403);

        // Unrelated child (another parent's) → denied.
        $this->withToken($this->mobileToken($parentA))
            ->getJson("/api/mobile/homework/{$hw->id}?student_id=99999")
            ->assertStatus(403);
    }

    public function test_student_dashboard_homework_block_carries_id_and_status(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $studentUser = $this->userWithRole('Student', 'Ada');
        $student = $this->makeStudent('Ada', $ctx, $studentUser);
        $teacher = $this->scopedTeacher('Tariq', $ctx);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        $this->submitAs($studentUser, $hw->id, ['content' => 'x']);

        $dashboard = $this->withToken($this->mobileToken($studentUser))->getJson('/api/mobile/dashboard');
        $dashboard->assertStatus(200);
        $item = collect($dashboard->json('homework'))->firstWhere('id', $hw->id);
        $this->assertNotNull($item);
        $this->assertEquals('submitted', $item['submission_status']);
    }

    // ── Attachments (private local disk) ────────────────────────────────────

    public function test_attachment_upload_is_served_only_to_authorized_callers(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $studentUser = $this->userWithRole('Student', 'Ada');
        $student = $this->makeStudent('Ada', $ctx, $studentUser);
        $this->makeStudent('Bo', $ctx, $this->userWithRole('Student', 'Bo'));
        $teacher = $this->scopedTeacher('Tariq', $ctx);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        $file = UploadedFile::fake()->createWithContent('answer.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF\n");
        $response = $this->withToken($this->mobileToken($studentUser))
            ->post("/api/mobile/homework/{$hw->id}/submit", [
                'content' => 'Attached my working.',
                'attachment' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.attachment.name', 'answer.pdf');

        $submission = HomeworkSubmission::where('student_id', $student->student_id)->first();
        $this->assertNotNull($submission->attachment_path);
        $this->assertStringStartsWith('homework_submissions/', $submission->attachment_path);

        // Owner student may download.
        $this->withToken($this->mobileToken($studentUser))
            ->get("/api/mobile/homework/submissions/{$submission->id}/attachment")
            ->assertStatus(200);

        // Reviewing teacher may download.
        $this->withToken($this->mobileToken($teacher['user']))
            ->get("/api/mobile/homework/submissions/{$submission->id}/attachment")
            ->assertStatus(200);

        \Illuminate\Support\Facades\Storage::disk('local')->delete($submission->attachment_path);
    }

    public function test_invalid_attachment_type_is_rejected(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $teacher = $this->scopedTeacher('Tariq', $ctx);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        $file = UploadedFile::fake()->createWithContent('notes.txt', 'plain text');
        $this->withToken($this->mobileToken($student))
            ->withHeader('Accept', 'application/json')
            ->post("/api/mobile/homework/{$hw->id}/submit", ['attachment' => $file])
            ->assertStatus(422);
    }

    public function test_oversized_attachment_is_rejected(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $teacher = $this->scopedTeacher('Tariq', $ctx);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        $file = UploadedFile::fake()->create('big.pdf', 6000); // 6000 KB > 5120 KB
        $this->withToken($this->mobileToken($student))
            ->withHeader('Accept', 'application/json')
            ->post("/api/mobile/homework/{$hw->id}/submit", ['attachment' => $file])
            ->assertStatus(422);
    }

    public function test_unauthorized_attachment_download_is_rejected(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $studentA = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $studentB = $this->makeStudent('Bo', $ctx, $this->userWithRole('Student', 'Bo'));
        $teacher = $this->scopedTeacher('Tariq', $ctx);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        $file = UploadedFile::fake()->createWithContent('answer.pdf', "%PDF-1.4\n%%EOF\n");
        $this->withToken($this->mobileToken($studentA))
            ->post("/api/mobile/homework/{$hw->id}/submit", ['attachment' => $file])
            ->assertStatus(200);

        $submission = HomeworkSubmission::where('student_id', $studentA->student_id)->first();

        // A different student cannot download another student's file.
        $this->withToken($this->mobileToken($studentB))
            ->get("/api/mobile/homework/submissions/{$submission->id}/attachment")
            ->assertStatus(403);

        \Illuminate\Support\Facades\Storage::disk('local')->delete($submission->attachment_path);
    }

    public function test_valid_submission_requires_content_or_attachment(): void
    {
        $ctx = $this->section('Form 2', 'A');
        $student = $this->makeStudent('Ada', $ctx, $this->userWithRole('Student', 'Ada'));
        $teacher = $this->scopedTeacher('Tariq', $ctx);
        $hw = $this->homework($teacher['user']->id, 'Form 2 A');

        $this->withToken($this->mobileToken($student))
            ->postJson("/api/mobile/homework/{$hw->id}/submit", [])
            ->assertStatus(422);
    }
}