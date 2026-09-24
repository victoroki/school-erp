<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Attendance term scoping and the one-status-per-day invariant.
 *
 * The web path keyed an entry on (student_id, class_section_id, date) while the
 * mobile app keyed on (student_id, date), so a learner marked on both surfaces
 * produced two rows for one day — and the app's row carried a NULL
 * class_section_id, so it also disappeared from class-filtered views.
 */
class AttendanceTermScopingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected AcademicYear $year;

    protected ClassSection $classSection;

    protected Term $term;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'att-term@test.local']);
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

        $this->term = Term::create([
            'academic_year_id' => $this->year->academic_year_id,
            'name' => 'Term 1',
            'code' => 'T1',
            'start_date' => '2026-01-05',
            'end_date' => '2026-04-03',
            'status' => 'active',
        ]);
    }

    private function makeStudent(): Student
    {
        $student = Student::create([
            'admission_no' => 'AT' . substr(uniqid(), -8),
            'first_name' => 'Term',
            'last_name' => 'Scoped',
            'date_of_birth' => '2012-01-01',
            'gender' => 'male',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
        ]);

        StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => $this->classSection->class_section_id,
            'academic_year_id' => $this->year->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);

        return $student;
    }

    public function test_marking_the_same_learner_twice_on_one_day_produces_one_record(): void
    {
        $student = $this->makeStudent();

        // A second class section in the same year, as a promoted or moved learner
        // would have. The old key included class_section_id, so this created two.
        $secondClass = SchoolClass::create(['name' => 'Grade 5', 'numeric_value' => 5]);
        $secondSection = Section::create(['name' => 'B']);
        $other = ClassSection::create([
            'academic_year_id' => $this->year->academic_year_id,
            'class_id' => $secondClass->class_id,
            'section_id' => $secondSection->section_id,
        ]);

        foreach ([$this->classSection, $other] as $section) {
            $this->actingAs($this->admin)
                ->post(route('student-attendance.store'), [
                    'class_section_id' => $section->class_section_id,
                    'date' => '2026-02-10',
                    'attendance' => [$student->student_id => 'present'],
                ])
                ->assertRedirect();
        }

        $this->assertSame(
            1,
            StudentAttendance::where('student_id', $student->student_id)->count(),
            'A learner can only have one attendance status per day.'
        );
    }

    public function test_attendance_is_scoped_to_the_academic_year_and_term_of_its_date(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($this->admin)
            ->post(route('student-attendance.store'), [
                'class_section_id' => $this->classSection->class_section_id,
                'date' => '2026-02-10',
                'attendance' => [$student->student_id => 'present'],
            ])
            ->assertRedirect();

        $record = StudentAttendance::where('student_id', $student->student_id)->firstOrFail();

        $this->assertSame($this->year->academic_year_id, (int) $record->academic_year_id);
        $this->assertSame($this->term->id, (int) $record->term_id);
    }

    public function test_a_date_outside_every_term_keeps_the_year_but_no_term(): void
    {
        $student = $this->makeStudent();

        // Between terms: inside the academic year, outside the term range.
        $this->actingAs($this->admin)
            ->post(route('student-attendance.store'), [
                'class_section_id' => $this->classSection->class_section_id,
                'date' => '2026-04-20',
                'attendance' => [$student->student_id => 'present'],
            ])
            ->assertRedirect();

        $record = StudentAttendance::where('student_id', $student->student_id)->firstOrFail();

        $this->assertSame($this->year->academic_year_id, (int) $record->academic_year_id);
        $this->assertNull($record->term_id, 'An out-of-term date must keep a NULL term rather than a guessed one.');
    }

    public function test_the_database_rejects_a_duplicate_learner_and_date(): void
    {
        $student = $this->makeStudent();

        StudentAttendance::create([
            'student_id' => $student->student_id,
            'date' => '2026-03-03',
            'status' => 'present',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('student_attendance')->insert([
            'student_id' => $student->student_id,
            'date' => '2026-03-03',
            'status' => 'absent',
        ]);
    }

    public function test_period_resolution_returns_the_enrollment_year_and_matching_term(): void
    {
        $student = $this->makeStudent();

        $period = StudentAttendance::resolvePeriodFor($student->student_id, '2026-02-10');

        $this->assertSame($this->year->academic_year_id, $period['academic_year_id']);
        $this->assertSame((int) $this->term->id, $period['term_id']);
    }

    public function test_period_resolution_is_null_safe_for_an_unknown_learner(): void
    {
        $period = StudentAttendance::resolvePeriodFor(999999, '2026-02-10');

        $this->assertNull($period['academic_year_id']);
        $this->assertNull($period['term_id']);
    }
}
