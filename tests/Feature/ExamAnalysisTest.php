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
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * exam_analysis performance and subject screens.
 *
 * Both rendered entirely fabricated figures — 450 learners, a 78.5% pass rate,
 * a 65.2 average, 12 subjects tested, fixed subject lists (Mathematics/English/
 * Kiswahili, Physics/History/Geography) and hardcoded chart arrays — while the
 * controller passed only $exams.
 */
class ExamAnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'exam-analysis@test.local']);
        $this->admin->roles()->sync(Role::where('role_name', 'Super Admin')->pluck('role_id'));
        $this->admin->load('roles.permissions');
    }

    /**
     * @return array{0: Exam, 1: Subject, 2: Subject}
     */
    private function fixture(): array
    {
        $year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'Grade 8', 'numeric_value' => 8]);
        $section = Section::create(['name' => 'A']);
        ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);

        $maths = Subject::create(['name' => 'Mathematics', 'subject_code' => 'MTH' . substr(uniqid(), -4)]);
        $kiswahili = Subject::create(['name' => 'Kiswahili', 'subject_code' => 'KIS' . substr(uniqid(), -4)]);

        $exam = Exam::create([
            'name' => 'End Term 2',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-10',
        ]);

        foreach ([$maths, $kiswahili] as $subject) {
            ExamSchedule::create([
                'exam_id' => $exam->exam_id,
                'class_id' => $class->class_id,
                'subject_id' => $subject->subject_id,
                'exam_date' => '2026-07-02',
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'max_marks' => 50,
                'passing_marks' => 20,
            ]);
        }

        return [$exam, $maths, $kiswahili];
    }

    private function addResults(Exam $exam, Subject $subject, array $marksOutOf50): void
    {
        foreach ($marksOutOf50 as $marks) {
            static $n = 0;
            $n++;

            $student = Student::create([
                'admission_no' => 'EA' . $n . substr(uniqid(), -6),
                'first_name' => 'Analysis',
                'last_name' => 'Learner' . $n,
                'date_of_birth' => '2011-01-01',
                'gender' => 'male',
                'city' => 'Nairobi',
                'country' => 'Kenya',
                'admission_date' => now(),
                'is_active' => true,
                'status' => 'active',
                'education_system' => 'CBC',
            ]);

            ExamResult::create([
                'exam_id' => $exam->exam_id,
                'student_id' => $student->student_id,
                'subject_id' => $subject->subject_id,
                'marks_obtained' => $marks,
            ]);
        }
    }

    public function test_performance_analysis_shows_real_figures(): void
    {
        [$exam, $maths, $kiswahili] = $this->fixture();

        // Maths out of 50: 45 (90%), 35 (70%) => mean 80, both pass.
        $this->addResults($exam, $maths, [45, 35]);
        // Kiswahili: 10 (20%), 20 (40%) => mean 30, one passes at the 40% mark.
        $this->addResults($exam, $kiswahili, [10, 20]);

        $response = $this->actingAs($this->admin)
            ->get(route('exam-analysis.performance', ['exam_id' => $exam->exam_id]))
            ->assertOk();

        // Real values: 4 learners assessed, 2 subjects tested.
        $response->assertSee('Learners Assessed');
        $response->assertSee('<h3>4</h3>', false);
        $response->assertSee('<h3>2</h3>', false);

        // Real per-subject means.
        $response->assertSee('80.0');
        $response->assertSee('30.0');

        // The fabricated figures must be gone.
        $response->assertDontSee('450');
        $response->assertDontSee('78.5');
        $response->assertDontSee('65.2');
    }

    public function test_subject_analysis_shows_real_figures_and_no_invented_subjects(): void
    {
        [$exam, $maths, $kiswahili] = $this->fixture();

        $this->addResults($exam, $maths, [45, 35, 25]);
        $this->addResults($exam, $kiswahili, [10, 15, 20]);

        $response = $this->actingAs($this->admin)
            ->get(route('exam-analysis.subject', ['exam_id' => $exam->exam_id]))
            ->assertOk();

        // Real subjects from the database.
        $response->assertSee('Mathematics');
        $response->assertSee('Kiswahili');

        // Invented subjects the page used to list regardless of data. Assert on
        // the rendered table cell, not the bare word — "History" also appears in
        // the application's navigation.
        foreach (['Physics', 'Chemistry', 'Geography', 'History'] as $invented) {
            $response->assertDontSee('font-weight-bold">' . $invented . '</td>', false);
        }

        // Fabricated statistics.
        $response->assertDontSee('76.5');
        $response->assertDontSee('12.3');
        $response->assertDontSee('10.8');

        // Real statistics columns.
        $response->assertSee('Std Dev');
        $response->assertSee('Median');
    }

    public function test_an_exam_with_no_marks_shows_an_empty_state_not_invented_numbers(): void
    {
        [$exam] = $this->fixture();

        foreach (['exam-analysis.performance', 'exam-analysis.subject'] as $route) {
            $response = $this->actingAs($this->admin)
                ->get(route($route, ['exam_id' => $exam->exam_id]))
                ->assertOk();

            $response->assertDontSee('450');
            $response->assertDontSee('78.5');
            $response->assertSee('nothing to analyse');
        }
    }

    public function test_the_views_no_longer_contain_fabricated_chart_series(): void
    {
        foreach (['views/exam_analysis/performance.blade.php', 'views/exam_analysis/subject.blade.php'] as $view) {
            $blade = file_get_contents(resource_path($view));

            foreach (['62, 65, 68, 65.2', '45, 120, 180, 85, 20', '68.5, 72.3, 65.8'] as $series) {
                $this->assertStringNotContainsString(
                    $series,
                    $blade,
                    "{$view} still contains the hardcoded chart series [{$series}]."
                );
            }

            $this->assertStringContainsString('$analysis', $blade);
        }
    }
}
