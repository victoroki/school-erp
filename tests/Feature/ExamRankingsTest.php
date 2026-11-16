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
 * Ranking must be computed on percentages, not raw marks.
 *
 * The previous implementation selected SUM(marks_obtained) as the ranking key
 * and ordered by it, which puts a learner who scored 90/100 above one who scored
 * 18/20 — 90% against 90% — purely because the paper happened to be marked out
 * of a larger total. It also printed that raw mean with a "%" sign, and the view
 * hardcoded a green "Passed" badge on every row regardless of the mark.
 *
 * The fixture is built so the two orderings disagree. At 12 per page there is one
 * page, so the first name rendered is the top-ranked learner.
 */
class ExamRankingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected AcademicYear $year;

    protected ClassSection $classSection;

    protected Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'exam-rankings@test.local']);
        $this->admin->roles()->sync(Role::where('role_name', 'Super Admin')->pluck('role_id'));
        $this->admin->load('roles.permissions');

        $this->year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'Grade 9', 'numeric_value' => 9]);
        $section = Section::create(['name' => 'B']);

        $this->classSection = ClassSection::create([
            'academic_year_id' => $this->year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);

        $this->exam = Exam::create([
            'name' => 'End Term 3',
            'start_date' => '2026-11-02',
            'end_date' => '2026-11-12',
        ]);
    }

    /**
     * A paper with an explicit maximum and pass mark for this class section.
     */
    private function paper(string $name, int $max, int $pass): Subject
    {
        $subject = Subject::create([
            'name' => $name,
            'subject_code' => strtoupper(substr($name, 0, 3)) . substr(uniqid(), -4),
        ]);

        ExamSchedule::create([
            'exam_id' => $this->exam->exam_id,
            'class_id' => $this->classSection->class_id,
            'subject_id' => $subject->subject_id,
            'exam_date' => '2026-11-03',
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'max_marks' => $max,
            'passing_marks' => $pass,
        ]);

        return $subject;
    }

    private function sit(string $firstName, array $marksBySubjectId): Student
    {
        static $n = 0;
        $n++;

        $student = Student::create([
            'admission_no' => 'RK' . $n . substr(uniqid(), -6),
            'first_name' => $firstName,
            'last_name' => 'Pupil',
            'date_of_birth' => '2012-01-01',
            'gender' => 'male',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
            'education_system' => 'CBC',
        ]);

        foreach ($marksBySubjectId as $subjectId => $marks) {
            ExamResult::create([
                'exam_id' => $this->exam->exam_id,
                'student_id' => $student->student_id,
                'subject_id' => $subjectId,
                'class_section_id' => $this->classSection->class_section_id,
                'marks_obtained' => $marks,
            ]);
        }

        return $student;
    }

    public function test_learners_are_ranked_on_percentage_not_raw_marks(): void
    {
        // Large paper: 100 marks, pass at 40.
        $big = $this->paper('Astronomy', 100, 40);
        // Small paper: 20 marks, pass at 8 (the same 40% standard).
        $small = $this->paper('Botany', 20, 8);

        // RawSum takes 90/100 and 10/20 -> raw total 100, percentages 90% + 50% = mean 70%.
        $this->sit('Rawsum', [$big->subject_id => 90, $small->subject_id => 10]);

        // Perc takes 80/100 and 18/20 -> raw total 98, percentages 80% + 90% = mean 85%.
        $this->sit('Percent', [$big->subject_id => 80, $small->subject_id => 18]);

        $response = $this->actingAs($this->admin)
            ->get(route('exam-analysis.rankings', [
                'exam_id' => $this->exam->exam_id,
                'class_section_id' => $this->classSection->class_section_id,
            ]))
            ->assertOk();

        // Old behaviour ranked on the raw total (100 > 98), putting Rawsum first
        // and printing means of 50.0% / 49.0%. Both assertions below fail on it.
        $response->assertSeeInOrder(['Percent', 'Rawsum']);
        $response->assertSeeInOrder(['85.0%', '70.0%']);

        // Raw aggregates must not be presented any more.
        $response->assertDontSee('Total Marks');
    }

    public function test_the_pass_label_reflects_the_learners_own_marks(): void
    {
        $big = $this->paper('Astronomy', 100, 40);
        $small = $this->paper('Botany', 20, 8);

        // Passes: 90% and 90%, mean 90% against a 40% threshold.
        $this->sit('Clearer', [$big->subject_id => 90, $small->subject_id => 18]);

        // Fails: 30% and 25%, mean 27.5% against the same 40% threshold.
        $this->sit('Struggler', [$big->subject_id => 30, $small->subject_id => 5]);

        $response = $this->actingAs($this->admin)
            ->get(route('exam-analysis.rankings', [
                'exam_id' => $this->exam->exam_id,
                'class_section_id' => $this->classSection->class_section_id,
            ]))
            ->assertOk();

        $response->assertSee('Passed');
        // The old view hardcoded a green "Passed" on every row, so this could
        // never appear.
        $response->assertSee('Below 40%');
    }

    public function test_a_paper_marked_out_of_a_larger_total_does_not_flatter_a_learner(): void
    {
        // Both learners scored 8/20 (40%) and 8/20. Identical percentages must
        // give identical means no matter how the papers are scaled.
        $big = $this->paper('Astronomy', 100, 40);
        $small = $this->paper('Botany', 20, 8);

        $this->sit('Scaled', [$big->subject_id => 40, $small->subject_id => 8]);

        $response = $this->actingAs($this->admin)
            ->get(route('exam-analysis.rankings', [
                'exam_id' => $this->exam->exam_id,
                'class_section_id' => $this->classSection->class_section_id,
            ]))
            ->assertOk();

        // 40/100 = 40% and 8/20 = 40%, so the mean is 40.0% — not the 24.0 the
        // raw-mark mean would produce.
        $response->assertSee('40.0%');
    }

    public function test_a_class_with_no_results_renders_the_empty_state(): void
    {
        $this->paper('Astronomy', 100, 40);

        $this->actingAs($this->admin)
            ->get(route('exam-analysis.rankings', [
                'exam_id' => $this->exam->exam_id,
                'class_section_id' => $this->classSection->class_section_id,
            ]))
            ->assertOk()
            ->assertSee('No ranking data available');
    }
}
