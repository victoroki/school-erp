<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ReportCardTemplate;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Services\CurriculumService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalAndCurriculumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['email' => strtolower($role) . '-' . uniqid() . '@test.local']);
        $user->roles()->sync(Role::where('role_name', $role)->pluck('role_id'));

        return $user->load('roles.permissions');
    }

    private function makeStudent(int $suffix, string $educationSystem): Student
    {
        return Student::create([
            'admission_no' => 'ADM-' . uniqid(),
            'first_name' => 'Learner',
            'last_name' => 'Number' . $suffix,
            'date_of_birth' => '2012-01-01',
            'gender' => 'male',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
            'education_system' => $educationSystem,
        ]);
    }

    // ---------------------------------------------------------------------
    // Report card template curriculum default
    // ---------------------------------------------------------------------

    public function test_report_card_template_defaults_to_the_schools_curriculum(): void
    {
        // A CBC school: every learner is on CBC.
        $this->makeStudent(1, 'CBC');
        $this->makeStudent(2, 'CBC');

        $template = ReportCardTemplate::create(['name' => 'Default Card']);

        $this->assertSame(
            'CBC',
            $template->education_system,
            'A CBC school must not get an 8-4-4 report card by default.'
        );
    }

    public function test_report_card_template_defaults_to_8_4_4_for_an_8_4_4_school(): void
    {
        $this->makeStudent(1, '8-4-4');
        $this->makeStudent(2, '8-4-4');

        $template = ReportCardTemplate::create(['name' => 'Legacy Card']);

        $this->assertSame('8-4-4', $template->education_system);
    }

    public function test_an_explicit_curriculum_is_not_overridden(): void
    {
        $this->makeStudent(1, 'CBC');
        $this->makeStudent(2, 'CBC');

        $template = ReportCardTemplate::create([
            'name' => 'Legacy Archive Card',
            'education_system' => '8-4-4',
        ]);

        $this->assertSame('8-4-4', $template->education_system);
    }

    public function test_curriculum_falls_back_to_cbc_with_no_learners(): void
    {
        // Matches the schema's own students.education_system default.
        $this->assertSame('CBC', app(CurriculumService::class)->current());
        $this->assertTrue(app(CurriculumService::class)->isCbe());
    }

    // ---------------------------------------------------------------------
    // Marks approval scope
    // ---------------------------------------------------------------------

    /**
     * @return array{0: Exam, 1: ClassSection, 2: array<int, int>} [exam, classSection, studentIds]
     */
    private function examFixture(int $studentCount = 3): array
    {
        $year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'Grade 6', 'numeric_value' => 6]);
        $section = Section::create(['name' => 'A']);
        $classSection = ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);

        $exam = Exam::create([
            'name' => 'End Term 1',
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-10',
        ]);

        $studentIds = [];

        for ($i = 1; $i <= $studentCount; $i++) {
            $student = $this->makeStudent($i, 'CBC');
            $studentIds[] = $student->student_id;

            ExamResult::create([
                'exam_id' => $exam->exam_id,
                'student_id' => $student->student_id,
                'class_section_id' => $classSection->class_section_id,
                'marks_obtained' => 60 + $i,
                'is_approved' => false,
            ]);
        }

        return [$exam, $classSection, $studentIds];
    }

    public function test_approving_selected_with_nothing_ticked_approves_nothing(): void
    {
        [$exam, $classSection] = $this->examFixture();

        $this->actingAs($this->userWithRole('Super Admin'))
            ->post(route('marks-approval.approve'), [
                'exam_id' => $exam->exam_id,
                'class_section_id' => $classSection->class_section_id,
                'approval_scope' => 'selected',
                // no student_ids: the user ticked nobody
            ])
            ->assertRedirect();

        $this->assertSame(
            0,
            ExamResult::where('is_approved', true)->count(),
            'Submitting "Approve Selected Learners" with nothing ticked must not approve the whole batch.'
        );
    }

    public function test_whole_batch_approval_still_works(): void
    {
        [$exam, $classSection] = $this->examFixture();

        $this->actingAs($this->userWithRole('Super Admin'))
            ->post(route('marks-approval.approve'), [
                'exam_id' => $exam->exam_id,
                'class_section_id' => $classSection->class_section_id,
                'approval_scope' => 'batch',
            ])
            ->assertRedirect(route('marks-approval.index'));

        $this->assertSame(3, ExamResult::where('is_approved', true)->count());
    }

    public function test_approving_selected_learners_only_approves_those_learners(): void
    {
        [$exam, $classSection, $studentIds] = $this->examFixture();

        $this->actingAs($this->userWithRole('Super Admin'))
            ->post(route('marks-approval.approve'), [
                'exam_id' => $exam->exam_id,
                'class_section_id' => $classSection->class_section_id,
                'approval_scope' => 'selected',
                'student_ids' => [$studentIds[0]],
            ])
            ->assertRedirect(route('marks-approval.index'));

        $this->assertSame(1, ExamResult::where('is_approved', true)->count());
        $this->assertTrue(
            (bool) ExamResult::where('student_id', $studentIds[0])->first()->is_approved
        );
        $this->assertFalse(
            (bool) ExamResult::where('student_id', $studentIds[1])->first()->is_approved
        );
    }

    public function test_approval_records_who_approved(): void
    {
        [$exam, $classSection, $studentIds] = $this->examFixture();
        $approver = $this->userWithRole('Super Admin');

        $this->actingAs($approver)->post(route('marks-approval.approve'), [
            'exam_id' => $exam->exam_id,
            'class_section_id' => $classSection->class_section_id,
            'approval_scope' => 'selected',
            'student_ids' => $studentIds,
        ])->assertRedirect();

        $result = ExamResult::where('student_id', $studentIds[0])->firstOrFail();

        $this->assertTrue((bool) $result->is_approved);
        $this->assertSame($approver->id, (int) $result->approved_by);
        $this->assertNotNull($result->approved_at);
    }
}
