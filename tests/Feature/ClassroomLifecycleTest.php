<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Classroom lifecycle.
 *
 * Regression coverage for the reported MySQL 1451 on class_sections_ibfk_4
 * when deleting a classroom. A room hosting a class section, timetable slot or
 * exam sitting is part of the academic record: the delete is refused with a
 * message naming the blockers, and the room is archived instead. Unused rooms
 * delete outright.
 */
class ClassroomLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

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

    private function makeClassroom(array $overrides = []): Classroom
    {
        static $seq = 0;
        $seq++;

        return Classroom::create(array_merge([
            'room_number' => 'CLT-' . $seq . '-' . substr(uniqid(), -4),
            'building' => 'Block T',
            'floor' => 1,
            'capacity' => 40,
        ], $overrides));
    }

    private function makeClassSection(Classroom $classroom): int
    {
        static $seq = 0;
        $seq++;

        $class = \App\Models\SchoolClass::create([
            'name' => 'CLTForm ' . $seq . '-' . uniqid(),
            'numeric_value' => $seq,
        ]);
        $section = \App\Models\Section::create([
            'class_id' => $class->class_id,
            'name' => 'C' . $seq,
            'capacity' => 40,
        ]);
        $year = \App\Models\AcademicYear::create([
            'name' => 'AY-CLT-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        return \App\Models\ClassSection::create([
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
            'academic_year_id' => $year->academic_year_id,
            'classroom_id' => $classroom->classroom_id,
        ])->class_section_id;
    }

    // ─── Unused classroom ────────────────────────────────────────────────

    public function test_deleting_an_unused_classroom_removes_the_row(): void
    {
        $classroom = $this->makeClassroom();

        $this->actingAs($this->admin)
            ->delete(route('classrooms.destroy', $classroom->classroom_id))
            ->assertRedirect(route('classrooms.index'));

        $this->assertDatabaseMissing('classrooms', ['classroom_id' => $classroom->classroom_id]);
    }

    // ─── Classroom with history ──────────────────────────────────────────

    public function test_deleting_a_classroom_hosting_a_class_section_is_refused(): void
    {
        $classroom = $this->makeClassroom();
        $this->makeClassSection($classroom);

        $response = $this->actingAs($this->admin)
            ->delete(route('classrooms.destroy', $classroom->classroom_id));

        // Refused with a message, not a 500 QueryException.
        $response->assertRedirect(route('classrooms.show', $classroom->classroom_id));
        $response->assertSessionHas('flash_notification');

        $this->assertDatabaseHas('classrooms', ['classroom_id' => $classroom->classroom_id]);
        $this->assertDatabaseHas('class_sections', ['classroom_id' => $classroom->classroom_id]);
    }

    public function test_deleting_a_classroom_with_a_timetable_slot_is_refused(): void
    {
        $classroom = $this->makeClassroom();
        $sectionId = $this->makeClassSection($classroom);

        $period = \App\Models\Period::create([
            'name' => 'CLT-P1',
            'start_time' => '08:00:00',
            'end_time' => '08:40:00',
            'type' => 'period',
        ]);
        $subject = \App\Models\Subject::create([
            'subject_code' => 'CLT' . substr(uniqid(), -4),
            'name' => 'Classroom Lifecycle Subject',
        ]);
        $year = \App\Models\AcademicYear::where('is_current', true)->first();

        // timetable.teacher_id is a RESTRICT FK to staff — a real staff row is
        // required, not a hard-coded id.
        $teacher = \App\Models\Staff::create([
            'first_name' => 'Slot',
            'last_name' => 'Teacher' . substr(uniqid(), -4),
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone_primary' => '078' . str_pad((string) random_int(0, 9999999), 7, '1', STR_PAD_LEFT),
            'work_email' => 'clt.' . uniqid() . '@test.local',
            'current_address' => '',
            'city' => '',
            'country' => '',
            'date_of_joining' => now()->toDateString(),
            'staff_type' => 'teaching',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
        ]);

        DB::table('timetable')->insert([
            'class_section_id' => $sectionId,
            'day_of_week' => 'monday',
            'period_id' => $period->period_id,
            'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->staff_id,
            'classroom_id' => $classroom->classroom_id,
            'academic_year_id' => $year->academic_year_id,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('classrooms.destroy', $classroom->classroom_id))
            ->assertRedirect(route('classrooms.show', $classroom->classroom_id));

        $this->assertDatabaseHas('timetable', ['classroom_id' => $classroom->classroom_id]);
    }

    public function test_the_refusal_message_names_the_blocking_records(): void
    {
        $classroom = $this->makeClassroom(['room_number' => 'BLOCKER-1']);
        $this->makeClassSection($classroom);

        $this->actingAs($this->admin)
            ->delete(route('classrooms.destroy', $classroom->classroom_id))
            ->assertSessionHas('flash_notification');

        $message = session('flash_notification');
        $this->assertStringContainsString('BLOCKER-1', $message);
        $this->assertStringContainsString('class section', $message);
        $this->assertStringContainsString('Archive', $message);
    }

    // ─── Archive / restore ───────────────────────────────────────────────

    public function test_archiving_keeps_every_dependent_row(): void
    {
        $classroom = $this->makeClassroom();
        $this->makeClassSection($classroom);

        $this->actingAs($this->admin)
            ->post(route('classrooms.archive', $classroom->classroom_id))
            ->assertRedirect(route('classrooms.show', $classroom->classroom_id));

        $this->assertFalse((bool) $classroom->fresh()->is_active);
        $this->assertDatabaseHas('class_sections', ['classroom_id' => $classroom->classroom_id]);
    }

    public function test_an_archived_classroom_is_hidden_from_the_active_list(): void
    {
        $this->makeClassroom(['room_number' => 'ACTIVE-1']);
        $archived = $this->makeClassroom(['room_number' => 'GONE-1']);
        $this->actingAs($this->admin)->post(route('classrooms.archive', $archived->classroom_id));

        $this->flushSession();

        $response = $this->actingAs($this->admin)->get(route('classrooms.index'));

        $response->assertOk();
        $response->assertSee('ACTIVE-1');
        $response->assertDontSee('GONE-1');
    }

    public function test_an_archived_classroom_is_listed_under_the_archived_filter(): void
    {
        $archived = $this->makeClassroom(['room_number' => 'GONE-2']);
        $this->actingAs($this->admin)->post(route('classrooms.archive', $archived->classroom_id));

        $this->flushSession();

        $this->actingAs($this->admin)
            ->get(route('classrooms.index', ['status' => 'archived']))
            ->assertOk()
            ->assertSee('GONE-2');
    }

    public function test_restoring_brings_a_classroom_back(): void
    {
        $classroom = $this->makeClassroom();
        $this->actingAs($this->admin)->post(route('classrooms.archive', $classroom->classroom_id));

        $this->actingAs($this->admin)
            ->post(route('classrooms.restore', $classroom->classroom_id))
            ->assertRedirect(route('classrooms.show', $classroom->classroom_id));

        $this->assertTrue((bool) $classroom->fresh()->is_active);
    }

    // ─── Authorization ───────────────────────────────────────────────────

    public function test_a_teacher_cannot_archive_or_delete_a_classroom(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');

        $classroom = $this->makeClassroom();

        $this->actingAs($teacher)
            ->post(route('classrooms.archive', $classroom->classroom_id))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->delete(route('classrooms.destroy', $classroom->classroom_id))
            ->assertForbidden();

        $this->assertDatabaseHas('classrooms', [
            'classroom_id' => $classroom->classroom_id,
            'is_active' => true,
        ]);
    }

    // ─── Show page usage panel ───────────────────────────────────────────

    public function test_the_show_page_explains_why_a_delete_would_be_refused(): void
    {
        $classroom = $this->makeClassroom();
        $this->makeClassSection($classroom);

        $this->actingAs($this->admin)
            ->get(route('classrooms.show', $classroom->classroom_id))
            ->assertOk()
            ->assertSee('Where this room is used')
            ->assertSee('cannot be deleted');
    }

    // ─── Index search ────────────────────────────────────────────────────

    public function test_the_index_search_narrows_by_room_number(): void
    {
        $this->makeClassroom(['room_number' => 'SEARCH-01']);
        $this->makeClassroom(['room_number' => 'OTHER-02']);

        $this->actingAs($this->admin)
            ->get(route('classrooms.index', ['q' => 'SEARCH-01']))
            ->assertOk()
            ->assertSee('SEARCH-01')
            ->assertDontSee('OTHER-02');
    }
}
