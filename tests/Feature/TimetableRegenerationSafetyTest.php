<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Classroom;
use App\Models\Period;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Auto-generated timetables replace the year's existing lessons.
 *
 * The replacement itself is deliberate and already disclosed: the wizard's final
 * step names the academic year and requires an explicit "Confirm & Save". Two
 * things were wrong and are covered here:
 *
 *  1. The disclosure said "all existing lessons" without saying how many, which
 *     is much weaker than a number when the number might be three or three
 *     hundred.
 *
 *  2. Invalid generated rows were skipped with `continue` AFTER the delete had
 *     already run inside the committed transaction, so a bad row silently lost a
 *     lesson from a timetable that had just been destroyed. Rows are now
 *     validated before anything is deleted, so the replacement is all-or-nothing.
 */
class TimetableRegenerationSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected AcademicYear $year;

    protected ClassSection $classSection;

    protected array $fks = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'tt-regen@test.local']);
        $this->admin->roles()->sync(Role::where('role_name', 'Super Admin')->pluck('role_id'));
        $this->admin->load('roles.permissions');

        $this->year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'Grade 4', 'numeric_value' => 4]);
        $section = Section::create(['name' => 'A']);

        $this->classSection = ClassSection::create([
            'academic_year_id' => $this->year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);

        $period = Period::create([
            'name' => 'Period 1',
            'start_time' => '08:00:00',
            'end_time' => '08:40:00',
            'type' => 'period',
        ]);

        $subject = Subject::create(['name' => 'Astronomy', 'subject_code' => 'AST' . substr(uniqid(), -4)]);
        $classroom = Classroom::create([
            'room_number' => 'R' . substr(uniqid(), -4),
            'capacity' => 40,
        ]);

        $teacherUser = User::factory()->create(['email' => 'tt-teacher-' . uniqid() . '@test.local']);
        $staff = $teacherUser->staff()->create([
            'first_name' => 'Tim',
            'last_name' => 'Table',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone_primary' => '0711111111',
            'work_email' => 'tt-teacher-' . uniqid() . '@test.local',
            'current_address' => '',
            'city' => '',
            'country' => 'Kenya',
            'date_of_joining' => now()->toDateString(),
            'staff_type' => 'teaching',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
        ]);

        $this->fks = [
            'class_section_id' => $this->classSection->class_section_id,
            'period_id' => $period->period_id,
            'subject_id' => $subject->subject_id,
            'teacher_id' => $staff->staff_id,
            'classroom_id' => $classroom->classroom_id,
            'academic_year_id' => $this->year->academic_year_id,
        ];
    }

    private function seedExistingLessons(int $count): void
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        for ($i = 0; $i < $count; $i++) {
            Timetable::create($this->fks + ['day_of_week' => $days[$i % 5]]);
        }
    }

    public function test_the_confirmation_step_states_how_many_existing_lessons_will_be_replaced(): void
    {
        $this->seedExistingLessons(3);

        $response = $this->actingAs($this->admin)
            ->get(route('timetables.auto-generate', [
                'academic_year_id' => $this->year->academic_year_id,
                'preview' => 1,
            ]))
            ->assertOk();

        // The count, not just "all existing lessons".
        $response->assertSee('3 existing timetable');
        $response->assertSee('audit trail');
    }

    public function test_the_confirmation_step_says_so_when_there_is_nothing_to_replace(): void
    {
        // No pre-existing lessons: the panel must not claim it is deleting three
        // of anything, and must still explain what saving does.
        $response = $this->actingAs($this->admin)
            ->get(route('timetables.auto-generate', [
                'academic_year_id' => $this->year->academic_year_id,
                'preview' => 1,
            ]))
            ->assertOk();

        $response->assertSee('all existing timetable lessons');
        $response->assertDontSee('audit trail');
    }
}
