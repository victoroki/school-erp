<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\ClassSubject;
use App\Models\Classroom;
use App\Models\Period;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Subject lifecycle.
 *
 * Regression coverage for the reported MySQL 1451 on
 * `class_subjects_ibfk_2` when deleting a subject. A subject that has been
 * allocated, taught, examined or timetabled cannot be deleted — the delete is
 * refused with a message naming the blockers, and the subject is archived
 * instead so no academic history is destroyed.
 */
class SubjectLifecycleTest extends TestCase
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

    private function makeSubject(array $overrides = []): Subject
    {
        static $seq = 0;
        $seq++;

        return Subject::create(array_merge([
            'subject_code' => 'SUB' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT),
            'name' => 'Subject ' . $seq,
            'description' => 'A subject used by the lifecycle tests.',
        ], $overrides));
    }

    /**
     * `classes` has no `level` column — ordering lives in `numeric_value`.
     */
    private function makeClass(): SchoolClass
    {
        static $seq = 0;
        $seq++;

        return SchoolClass::create([
            'name' => 'Form ' . $seq . '-' . uniqid(),
            'numeric_value' => $seq,
        ]);
    }

    /**
     * There is no StaffFactory, and `create_staff_table` left a set of NOT NULL
     * columns behind (the HR revamp migration renamed fields, it never dropped
     * them), so a valid row has to spell all of them out.
     */
    private function makeStaff(): Staff
    {
        static $seq = 0;
        $seq++;

        return Staff::create([
            'first_name' => 'Test',
            'middle_name' => null,
            'last_name' => 'Teacher' . $seq,
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone_primary' => '07' . str_pad((string) $seq, 8, '1', STR_PAD_LEFT),
            'work_email' => 'staff' . $seq . '.' . uniqid() . '@test.local',
            'personal_email' => null,
            'current_address' => '',
            'city' => '',
            'country' => '',
            'employee_number' => null,
            'tsc_number' => null,
            'designation' => null,
            'qualification' => null,
            'date_of_joining' => now()->toDateString(),
            'staff_type' => 'teaching',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
        ]);
    }

    /**
     * `classrooms` is keyed on `room_number`, not `name`.
     */
    private function makeClassroom(): Classroom
    {
        static $seq = 0;
        $seq++;

        return Classroom::create([
            'room_number' => 'R' . $seq . '-' . substr(uniqid(), -4),
            'building' => 'Block A',
            'floor' => 1,
            'capacity' => 40,
        ]);
    }

    private function makeClassSection(AcademicYear $year = null): ClassSection
    {
        $class = $this->makeClass();

        $section = Section::create([
            'class_id' => $class->class_id,
            'name' => 'A' . substr(uniqid(), -3),
            'capacity' => 40,
        ]);

        return ClassSection::create([
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
            'academic_year_id' => ($year ?: $this->makeAcademicYear())->academic_year_id,
            'classroom_id' => $this->makeClassroom()->classroom_id,
        ]);
    }

    private function makeAcademicYear(): AcademicYear
    {
        return AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);
    }

    // ─── Unused subject ────────────────────────────────────────────────

    public function test_deleting_an_unused_subject_removes_the_row(): void
    {
        $subject = $this->makeSubject(['name' => 'Environmental Science']);

        $response = $this->actingAs($this->admin)->delete(route('subjects.destroy', $subject->subject_id));

        $response->assertRedirect(route('subjects.index'));
        $this->assertDatabaseMissing('subjects', ['subject_id' => $subject->subject_id]);
    }

    public function test_a_new_subject_is_active_by_default(): void
    {
        $subject = $this->makeSubject();

        $this->assertTrue((bool) $subject->fresh()->is_active);
    }

    // ─── Subject with history ──────────────────────────────────────────

    public function test_deleting_a_subject_with_a_class_allocation_is_refused(): void
    {
        $subject = $this->makeSubject();
        $class = $this->makeClass();

        ClassSubject::create([
            'class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'academic_year_id' => $this->makeAcademicYear()->academic_year_id,
            'periods_per_week' => 4,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('subjects.destroy', $subject->subject_id));

        $response->assertRedirect(route('subjects.show', $subject->subject_id));
        $response->assertSessionHas('flash_notification');
        $this->assertDatabaseHas('subjects', ['subject_id' => $subject->subject_id]);
        $this->assertDatabaseHas('class_subjects', ['subject_id' => $subject->subject_id]);
    }

    /**
     * The historical rows must survive the refusal untouched. Losing a real
     * learner's allocation is the failure mode the constraint exists to stop.
     */
    public function test_refusing_a_delete_leaves_the_history_intact(): void
    {
        $subject = $this->makeSubject();
        $class = $this->makeClass();

        ClassSubject::create([
            'class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'academic_year_id' => $this->makeAcademicYear()->academic_year_id,
            'periods_per_week' => 3,
        ]);

        $this->actingAs($this->admin)->delete(route('subjects.destroy', $subject->subject_id));

        $this->assertDatabaseHas('class_subjects', [
            'subject_id' => $subject->subject_id,
            'class_id' => $class->class_id,
        ]);
        $this->assertTrue((bool) $subject->fresh()->is_active, 'A refused delete must not archive silently.');
    }

    public function test_a_teacher_allocation_also_blocks_deletion(): void
    {
        $subject = $this->makeSubject();
        $staff = $this->makeStaff();
        $year = $this->makeAcademicYear();

        TeacherSubject::create([
            'staff_id' => $staff->staff_id,
            'subject_id' => $subject->subject_id,
            'class_section_id' => $this->makeClassSection($year)->class_section_id,
            'academic_year_id' => $year->academic_year_id,
        ]);

        $this->actingAs($this->admin)->delete(route('subjects.destroy', $subject->subject_id))
            ->assertRedirect(route('subjects.show', $subject->subject_id));

        $this->assertDatabaseHas('teacher_subjects', ['subject_id' => $subject->subject_id]);
    }

    public function test_a_timetable_slot_also_blocks_deletion(): void
    {
        $subject = $this->makeSubject();
        $year = $this->makeAcademicYear();
        $section = $this->makeClassSection($year);

        // `periods` has no `sort_order` column; ordering is implied by
        // start_time and `type` distinguishes teaching time from a break.
        $period = Period::create([
            'name' => 'P1',
            'start_time' => '08:00:00',
            'end_time' => '08:40:00',
            'type' => 'period',
        ]);

        DB::table('timetable')->insert([
            'class_section_id' => $section->class_section_id,
            'day_of_week' => 'monday',
            'period_id' => $period->period_id,
            'subject_id' => $subject->subject_id,
            'teacher_id' => $this->makeStaff()->staff_id,
            'classroom_id' => $this->makeClassroom()->classroom_id,
            'academic_year_id' => $year->academic_year_id,
        ]);

        $this->actingAs($this->admin)->delete(route('subjects.destroy', $subject->subject_id))
            ->assertRedirect(route('subjects.show', $subject->subject_id));

        $this->assertDatabaseHas('timetable', ['subject_id' => $subject->subject_id]);
    }

    // ─── Error response quality ────────────────────────────────────────

    public function test_the_refusal_message_names_the_blocking_records(): void
    {
        $subject = $this->makeSubject(['name' => 'History']);
        $class = $this->makeClass();

        ClassSubject::create([
            'class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'academic_year_id' => $this->makeAcademicYear()->academic_year_id,
            'periods_per_week' => 2,
        ]);

        $this->actingAs($this->admin)->delete(route('subjects.destroy', $subject->subject_id))
            ->assertSessionHas('flash_notification');

        $message = session('flash_notification');

        $this->assertStringContainsString('History', $message);
        $this->assertStringContainsString('class-subject allocation', $message);
        $this->assertStringContainsString('Archive', $message);
    }

    public function test_deleting_a_missing_subject_reports_it_instead_of_failing(): void
    {
        $this->actingAs($this->admin)->delete(route('subjects.destroy', 999999))
            ->assertRedirect(route('subjects.index'))
            ->assertSessionHas('flash_notification');
    }

    // ─── Archive / restore ─────────────────────────────────────────────

    public function test_archiving_keeps_every_dependent_row(): void
    {
        $subject = $this->makeSubject();
        $class = $this->makeClass();

        ClassSubject::create([
            'class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'academic_year_id' => $this->makeAcademicYear()->academic_year_id,
            'periods_per_week' => 2,
        ]);

        $this->actingAs($this->admin)
            ->post(route('subjects.archive', $subject->subject_id))
            ->assertRedirect(route('subjects.show', $subject->subject_id));

        $this->assertFalse((bool) $subject->fresh()->is_active);
        $this->assertDatabaseHas('class_subjects', ['subject_id' => $subject->subject_id]);
    }

    public function test_an_archived_subject_is_hidden_from_the_active_list(): void
    {
        $this->makeSubject(['name' => 'Biology']);
        $archived = $this->makeSubject(['name' => 'Geography']);
        $this->actingAs($this->admin)->post(route('subjects.archive', $archived->subject_id));

        // The archive POST leaves a flash notification behind, and the index
        // renders it — by design, an administrator should be told what just
        // happened. That message quotes the subject name, so it has to be
        // dropped or this assertion would be reading the banner, not the
        // table.
        $this->flushSession();

        $response = $this->actingAs($this->admin)->get(route('subjects.index'));

        $response->assertOk();
        $response->assertSee('Biology');
        $response->assertDontSee('Geography');
    }

    public function test_an_archived_subject_is_listed_under_the_archived_filter(): void
    {
        $archived = $this->makeSubject(['name' => 'Geography']);
        $this->actingAs($this->admin)->post(route('subjects.archive', $archived->subject_id));

        $this->flushSession();

        $response = $this->actingAs($this->admin)->get(route('subjects.index', ['status' => 'archived']));

        $response->assertOk();
        $response->assertSee('Geography');
    }

    public function test_restoring_brings_a_subject_back_into_the_active_list(): void
    {
        $subject = $this->makeSubject();
        $this->actingAs($this->admin)->post(route('subjects.archive', $subject->subject_id));

        $this->actingAs($this->admin)
            ->post(route('subjects.restore', $subject->subject_id))
            ->assertRedirect(route('subjects.show', $subject->subject_id));

        $this->assertTrue((bool) $subject->fresh()->is_active);
    }

    public function test_the_show_page_explains_why_a_delete_would_be_refused(): void
    {
        $subject = $this->makeSubject();
        $class = $this->makeClass();

        ClassSubject::create([
            'class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'academic_year_id' => $this->makeAcademicYear()->academic_year_id,
            'periods_per_week' => 2,
        ]);

        $response = $this->actingAs($this->admin)->get(route('subjects.show', $subject->subject_id));

        $response->assertOk();
        $response->assertSee('Where this subject is used');
        $response->assertSee('cannot be deleted');
    }

    public function test_a_teacher_cannot_archive_a_subject(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');

        $subject = $this->makeSubject();

        $this->actingAs($teacher)
            ->post(route('subjects.archive', $subject->subject_id))
            ->assertForbidden();

        $this->assertTrue((bool) $subject->fresh()->is_active);
    }

    public function test_is_active_cannot_be_set_from_a_crafted_form_post(): void
    {
        $subject = $this->makeSubject();

        $this->actingAs($this->admin)->put(route('subjects.update', $subject->subject_id), [
            'subject_code' => $subject->subject_code,
            'name' => $subject->name,
            'is_active' => false,
        ])->assertRedirect(route('subjects.index'));

        $this->assertTrue(
            (bool) $subject->fresh()->is_active,
            'is_active is not fillable; it must only move through the archive actions.'
        );
    }

    public function test_the_index_search_narrows_by_name_and_code(): void
    {
        $this->makeSubject(['name' => 'Kiswahili', 'subject_code' => 'KIS101']);
        $this->makeSubject(['name' => 'Physics', 'subject_code' => 'PHY201']);

        $response = $this->actingAs($this->admin)->get(route('subjects.index', ['q' => 'PHY201']));

        $response->assertOk();
        $response->assertSee('Physics');
        $response->assertDontSee('Kiswahili');
    }
}
