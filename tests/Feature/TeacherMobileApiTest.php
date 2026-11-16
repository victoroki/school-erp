<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamSchedule;
use App\Models\Homework;
use App\Models\Parents;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use App\Models\StudentParentRelationship;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE 2 — TEACHER DAILY EXPERIENCE: trust fixes on the mobile API.
 *
 * Covers the attendance register lifecycle (historical date preserved,
 * server-side teacher scoping, pending-register accounting that disappears
 * when the register is complete), the homework privacy scoping fix, and the
 * marks/roster join the teacher home depends on.
 */
class TeacherMobileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    // ── Fixtures ────────────────────────────────────────────────────────────

    private function teacherUser(string $name = 'Teacher'): User
    {
        $user = User::factory()->create(['name' => $name]);
        $user->roles()->sync(Role::where('role_name', 'Teacher')->pluck('role_id'));
        $staff = $user->staff()->create([
            'first_name' => $name,
            'last_name' => 'Tester',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone_primary' => '0712345678',
            'work_email' => strtolower(str_replace(' ', '.', $name)) . '.' . uniqid() . '@test.local',
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
        return $user->load('roles.permissions')->setRelation('staff', $staff);
    }

    private function portalUser(string $roleName): User
    {
        $user = User::factory()->create();
        $user->roles()->sync(Role::where('role_name', $roleName)->pluck('role_id'));
        return $user->load('roles.permissions');
    }

    private function year(): AcademicYear
    {
        // Tests create several sections over one school year — firstOrCreate
        // keeps the unique `name` (and single is_current row) intact.
        return AcademicYear::firstOrCreate(
            ['name' => '2026'],
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true]
        );
    }

    private function section(string $className = 'Form 1', string $sectionName = 'A'): array
    {
        $year = $this->year();
        $class = SchoolClass::create(['name' => $className, 'numeric_value' => 1]);
        $section = Section::create(['class_id' => $class->class_id, 'name' => $sectionName]);
        $cs = ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);
        return compact('year', 'class', 'sectionName', 'cs');
    }

    private function enroll($classSection, AcademicYear $year): Student
    {
        $student = Student::create([
            'admission_no' => 'ADM-' . substr(md5(uniqid((string) mt_rand(), true)), 0, 12),
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'date_of_birth' => '2010-01-01',
            'gender' => 'female',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
        ]);
        StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => $classSection->class_section_id,
            'academic_year_id' => $year->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);
        return $student;
    }

    private function mobileToken(User $user): string
    {
        return $user->createToken('mobile', ['mobile:access'])->plainTextToken;
    }

    // ── STEP 6A/6B: date preservation + real remarks + extended statuses ───

    public function test_posting_attendance_for_a_historical_date_creates_that_dates_register(): void
    {
        $teacher = $this->teacherUser();
        ['year' => $year, 'cs' => $cs] = $this->section();
        $student = $this->enroll($cs, $year);
        TeacherSubject::create([
            'staff_id' => $teacher->staff->staff_id,
            'subject_id' => Subject::create(['name' => 'Mathematics', 'subject_code' => 'M1'])->subject_id,
            'class_section_id' => $cs->class_section_id,
            'academic_year_id' => $year->academic_year_id,
        ]);

        $historical = '2026-09-10';

        $res = $this->withToken($this->mobileToken($teacher))->postJson('/api/mobile/attendance', [
            'date' => $historical,
            'records' => [
                ['student_id' => $student->student_id, 'status' => 'present', 'remarks' => 'Arrived with list'],
            ],
        ]);
        $res->assertOk()->assertJson(['ok' => true]);

        // The row landed on the requested date — not "today" — and the remark
        // persisted (previously silently dropped).
        $row = StudentAttendance::where('student_id', $student->student_id)->first();
        $this->assertNotNull($row);
        $this->assertSame($historical, $row->date->toDateString());
        $this->assertSame('Arrived with list', $row->remarks);
        $this->assertSame($teacher->staff->staff_id, $row->marked_by);

        // GET returns the same historical date, now including class_section_id.
        $this->withToken($this->mobileToken($teacher))
            ->getJson("/api/mobile/attendance?date={$historical}")
            ->assertOk()
            ->assertJsonPath('date', $historical)
            ->assertJsonPath('records.0.status', 'present')
            ->assertJsonPath('records.0.remark', 'Arrived with list')
            ->assertJsonPath('records.0.class_section_id', $cs->class_section_id);
    }

    public function test_attendance_accepts_the_full_status_enum_including_excused_and_half_day(): void
    {
        $teacher = $this->teacherUser();
        ['year' => $year, 'cs' => $cs] = $this->section();
        $s1 = $this->enroll($cs, $year);
        $s2 = $this->enroll($cs, $year);
        TeacherSubject::create([
            'staff_id' => $teacher->staff->staff_id,
            'subject_id' => Subject::create(['name' => 'English', 'subject_code' => 'E1'])->subject_id,
            'class_section_id' => $cs->class_section_id,
            'academic_year_id' => $year->academic_year_id,
        ]);

        $this->withToken($this->mobileToken($teacher))->postJson('/api/mobile/attendance', [
            'date' => now()->toDateString(),
            'records' => [
                ['student_id' => $s1->student_id, 'status' => 'excused'],
                ['student_id' => $s2->student_id, 'status' => 'half_day'],
            ],
        ])->assertOk();

        $this->assertSame('excused', StudentAttendance::where('student_id', $s1->student_id)->value('status'));
        $this->assertSame('half_day', StudentAttendance::where('student_id', $s2->student_id)->value('status'));
    }

    public function test_a_teacher_cannot_mark_attendance_for_another_teachers_class(): void
    {
        $teacherA = $this->teacherUser('Amani');
        $teacherB = $this->teacherUser('Beatrice');
        ['year' => $year, 'cs' => $csA] = $this->section('Form 2', 'A');
        ['cs' => $csB] = $this->section('Form 3', 'B');
        $studentOfB = $this->enroll($csB, $year);
        TeacherSubject::create([
            'staff_id' => $teacherB->staff->staff_id,
            'subject_id' => Subject::create(['name' => 'Physics', 'subject_code' => 'P1'])->subject_id,
            'class_section_id' => $csB->class_section_id,
            'academic_year_id' => $year->academic_year_id,
        ]);

        $this->withToken($this->mobileToken($teacherA))->postJson('/api/mobile/attendance', [
            'records' => [['student_id' => $studentOfB->student_id, 'status' => 'present']],
        ])->assertForbidden()->assertJson(['message' => 'You can only mark attendance for your assigned classes.']);

        // And it does not appear in A's register read either.
        $this->withToken($this->mobileToken($teacherA))
            ->getJson('/api/mobile/attendance?date=' . now()->toDateString())
            ->assertOk()
            ->assertJsonCount(0, 'records');
    }

    // ── STEP: pending registers — the authoritative home answer ─────────────

    public function test_pending_registers_requires_a_complete_register_and_disappears_once_complete(): void
    {
        $teacher = $this->teacherUser();
        ['year' => $year, 'cs' => $cs] = $this->section();
        $s1 = $this->enroll($cs, $year);
        $s2 = $this->enroll($cs, $year);
        TeacherSubject::create([
            'staff_id' => $teacher->staff->staff_id,
            'subject_id' => Subject::create(['name' => 'Chemistry', 'subject_code' => 'C1'])->subject_id,
            'class_section_id' => $cs->class_section_id,
            'academic_year_id' => $year->academic_year_id,
        ]);
        $today = now()->toDateString();

        // No attendance at all → the register is pending with every student.
        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/teacher/pending-registers?date=' . $today)
            ->assertOk()
            ->assertJsonPath('pending.0.class_section_id', $cs->class_section_id)
            ->assertJsonPath('pending.0.enrolled', 2)
            ->assertJsonPath('pending.0.marked', 0);

        // A partial register is still pending, with the honest remaining count.
        StudentAttendance::create([
            'student_id' => $s1->student_id,
            'class_section_id' => $cs->class_section_id,
            'date' => $today,
            'status' => 'present',
            'marked_by' => $teacher->staff->staff_id,
        ]);
        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/teacher/pending-registers?date=' . $today)
            ->assertOk()
            ->assertJsonPath('pending.0.marked', 1)
            ->assertJsonPath('pending.0.pending', 1);

        // Complete the register → it must disappear from the pending list.
        StudentAttendance::create([
            'student_id' => $s2->student_id,
            'class_section_id' => $cs->class_section_id,
            'date' => $today,
            'status' => 'absent',
            'marked_by' => $teacher->staff->staff_id,
        ]);
        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/teacher/pending-registers?date=' . $today)
            ->assertOk()
            ->assertJsonCount(0, 'pending');
    }

    public function test_teacher_dashboard_exposes_next_class_and_pending_work(): void
    {
        $teacher = $this->teacherUser();
        ['year' => $year, 'cs' => $cs] = $this->section();
        $this->enroll($cs, $year);
        $subject = Subject::create(['name' => 'Biology', 'subject_code' => 'B1']);
        TeacherSubject::create([
            'staff_id' => $teacher->staff->staff_id,
            'subject_id' => $subject->subject_id,
            'class_section_id' => $cs->class_section_id,
            'academic_year_id' => $year->academic_year_id,
        ]);

        // A lesson later today + a scheduled exam this teacher must mark.
        $period = \App\Models\Period::create(['name' => 'P5', 'start_time' => '22:00:00', 'end_time' => '23:00:00', 'type' => 'period']);
        \App\Models\Timetable::create([
            'class_section_id' => $cs->class_section_id,
            'day_of_week' => strtolower(now()->format('l')),
            'period_id' => $period->period_id,
            'subject_id' => $subject->subject_id,
            'teacher_id' => $teacher->staff->staff_id,
            'academic_year_id' => $year->academic_year_id,
        ]);
        $exam = Exam::create(['name' => 'Mid Term', 'start_date' => now()->subDays(4)->toDateString(), 'end_date' => now()->subDays(1)->toDateString(), 'academic_year_id' => $year->academic_year_id]);
        ExamSchedule::create([
            'exam_id' => $exam->exam_id,
            'class_id' => $cs->class_id,
            'subject_id' => $subject->subject_id,
            'exam_date' => now()->subDays(2)->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'max_marks' => 100,
            'passing_marks' => 50,
        ]);

        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/teacher/dashboard')
            ->assertOk()
            ->assertJsonPath('teacher_name', 'Teacher Tester')
            ->assertJsonPath('next_class.class_section_id', $cs->class_section_id)
            ->assertJsonPath('next_class.end_time', '23:00:00')
            ->assertJsonPath('pending_registers.0.pending', 1)
            ->assertJsonPath('pending_marks.0.missing', 1);

        // Recording the mark clears the grading debt.
        $schedule = ExamSchedule::first();
        ExamResult::create([
            'exam_id' => $exam->exam_id,
            'student_id' => Student::first()->student_id,
            'class_section_id' => $cs->class_section_id,
            'subject_id' => $subject->subject_id,
            'marks_obtained' => 88,
            // exam_results.created_by references staff.staff_id, not users.id.
            'created_by' => $teacher->staff->staff_id,
        ]);

        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/teacher/dashboard')
            ->assertOk()
            ->assertJsonCount(0, 'pending_marks');
        unset($schedule);
    }

    // ── STEP 9: homework privacy + manage permission ────────────────────────

    public function test_homework_create_requires_the_manage_permission(): void
    {
        $parent = $this->portalUser('Parent');

        $this->withToken($this->mobileToken($parent))->postJson('/api/mobile/homework', [
            'title' => 'Sneaky assignment',
            'class_name' => 'Form 1 A',
            'subject' => 'Mathematics',
            'due_date' => now()->addDay()->toDateString(),
        ])->assertForbidden();
    }

    public function test_parents_and_students_only_see_homework_for_their_classes(): void
    {
        ['year' => $year, 'cs' => $cs] = $this->section('Form 1', 'A');
        $student = $this->enroll($cs, $year);

        $teacher = $this->teacherUser();
        TeacherSubject::create([
            'staff_id' => $teacher->staff->staff_id,
            'subject_id' => Subject::create(['name' => 'Geography', 'subject_code' => 'G1'])->subject_id,
            'class_section_id' => $cs->class_section_id,
            'academic_year_id' => $year->academic_year_id,
        ]);
        Homework::create(['created_by' => $teacher->id, 'title' => 'For my class', 'subject' => 'Geography', 'class_name' => 'Form 1 A', 'due_date' => now()->addDays(2), 'status' => 'active']);
        Homework::create(['created_by' => $teacher->id, 'title' => 'For another class', 'subject' => 'Geography', 'class_name' => 'Form 3 R', 'due_date' => now()->addDays(2), 'status' => 'active']);

        // A parent linked to the student.
        $parent = $this->portalUser('Parent');
        $parentRecord = Parents::create([
            'user_id' => $parent->id,
            'first_name' => 'Parent',
            'last_name' => 'Doe',
            'relationship' => 'guardian',
            'phone' => '0722000111',
            'email' => 'parent.' . uniqid() . '@test.local',
        ]);
        StudentParentRelationship::create(['parent_id' => $parentRecord->parent_id, 'student_id' => $student->student_id]);

        $this->withToken($this->mobileToken($parent))
            ->getJson('/api/mobile/homework')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'For my class');

        // A student portal account for the enrolled student.
        $studentUser = $this->portalUser('Student');
        $student->update(['user_id' => $studentUser->id]);
        $this->withToken($this->mobileToken($studentUser))
            ->getJson('/api/mobile/homework')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'For my class');
    }

    public function test_a_teacher_cannot_assign_homework_to_a_class_they_do_not_teach(): void
    {
        $teacher = $this->teacherUser();
        ['year' => $year, 'cs' => $cs] = $this->section('Form 4', 'C');
        TeacherSubject::create([
            'staff_id' => $teacher->staff->staff_id,
            'subject_id' => Subject::create(['name' => 'History', 'subject_code' => 'H1'])->subject_id,
            'class_section_id' => $cs->class_section_id,
            'academic_year_id' => $year->academic_year_id,
        ]);

        $this->withToken($this->mobileToken($teacher))->postJson('/api/mobile/homework', [
            'title' => 'Wrong class',
            'class_name' => 'Form 9 Z',
            'subject' => 'History',
            'due_date' => now()->addDay()->toDateString(),
        ])->assertForbidden();

        $this->withToken($this->mobileToken($teacher))->postJson('/api/mobile/homework', [
            'title' => 'Right class',
            'class_name' => 'Form 4 C',
            'subject' => 'History',
            'due_date' => now()->addDay()->toDateString(),
        ])->assertOk();
    }
}
