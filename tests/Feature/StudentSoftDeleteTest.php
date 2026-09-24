<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Soft deletes on students.
 *
 * 18 constraints reference `students`. 10 of them (exam_results,
 * student_attendance, student_class_enrollments, ...) are RESTRICT, so a hard
 * delete threw an uncaught QueryException; 7 CASCADE, so a hard delete on a
 * learner without those rows instead destroyed ledger_entries, refunds and
 * fee_adjustments.
 *
 * Confirmed with the user: removal is reversible, and a re-admitted learner
 * restores the existing record rather than creating a new one.
 */
class StudentSoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'soft-delete@test.local']);
        $this->admin->roles()->sync(Role::where('role_name', 'Super Admin')->pluck('role_id'));
        $this->admin->load('roles.permissions');
    }

    private function makeStudent(string $first = 'Removable'): Student
    {
        return Student::create([
            'admission_no' => 'SD' . substr(uniqid(), -8),
            'first_name' => $first,
            'last_name' => 'Learner',
            'date_of_birth' => '2012-01-01',
            'gender' => 'male',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    private function enrol(Student $student): StudentClassEnrollment
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

        return StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => $classSection->class_section_id,
            'academic_year_id' => $year->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);
    }

    public function test_removing_a_learner_keeps_the_row(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($this->admin)
            ->delete(route('students.destroy', $student->student_id))
            ->assertRedirect(route('students.index'));

        // The row survives; it is only flagged.
        $this->assertNotNull(Student::withTrashed()->find($student->student_id));
        $this->assertNotNull(Student::withTrashed()->find($student->student_id)->deleted_at);

        // ...but is gone from the normal query.
        $this->assertNull(Student::find($student->student_id));
    }

    public function test_a_learner_with_enrollment_history_can_be_removed_without_erroring(): void
    {
        // student_class_enrollments.student_id is ON DELETE RESTRICT, so the old
        // hard delete raised a QueryException here.
        $student = $this->makeStudent();
        $this->enrol($student);

        $this->actingAs($this->admin)
            ->delete(route('students.destroy', $student->student_id))
            ->assertRedirect(route('students.index'));

        $this->assertNotNull(Student::withTrashed()->find($student->student_id));
        $this->assertSame(
            1,
            StudentClassEnrollment::where('student_id', $student->student_id)->count(),
            'A learner\'s enrollment history must survive their removal.'
        );
    }

    public function test_the_active_roster_excludes_removed_learners(): void
    {
        $kept = $this->makeStudent('StaysOnRoster');
        $removed = $this->makeStudent('LeavesRoster');

        $removed->delete();

        $roster = $this->actingAs($this->admin)
            ->get(route('students.index'))
            ->assertOk();

        $roster->assertSee('StaysOnRoster');
        $roster->assertDontSee('LeavesRoster');
    }

    public function test_the_trashed_filter_surfaces_removed_learners_for_restoring(): void
    {
        $removed = $this->makeStudent('WaitingToReturn');
        $removed->delete();

        $this->actingAs($this->admin)
            ->get(route('students.index', ['trashed' => 1]))
            ->assertOk()
            ->assertSee('WaitingToReturn');
    }

    public function test_a_removed_learner_can_be_restored(): void
    {
        $student = $this->makeStudent('ComesBack');
        $student->delete();

        $this->actingAs($this->admin)
            ->post(route('students.restore', $student->student_id))
            ->assertRedirect(route('students.index', ['trashed' => 1]));

        $fresh = Student::find($student->student_id);

        $this->assertNotNull($fresh, 'A restored learner must return to the active roster.');
        $this->assertNull($fresh->deleted_at);
    }

    public function test_restoring_a_learner_who_is_not_removed_is_reported_not_crashed(): void
    {
        $student = $this->makeStudent('StillHere');

        $this->actingAs($this->admin)
            ->post(route('students.restore', $student->student_id))
            ->assertRedirect(route('students.index', ['trashed' => 1]));

        $this->assertNull(Student::find($student->student_id)->deleted_at);
    }

    public function test_readmission_restores_the_same_record_because_the_admission_number_stays_reserved(): void
    {
        $student = $this->makeStudent('Readmitted');
        $admissionNo = $student->admission_no;

        $student->delete();

        // The unique index still holds the value, so re-admitting the same
        // learner must mean restoring, not inserting a duplicate.
        $this->assertSame(
            1,
            Student::withTrashed()->where('admission_no', $admissionNo)->count()
        );

        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('students')->insert([
            'admission_no' => $admissionNo,
            'first_name' => 'Duplicate',
            'last_name' => 'Attempt',
            'date_of_birth' => '2012-01-01',
            'gender' => 'male',
        ]);
    }

    public function test_removal_and_restore_are_audited(): void
    {
        $student = $this->makeStudent('Audited');
        $student->delete();

        $this->actingAs($this->admin)
            ->post(route('students.restore', $student->student_id))
            ->assertRedirect();

        $this->assertSame(
            1,
            DB::table('audit_trails')->where('action', 'RESTORE')->where('module', 'Student')->count()
        );
    }
}
