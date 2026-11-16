<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use App\Support\GradeBadge;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Examination statistics honesty.
 *
 * exams/show hardcoded "Highest Score 98.0%" and "Lowest Score 12.0%" for every
 * exam, and the controller averaged RAW marks and treated `marks >= 40` as a 40%
 * pass — meaningless when a paper is out of 50 rather than 100.
 *
 * exam_results has no max_marks column; the per-paper maximum lives on
 * exam_schedules.
 */
class ExamStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'exam-stats@test.local']);
        $this->admin->roles()->sync(Role::where('role_name', 'Super Admin')->pluck('role_id'));
        $this->admin->load('roles.permissions');
    }

    /**
     * @return array{0: Exam, 1: SchoolClass, 2: Subject}
     */
    private function examFixture(float $maxMarks = 50): array
    {
        $year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'Grade 7', 'numeric_value' => 7]);
        $section = Section::create(['name' => 'A']);
        ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);

        $subject = Subject::create(['name' => 'Science', 'subject_code' => 'SCI' . substr(uniqid(), -4)]);

        $exam = Exam::create([
            'name' => 'Mid Term',
            'start_date' => '2026-03-01',
            'end_date' => '2026-03-07',
        ]);

        ExamSchedule::create([
            'exam_id' => $exam->exam_id,
            'class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'exam_date' => '2026-03-02',
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'max_marks' => $maxMarks,
            // 20/50 = 40% pass mark.
            'passing_marks' => $maxMarks * 0.4,
        ]);

        return [$exam, $class, $subject];
    }

    private function makeStudent(int $suffix): Student
    {
        return Student::create([
            'admission_no' => 'ES' . $suffix . substr(uniqid(), -6),
            'first_name' => 'Exam',
            'last_name' => 'Learner' . $suffix,
            'date_of_birth' => '2011-01-01',
            'gender' => 'male',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
            'education_system' => 'CBC',
        ]);
    }

    private function addResult(Exam $exam, Subject $subject, float $marks): void
    {
        static $n = 0;
        $n++;

        ExamResult::create([
            'exam_id' => $exam->exam_id,
            'student_id' => $this->makeStudent($n)->student_id,
            'subject_id' => $subject->subject_id,
            'marks_obtained' => $marks,
        ]);
    }

    public function test_statistics_are_percentages_not_raw_marks(): void
    {
        [$exam, , $subject] = $this->examFixture(50);

        // Out of 50: 90%, 40%.
        $this->addResult($exam, $subject, 45);
        $this->addResult($exam, $subject, 20);

        $response = $this->actingAs($this->admin)
            ->get(route('exams.show', $exam->exam_id))
            ->assertOk();

        // Mean of raw marks would be 32.5; the mean percentage is 65.
        $response->assertSee('65.00');
        $response->assertSee('90.0');
        $response->assertSee('40.0');

        // The fabricated values must be gone.
        $response->assertDontSee('98.0%');
        $response->assertDontSee('12.0%');
    }

    public function test_a_paper_out_of_fifty_is_not_treated_as_out_of_one_hundred(): void
    {
        [$exam, , $subject] = $this->examFixture(50);

        // 30/50 is 60%, which is a pass at a 40% threshold.
        $this->addResult($exam, $subject, 30);

        $response = $this->actingAs($this->admin)
            ->get(route('exams.show', $exam->exam_id))
            ->assertOk();

        $response->assertSee('60.00');

        // Pass rate must read 100%, not 0% (raw 30 < 40 previously failed).
        $response->assertSee('100.0');
    }

    public function test_an_exam_with_no_results_shows_no_invented_numbers(): void
    {
        [$exam] = $this->examFixture(50);

        $response = $this->actingAs($this->admin)
            ->get(route('exams.show', $exam->exam_id))
            ->assertOk();

        $response->assertDontSee('98.0%');
        $response->assertDontSee('12.0%');
        $response->assertSee('0.00');
    }

    public function test_grade_badges_use_the_shared_resolver(): void
    {
        foreach ([
            'views/exam_results/table.blade.php',
            'views/exam_results/index.blade.php',
            'views/mark_sheets/index.blade.php',
        ] as $view) {
            $blade = file_get_contents(resource_path($view));

            $this->assertStringContainsString(
                'GradeBadge::for',
                $blade,
                "{$view} must use the shared grade badge resolver."
            );
        }

        // And the resolver itself treats a top CBE achievement as a success.
        $this->assertSame('badge-success', GradeBadge::for('EE1'));
        $this->assertSame('badge-danger', GradeBadge::for('BE2'));
    }
}
