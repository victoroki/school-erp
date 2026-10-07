<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Class Subjects index.
 *
 * The page is scoped to the current academic year — classes are not per-year,
 * so listing every year showed the same class more than once — and the subject
 * lists are fetched on demand. These cover both decisions:
 *
 *  1. Only the current year's curriculum is listed, one card per class, and the
 *     page says which year it is showing.
 *
 *  2. The grid ships card shells only. It used to render every assignment in
 *     the year, each with its own <form>, CSRF token and inline confirm()
 *     handler, so a 14-class school produced 758 KB of HTML and 350 forms
 *     before anything was readable. The list now comes from
 *     class-subjects.curriculum the first time a card is opened.
 *
 *  3. The search box, the card disclosure and Expand/Collapse All are wired,
 *     and search matches subjects by name even before a card is loaded.
 */
class ClassSubjectsIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');
    }

    private function makeYear(string $name, bool $current): AcademicYear
    {
        static $seq = 0;
        $seq++;

        return AcademicYear::create([
            'name' => $name,
            'start_date' => sprintf('20%02d-01-01', 20 + $seq),
            'end_date' => sprintf('20%02d-12-31', 20 + $seq),
            'is_current' => $current,
        ]);
    }

    private function makeClass(string $name, int $grade): SchoolClass
    {
        return SchoolClass::create(['name' => $name, 'numeric_value' => $grade]);
    }

    private function makeSubject(string $code, string $name, bool $active = true): Subject
    {
        $subject = Subject::create([
            'subject_code' => $code,
            'name' => $name,
        ]);

        // is_active is deliberately absent from $fillable — archiving goes
        // through SubjectLifecycleService, never request input — so mass
        // assignment would drop it silently. Set it through the query builder.
        Subject::whereKey($subject->subject_id)->update(['is_active' => $active]);

        return $subject->fresh();
    }

    private function assign(SchoolClass $class, Subject $subject, AcademicYear $year, int $periods = 4): ClassSubject
    {
        return ClassSubject::create([
            'class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'academic_year_id' => $year->academic_year_id,
            'periods_per_week' => $periods,
        ]);
    }

    public function test_only_the_current_academic_year_is_listed()
    {
        $old = $this->makeYear('2024/2025', false);
        $cur = $this->makeYear('2025/2026', true);

        $class = $this->makeClass('Grade 5', 5);
        $maths = $this->makeSubject('MTH', 'Mathematics');
        $english = $this->makeSubject('ENG', 'English');
        $art = $this->makeSubject('ART', 'Art & Design');

        $this->assign($class, $maths, $old);
        $this->assign($class, $english, $old);
        $this->assign($class, $art, $old);

        $this->assign($class, $maths, $cur);
        $this->assign($class, $english, $cur);

        $html = $this->actingAs($this->admin)->get(route('class-subjects.index'))
            ->assertStatus(200)
            ->getContent();

        // One card for the class — the same class in a second year is not a
        // second card.
        $this->assertSame(1, substr_count($html, '<details class="cs-disclosure"'));
        $this->assertSame(1, substr_count($html, 'data-subject-count="'));

        // The card is the current year, and the page names that year.
        $this->assertStringContainsString('data-year-id="' . $cur->academic_year_id . '"', $html);
        $this->assertStringContainsString('2025/2026', $html);
        $this->assertStringNotContainsString('2024/2025', $html);

        // Two subjects this year, three last year: only this year's count shows.
        $this->assertStringContainsString('2 subjects', $html);
        $this->assertStringNotContainsString('3 subjects', $html);

        // The year filter is gone, and so is the option to widen the scope.
        $this->assertStringNotContainsString('classSubjectYear', $html);
    }

    public function test_the_grid_ships_cards_without_their_subject_rows()
    {
        $year = $this->makeYear('2025/2026', true);
        $class = $this->makeClass('Grade 5', 5);

        for ($i = 0; $i < 6; $i++) {
            $this->assign($class, $this->makeSubject('S' . $i, 'Subject ' . $i), $year);
        }

        $html = $this->actingAs($this->admin)->get(route('class-subjects.index'))->getContent();

        // One card, no subject markup, and no per-row forms: the only
        // method-override form on the page is the shared one every delete
        // button posts through.
        $this->assertSame(1, substr_count($html, '<details class="cs-disclosure"'));
        $this->assertSame(0, substr_count($html, 'class="cs-item"'));
        $this->assertSame(1, substr_count($html, 'name="_method"'));
        $this->assertStringContainsString('id="classSubjectDeleteForm"', $html);
        $this->assertStringContainsString('id="classSubjectClearForm"', $html);

        // Counts, the search haystack and the clear target are server-rendered,
        // so a card is still useful — and still searchable — before it loads.
        $this->assertStringContainsString('6 subjects', $html);
        $this->assertStringContainsString('data-subject-count="6"', $html);
        $this->assertStringContainsString('subject 5', $html);
        $this->assertStringContainsString('data-clear', $html);
    }

    public function test_the_curriculum_endpoint_serves_the_current_year_by_class()
    {
        $old = $this->makeYear('2024/2025', false);
        $cur = $this->makeYear('2025/2026', true);

        $gradeFive = $this->makeClass('Grade 5', 5);
        $gradeSix = $this->makeClass('Grade 6', 6);

        $maths = $this->makeSubject('MTH', 'Mathematics');
        $english = $this->makeSubject('ENG', 'English');
        $history = $this->makeSubject('HIS', 'History');
        $retired = $this->makeSubject('RET', 'Retired Studies', false);

        $this->assign($gradeFive, $history, $old);
        $this->assign($gradeFive, $english, $cur);
        $this->assign($gradeFive, $maths, $cur, 5);
        $this->assign($gradeFive, $retired, $cur);
        $this->assign($gradeSix, $maths, $cur);

        $response = $this->actingAs($this->admin)->getJson(route('class-subjects.curriculum'));
        $response->assertStatus(200);

        $payload = $response->json();

        $this->assertSame('2025/2026', $payload['year']['name']);
        $this->assertArrayHasKey((string) $gradeFive->class_id, $payload['classes']);
        $this->assertArrayHasKey((string) $gradeSix->class_id, $payload['classes']);

        $gradeFiveSubjects = collect($payload['classes'][$gradeFive->class_id]);

        // Last year's subject never appears, and the current year's are sorted
        // by name with the periods and archived flag the row markup needs.
        $this->assertSame(
            ['English', 'Mathematics', 'Retired Studies'],
            $gradeFiveSubjects->pluck('name')->all()
        );
        $this->assertSame(5, $gradeFiveSubjects->firstWhere('name', 'Mathematics')['periods']);
        $this->assertTrue($gradeFiveSubjects->firstWhere('name', 'Retired Studies')['archived']);
        $this->assertFalse($gradeFiveSubjects->firstWhere('name', 'English')['archived']);

        // Every row carries the targets its buttons need, so the client builds
        // no URLs of its own.
        $mathsRow = $gradeFiveSubjects->firstWhere('name', 'Mathematics');
        $this->assertSame(route('class-subjects.show', [$mathsRow['id']]), $mathsRow['show_url']);
        $this->assertSame(route('class-subjects.edit', [$mathsRow['id']]), $mathsRow['edit_url']);
        $this->assertSame(route('class-subjects.destroy', [$mathsRow['id']]), $mathsRow['delete_url']);
    }

    public function test_controls_are_present_and_wired()
    {
        $class = $this->makeClass('Grade 5', 5);
        $this->assign($class, $this->makeSubject('MTH', 'Mathematics'), $this->makeYear('2025/2026', true));

        $html = $this->actingAs($this->admin)->get(route('class-subjects.index'))->getContent();

        foreach ([
            'classSubjectSearch',
            'classSubjectSearchClear',
            'classSubjectNoResults',
            'classSubjectResultCount',
            'classSubjectDeleteForm',
            'classSubjectClearForm',
            'btnExpandAll',
            'btnCollapseAll',
        ] as $id) {
            $this->assertStringContainsString('id="' . $id . '"', $html, $id . ' control is missing');
        }

        // Delegated handler, and the disclosure state it maintains.
        $this->assertStringContainsString("grid.addEventListener('click'", $html);
        $this->assertStringContainsString('aria-live="polite"', $html);
        $this->assertStringContainsString('<details class="cs-disclosure"', $html);

        // Searchable text per card, keyed to the class the list is fetched for.
        $this->assertStringContainsString('data-search=', $html);
        $this->assertStringContainsString('data-class-id=', $html);
        $this->assertStringContainsString('data-subject-count=', $html);

        // The card list is fetched once, on demand. The endpoint is inlined as
        // JSON, so compare against the encoded form the page carries.
        $this->assertStringContainsString(json_encode(route('class-subjects.curriculum')), $html);
        $this->assertStringContainsString('loadCurriculum', $html);
    }

    public function test_the_page_explains_itself_when_no_year_is_current()
    {
        $this->makeYear('2025/2026', false);

        $class = $this->makeClass('Grade 5', 5);
        $this->assign($class, $this->makeSubject('MTH', 'Mathematics'), AcademicYear::first());

        $response = $this->actingAs($this->admin)->get(route('class-subjects.index'));

        $response->assertStatus(200);
        $response->assertSee('No academic year is marked as current');
        $response->assertSee(route('academic-years.index'), false);

        // No grid, no toolbar and no "assign" shortcut to a year that is not
        // the one this page manages.
        $this->assertStringNotContainsString('id="class-subjects-grid"', $response->getContent());
        $this->assertStringNotContainsString('class-subjects.create', $response->getContent());
    }

    public function test_the_curriculum_endpoint_is_empty_without_a_current_year()
    {
        $this->makeYear('2025/2026', false);

        $this->actingAs($this->admin)
            ->getJson(route('class-subjects.curriculum'))
            ->assertStatus(200)
            ->assertExactJson(['year' => null, 'classes' => []]);
    }

    public function test_archived_subjects_are_counted_on_the_card()
    {
        $class = $this->makeClass('Grade 5', 5);
        $year = $this->makeYear('2025/2026', true);

        $this->assign($class, $this->makeSubject('MTH', 'Mathematics'), $year);
        $this->assign($class, $this->makeSubject('RET', 'Retired Studies', false), $year);

        $html = $this->actingAs($this->admin)->get(route('class-subjects.index'))->getContent();

        // Counted, not hidden: the card warns before it is even opened.
        $this->assertStringContainsString('1 archived', $html);
        $this->assertStringContainsString('cs-badge-warn', $html);
    }

    public function test_empty_state_offers_the_create_action()
    {
        $this->makeYear('2025/2026', true);

        $response = $this->actingAs($this->admin)->get(route('class-subjects.index'));

        $response->assertStatus(200);
        $response->assertSee('No subjects assigned for 2025/2026 yet');
        $response->assertSee(route('class-subjects.create'), false);

        // No grid and no toolbar to filter when there is nothing to filter.
        $this->assertStringNotContainsString('id="class-subjects-grid"', $response->getContent());
    }

    public function test_periods_per_week_are_summarised_per_card()
    {
        $class = $this->makeClass('Grade 5', 5);
        $year = $this->makeYear('2025/2026', true);

        $this->assign($class, $this->makeSubject('MTH', 'Mathematics'), $year, 5);
        $this->assign($class, $this->makeSubject('ENG', 'English'), $year, 3);

        $html = $this->actingAs($this->admin)->get(route('class-subjects.index'))->getContent();

        $this->assertStringContainsString('8 periods / week', $html);
    }

    public function test_the_assign_form_defaults_to_the_current_year()
    {
        $old = $this->makeYear('2024/2025', false);
        $cur = $this->makeYear('2025/2026', true);

        $html = $this->actingAs($this->admin)->get(route('class-subjects.create'))->getContent();

        $this->assertStringContainsString('value="' . $cur->academic_year_id . '" selected', $html);
        $this->assertStringContainsString('2025/2026 (current)', $html);
        $this->assertStringContainsString('2024/2025', $html);
    }
}
