<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\ClassSubject;
use App\Models\ExamType;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Attendance marker attribution, academic-journey relation, academic-year
 * scoping of the class-subject bulk clear, and the exam type edit route.
 */
class AttendanceAndAcademicIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['email' => strtolower($role) . '-' . uniqid() . '@test.local']);
        $user->roles()->sync(Role::where('role_name', $role)->pluck('role_id'));

        return $user->load('roles.permissions');
    }

    private function makeStaffFor(User $user): Staff
    {
        return $user->staff()->create([
            'first_name' => 'Staff',
            'middle_name' => null,
            'last_name' => 'Member',
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
            'phone_primary' => '0712000000',
            'work_email' => 'staff.' . uniqid() . '@test.local',
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

    private function makeStudent(int $suffix): Student
    {
        return Student::create([
            'admission_no' => 'AT' . $suffix . substr(uniqid(), -6),
            'first_name' => 'Attend',
            'last_name' => 'Learner' . $suffix,
            'date_of_birth' => '2012-01-01',
            'gender' => 'male',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    private function classSection(): ClassSection
    {
        $year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);
        $class = SchoolClass::create(['name' => 'Grade 5', 'numeric_value' => 5]);
        $section = Section::create(['name' => 'A']);

        return ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);
    }

    // ---------------------------------------------------------------------
    // Attendance marker attribution
    // ---------------------------------------------------------------------

    public function test_attendance_records_the_staff_id_not_the_user_id(): void
    {
        // A Super Admin bypasses the controller's class-scope check; a Teacher
        // is scoped to their own classes and would be redirected instead.
        $marker = $this->userWithRole('Super Admin');

        // Give a DIFFERENT user the first staff row, so the marker's user id
        // points at a staff member who is not them.
        $this->makeStaffFor($this->userWithRole('Teacher'));
        $markerStaff = $this->makeStaffFor($marker);

        $this->assertNotSame(
            (int) $marker->id,
            (int) $markerStaff->staff_id,
            'Test fixture must make the user id and staff id differ, otherwise it proves nothing.'
        );

        $section = $this->classSection();
        $student = $this->makeStudent(1);

        $this->actingAs($marker)
            ->post(route('student-attendance.store'), [
                'class_section_id' => $section->class_section_id,
                'date' => now()->toDateString(),
                'attendance' => [$student->student_id => 'present'],
            ])
            ->assertRedirect();

        $record = StudentAttendance::where('student_id', $student->student_id)->firstOrFail();

        $this->assertSame(
            $markerStaff->staff_id,
            (int) $record->marked_by,
            'marked_by must be the marker\'s staff_id; the user id points at a different staff member.'
        );
    }

    public function test_attendance_marker_is_null_when_the_user_has_no_staff_record(): void
    {
        // The old code wrote auth()->id(), which has no matching staff row and
        // violated student_attendance_ibfk_3. Super Admin so the class-scope
        // check does not redirect before the write.
        $marker = $this->userWithRole('Super Admin');
        $section = $this->classSection();
        $student = $this->makeStudent(2);

        $this->actingAs($marker)
            ->post(route('student-attendance.store'), [
                'class_section_id' => $section->class_section_id,
                'date' => now()->toDateString(),
                'attendance' => [$student->student_id => 'present'],
            ])
            ->assertRedirect();

        $record = StudentAttendance::where('student_id', $student->student_id)->firstOrFail();

        $this->assertNull($record->marked_by);
        $this->assertSame('present', $record->status);
    }

    public function test_attendance_marker_relation_resolves_the_staff_member(): void
    {
        $marker = $this->userWithRole('Teacher');
        $staff = $this->makeStaffFor($marker);

        $section = $this->classSection();
        $student = $this->makeStudent(3);

        StudentAttendance::create([
            'student_id' => $student->student_id,
            'class_section_id' => $section->class_section_id,
            'date' => now()->toDateString(),
            'status' => 'present',
            'marked_by' => $staff->staff_id,
        ]);

        $record = StudentAttendance::where('student_id', $student->student_id)->firstOrFail();

        $this->assertInstanceOf(Staff::class, $record->markedBy);
        $this->assertSame($staff->staff_id, $record->markedBy->staff_id);
    }

    // ---------------------------------------------------------------------
    // Academic journey
    // ---------------------------------------------------------------------

    /**
     * NOT DISCRIMINATING — passes on the unfixed code too; behaviour preservation.
     *
     * Phase 1B reported that this tab crashed because `academic_journey` did not
     * exist. It did: Student::getAcademicJourneyAttribute() has always resolved
     * that property (the audit's literal grep for `academic_journey` missed the
     * camelCase method name). The accessor orders oldest-first, which is the
     * sensible order for a journey timeline. This test documents the working
     * behaviour so a duplicate definition is not added again.
     */
    public function test_academic_journey_accessor_returns_the_enrollment_history(): void
    {
        $student = $this->makeStudent(4);
        $older = $this->classSection();
        $newer = $this->classSection();

        StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => $older->class_section_id,
            'academic_year_id' => $older->academic_year_id,
            'enrollment_date' => '2025-01-10',
            'status' => 'active',
        ]);
        StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => $newer->class_section_id,
            'academic_year_id' => $newer->academic_year_id,
            'enrollment_date' => '2026-01-10',
            'status' => 'active',
        ]);

        $journey = $student->fresh()->academic_journey;

        $this->assertCount(2, $journey);
        $this->assertEqualsCanonicalizing(
            ['2025-01-10', '2026-01-10'],
            $journey->pluck('enrollment_date')->map(fn ($date) => $date?->toDateString())->all()
        );

        // The accessor eager-loads what the timeline renders, so the view does
        // not lazy-load per row.
        $this->assertTrue($journey->first()->relationLoaded('classSection'));
        $this->assertTrue($journey->first()->relationLoaded('academicYear'));
    }

    public function test_the_academic_tab_view_uses_the_existing_accessor(): void
    {
        $blade = file_get_contents(resource_path('views/students/tabs/academic.blade.php'));

        $this->assertStringContainsString('$student->academic_journey', $blade);

        // A duplicate camelCase definition must not be reintroduced.
        $student = file_get_contents(app_path('Models/Student.php'));
        $this->assertStringContainsString('function getAcademicJourneyAttribute', $student);
        $this->assertStringNotContainsString('function academicJourney(', $student);
    }

    // ---------------------------------------------------------------------
    // Class subject bulk clear must stay inside one academic year
    // ---------------------------------------------------------------------

    public function test_clearing_class_subjects_does_not_touch_other_academic_years(): void
    {
        $this->actingAs($this->userWithRole('Super Admin'));

        $class = SchoolClass::create(['name' => 'Grade 8', 'numeric_value' => 8]);
        $subject = Subject::create([
            'name' => 'Mathematics',
            'subject_code' => 'MAT' . substr(uniqid(), -4),
        ]);

        $yearA = AcademicYear::create([
            'name' => 'A-' . uniqid(), 'start_date' => '2025-01-01', 'end_date' => '2025-12-31',
        ]);
        $yearB = AcademicYear::create([
            'name' => 'B-' . uniqid(), 'start_date' => '2026-01-01', 'end_date' => '2026-12-31',
        ]);

        $keep = ClassSubject::create([
            'class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'academic_year_id' => $yearA->academic_year_id,
        ]);
        $remove = ClassSubject::create([
            'class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'academic_year_id' => $yearB->academic_year_id,
        ]);

        $this->post(route('class-subjects.bulk-delete'), [
            'class_id' => $class->class_id,
            'academic_year_id' => $yearB->academic_year_id,
        ])->assertRedirect(route('class-subjects.index'));

        $this->assertNull(
            ClassSubject::find($remove->class_subject_id),
            'The targeted year should have been cleared.'
        );
        $this->assertNotNull(
            ClassSubject::find($keep->class_subject_id),
            'Clearing one year must not delete another year\'s subject assignments.'
        );
    }

    public function test_bulk_clear_without_a_year_scopes_to_the_current_year(): void
    {
        $this->actingAs($this->userWithRole('Super Admin'));

        $class = SchoolClass::create(['name' => 'Grade 9', 'numeric_value' => 9]);
        $subject = Subject::create([
            'name' => 'English',
            'subject_code' => 'ENG' . substr(uniqid(), -4),
        ]);

        $past = AcademicYear::create([
            'name' => 'P-' . uniqid(), 'start_date' => '2024-01-01', 'end_date' => '2024-12-31',
            'is_current' => false,
        ]);
        $current = AcademicYear::create([
            'name' => 'C-' . uniqid(), 'start_date' => '2026-01-01', 'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $historic = ClassSubject::create([
            'class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'academic_year_id' => $past->academic_year_id,
        ]);
        $active = ClassSubject::create([
            'class_id' => $class->class_id,
            'subject_id' => $subject->subject_id,
            'academic_year_id' => $current->academic_year_id,
        ]);

        // No academic_year_id posted (the old form shape).
        $this->post(route('class-subjects.bulk-delete'), [
            'class_id' => $class->class_id,
        ])->assertRedirect(route('class-subjects.index'));

        $this->assertNull(ClassSubject::find($active->class_subject_id));
        $this->assertNotNull(
            ClassSubject::find($historic->class_subject_id),
            'Without an explicit year the clear must fall back to the current year only.'
        );
    }

    // ---------------------------------------------------------------------
    // Exam type edit route
    // ---------------------------------------------------------------------

    public function test_exam_type_edit_form_targets_the_correct_record(): void
    {
        $examType = ExamType::create([
            'name' => 'Opener ' . substr(uniqid(), -4),
            'weight' => 10,
        ]);

        $this->actingAs($this->userWithRole('Super Admin'))
            ->get(route('exam-types.edit', $examType->exam_type_id))
            ->assertOk()
            ->assertSee(route('exam-types.update', $examType->exam_type_id), false);

        $this->assertNotNull($examType->exam_type_id);
    }
}
