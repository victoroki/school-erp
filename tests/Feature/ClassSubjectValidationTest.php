<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class curriculum assignment.
 *
 * ClassSubject::$rules declared every field `nullable`, so it validated nothing:
 * a POST omitting the foreign keys passed validation and inserted a row with NULL
 * class_id / subject_id / academic_year_id. The columns are nullable in the
 * schema, so the database accepted it and the row then appeared in listings as a
 * curriculum entry belonging to nothing. Only the form's HTML `required`
 * attributes stood in the way, and those do not survive a direct POST.
 *
 * class_subjects also has no unique key on (class, subject, year), so re-posting
 * the bulk grid added a second identical row for the same class.
 *
 * On the create/edit "UI divergence": it is deliberate and implemented in the
 * shared fields partial — `@if(isset($classSubject))` renders a single subject
 * select for edit, `@else` renders the bulk checkbox grid for create. That part
 * is not a defect and is not changed here; these tests cover the validation and
 * duplicate protection underneath it.
 */
class ClassSubjectValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected AcademicYear $year;

    protected SchoolClass $class;

    protected Subject $subjectA;

    protected Subject $subjectB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'class-subject@test.local']);
        $this->admin->roles()->sync(Role::where('role_name', 'Super Admin')->pluck('role_id'));
        $this->admin->load('roles.permissions');

        $this->year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $this->class = SchoolClass::create(['name' => 'Grade 6', 'numeric_value' => 8]);

        $this->subjectA = Subject::create(['name' => 'Astronomy', 'subject_code' => 'AST' . substr(uniqid(), -4)]);
        $this->subjectB = Subject::create(['name' => 'Botany', 'subject_code' => 'BOT' . substr(uniqid(), -4)]);
    }

    public function test_a_submission_missing_the_foreign_keys_is_rejected(): void
    {
        // Previously accepted, inserting a row with NULL class_id, subject_id and
        // academic_year_id.
        $this->actingAs($this->admin)
            ->post(route('class-subjects.store'), [])
            ->assertSessionHasErrors(['class_id', 'subject_id', 'academic_year_id']);

        $this->assertSame(0, ClassSubject::count(), 'No curriculum row may be created without its keys.');
    }

    public function test_an_unknown_subject_id_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('class-subjects.store'), [
                'class_id' => $this->class->class_id,
                'academic_year_id' => $this->year->academic_year_id,
                'subject_id' => [99999999],
            ])
            ->assertSessionHasErrors('subject_id.0');

        $this->assertSame(0, ClassSubject::count());
    }

    public function test_an_unknown_class_id_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('class-subjects.store'), [
                'class_id' => 99999999,
                'academic_year_id' => $this->year->academic_year_id,
                'subject_id' => [$this->subjectA->subject_id],
            ])
            ->assertSessionHasErrors('class_id');

        $this->assertSame(0, ClassSubject::count());
    }

    public function test_the_bulk_grid_assigns_each_selected_subject(): void
    {
        $this->actingAs($this->admin)
            ->post(route('class-subjects.store'), [
                'class_id' => $this->class->class_id,
                'academic_year_id' => $this->year->academic_year_id,
                'subject_id' => [$this->subjectA->subject_id, $this->subjectB->subject_id],
                'periods_per_week' => 4,
            ])
            ->assertRedirect(route('class-subjects.index'));

        $this->assertSame(2, ClassSubject::count());

        foreach ([$this->subjectA, $this->subjectB] as $subject) {
            $this->assertDatabaseHas('class_subjects', [
                'class_id' => $this->class->class_id,
                'subject_id' => $subject->subject_id,
                'academic_year_id' => $this->year->academic_year_id,
                'periods_per_week' => 4,
            ]);
        }
    }

    public function test_a_single_subject_id_is_accepted_and_normalised(): void
    {
        // The edit form posts a bare subject_id. CreateClassSubjectRequest wraps it
        // so the same rules validate both shapes.
        $this->actingAs($this->admin)
            ->post(route('class-subjects.store'), [
                'class_id' => $this->class->class_id,
                'academic_year_id' => $this->year->academic_year_id,
                'subject_id' => $this->subjectA->subject_id,
            ])
            ->assertRedirect(route('class-subjects.index'));

        $this->assertSame(1, ClassSubject::count());
        $this->assertDatabaseHas('class_subjects', [
            'class_id' => $this->class->class_id,
            'subject_id' => $this->subjectA->subject_id,
        ]);
    }

    public function test_reposting_the_grid_does_not_duplicate_rows(): void
    {
        $payload = [
            'class_id' => $this->class->class_id,
            'academic_year_id' => $this->year->academic_year_id,
            'subject_id' => [$this->subjectA->subject_id, $this->subjectB->subject_id],
        ];

        $this->actingAs($this->admin)->post(route('class-subjects.store'), $payload);
        $this->assertSame(2, ClassSubject::count());

        // Same submission again — the table has no unique key to catch this.
        $this->actingAs($this->admin)->post(route('class-subjects.store'), $payload);

        $this->assertSame(2, ClassSubject::count(), 'Re-posting the grid must not duplicate curriculum rows.');

        $this->assertSame(
            1,
            ClassSubject::where('class_id', $this->class->class_id)
                ->where('subject_id', $this->subjectA->subject_id)
                ->count()
        );
    }

    public function test_a_duplicate_within_one_submission_is_created_once(): void
    {
        $this->actingAs($this->admin)
            ->post(route('class-subjects.store'), [
                'class_id' => $this->class->class_id,
                'academic_year_id' => $this->year->academic_year_id,
                // The same subject ticked twice in one post.
                'subject_id' => [$this->subjectA->subject_id, $this->subjectA->subject_id],
            ]);

        $this->assertSame(1, ClassSubject::count());
    }

    public function test_assigning_nothing_new_reports_no_change_rather_than_success(): void
    {
        ClassSubject::create([
            'class_id' => $this->class->class_id,
            'subject_id' => $this->subjectA->subject_id,
            'academic_year_id' => $this->year->academic_year_id,
        ]);

        $this->actingAs($this->admin)
            ->post(route('class-subjects.store'), [
                'class_id' => $this->class->class_id,
                'academic_year_id' => $this->year->academic_year_id,
                'subject_id' => [$this->subjectA->subject_id],
            ]);

        // Flash::warning() stores a message with level 'warning' in the
        // standard flash_notification session bag.
        $flash = session('flash_notification');
        $this->assertNotNull($flash, 'A flash message must be set when nothing new is assigned.');
        $this->assertTrue(
            $flash->contains(fn ($m) => ($m->level ?? null) === 'warning'),
            'The flash message must be a warning, not a success.'
        );

        $this->assertSame(1, ClassSubject::count());
    }
}
