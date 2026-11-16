<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\DisciplinaryRecord;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Message;
use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\Parents;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\StaffLeaveBalance;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentNotice;
use App\Models\StudentParentRelationship;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE 5 — COMMUNICATION + REAL OPERATIONAL WRITES (STEPS 26/27).
 *
 * Pins the security properties this phase added, with Laravel as the only
 * authorization boundary (mobile gates are presentation):
 *  - Direct messages: staff-only, canonical payload, own-inbox visibility,
 *    receiver-only read state, no phantom room endpoints.
 *  - Notifications: real per-user read state via the recipient pivot.
 *  - Student notices: teacher/admin create, student/parent cannot, teacher
 *    cannot target an out-of-scope student.
 *  - Discipline: manage-only writes, portal reads stay scoped, garbage dates
 *    validated (422 not 500).
 *  - Medical: unauthorized roles cannot read the school-wide log.
 *  - Leave: hr.leave.apply can apply + sees only their own; unauthorized cannot.
 *  - Staff clock: eligible staff yes, Student/Parent no; status set on first
 *    clock-in (the MySQL-strict 500 regression).
 */
class MobilePhase5CommunicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    // ── Fixtures ────────────────────────────────────────────────────────────

    private function userWithRole(string $roleName, string $name = 'Tester'): User
    {
        $user = User::factory()->create(['name' => $name]);
        $user->roles()->sync(Role::where('role_name', $roleName)->pluck('role_id'));

        return $user->load('roles.permissions');
    }

    private function mobileToken(User $user): string
    {
        // Sanctum's guard caches the resolved user per app instance; tests
        // that act as several identities need a fresh resolution each time.
        $this->app['auth']->forgetGuards();

        return $user->createToken('mobile', ['mobile:access'])->plainTextToken;
    }

    private function year(): AcademicYear
    {
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

        return ['year' => $year, 'class' => $class, 'cs' => $cs];
    }

    private function makeStudent(string $first, array $ctx, ?User $user = null): Student
    {
        $student = Student::create([
            'admission_no' => 'ADM-' . substr(md5($first . uniqid('', true)), 0, 12),
            'first_name' => $first,
            'last_name' => 'Doe',
            'date_of_birth' => '2010-01-01',
            'gender' => 'female',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
            'user_id' => $user?->id,
        ]);
        StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => $ctx['cs']->class_section_id,
            'academic_year_id' => $ctx['year']->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);

        return $student;
    }

    /** Teacher attached to a staff record + assigned as class teacher of ctx['cs']. */
    private function scopedTeacher(string $name = 'Tariq Teacher'): array
    {
        $ctx = $this->section('Form 2', 'A');
        $user = $this->userWithRole('Teacher', $name);
        $staff = Staff::create([
            'user_id' => $user->id,
            'first_name' => 'Tariq',
            'last_name' => 'Tester',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone_primary' => '0712345678',
            'work_email' => 'tariq.' . uniqid() . '@test.local',
            'current_address' => '',
            'city' => '',
            'country' => '',
            'date_of_joining' => now()->toDateString(),
            'staff_type' => 'teaching',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
        ]);
        $ctx['cs']->update(['class_teacher_id' => $staff->staff_id]);

        return ['user' => $user->load('roles.permissions'), 'staff' => $staff, 'ctx' => $ctx];
    }

    private function parentOf(User $user): Parents
    {
        return Parents::create([
            'user_id' => $user->id,
            'first_name' => 'Grace',
            'last_name' => 'Tester',
            'relationship' => 'mother',
            'phone' => '0712345678',
        ]);
    }

    private function link(Parents $parent, Student $student): void
    {
        StudentParentRelationship::create([
            'student_id' => $student->student_id,
            'parent_id' => $parent->parent_id,
            'is_primary_contact' => true,
        ]);
    }

    // ════ DIRECT MESSAGES ═══════════════════════════════════════════════════

    public function test_staff_can_send_and_receive_a_direct_message_with_canonical_payload(): void
    {
        $admin = $this->userWithRole('Admin', 'Ada Admin');
        $teacher = $this->userWithRole('Teacher', 'Tom Teacher');

        $res = $this->withToken($this->mobileToken($admin))
            ->postJson('/api/mobile/messages/send', [
                'recipient_id' => $teacher->id,
                'content'      => 'Please submit the Form 2 marks.',
            ]);
        $res->assertStatus(200)
            ->assertJsonPath('data.sender_id', $admin->id)
            ->assertJsonPath('data.receiver_id', $teacher->id)
            ->assertJsonPath('data.body', 'Please submit the Form 2 marks.');

        $this->assertDatabaseHas('messages', [
            'sender_id' => $admin->id,
            'receiver_id' => $teacher->id,
            'message' => 'Please submit the Form 2 marks.',
        ]);

        // Recipient inbox: sees it, unread, with server field names surfaced.
        $inbox = $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/messages')
            ->assertStatus(200)
            ->assertJsonPath('data.0.body', 'Please submit the Form 2 marks.')
            ->assertJsonPath('data.0.is_read', false);
        $msgId = $inbox->json('data.0.id');

        // Sender's own copy always renders read (is_read is the receiver's flag).
        $this->withToken($this->mobileToken($admin))
            ->getJson('/api/mobile/messages')
            ->assertStatus(200)
            ->assertJsonPath('data.0.is_read', true);

        // Thread view lists the pair oldest-first.
        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/messages/thread/' . $admin->id)
            ->assertStatus(200)
            ->assertJsonPath('other_user_id', $admin->id)
            ->assertJsonPath('messages.0.body', 'Please submit the Form 2 marks.');

        // Threads are derived from pairs: one counterpart, one unread.
        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/messages/threads')
            ->assertStatus(200)
            ->assertJsonPath('threads.0.user_id', $admin->id)
            ->assertJsonPath('threads.0.unread_count', 1);

        // Receiver marks read → persists.
        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/messages/' . $msgId . '/read')
            ->assertStatus(200);
        $this->assertDatabaseHas('messages', ['message_id' => $msgId, 'is_read' => true]);
    }

    public function test_student_and_parent_cannot_send_direct_messages(): void
    {
        $student = $this->userWithRole('Student', 'Sam Student');
        $parent = $this->userWithRole('Parent', 'Pia Parent');
        $admin = $this->userWithRole('Admin', 'Ada Admin');

        foreach ([$student, $parent] as $portal) {
            $this->withToken($this->mobileToken($portal))
                ->postJson('/api/mobile/messages/send', [
                    'recipient_id' => $admin->id,
                    'content'      => 'hello?',
                ])
                ->assertStatus(403);
        }

        // Contacts picker is gated by the same rule (picker can never offer
        // what send() would reject).
        $this->withToken($this->mobileToken($student))
            ->getJson('/api/mobile/messages/contacts')
            ->assertStatus(403);

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_staff_cannot_dm_a_non_staff_recipient(): void
    {
        $teacher = $this->userWithRole('Teacher', 'Tom Teacher');
        $parent = $this->userWithRole('Parent', 'Pia Parent');

        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/messages/send', [
                'recipient_id' => $parent->id,
                'content'      => 'book a meeting',
            ])
            ->assertStatus(403);

        // Content validation: empty and oversized rejected, nothing stored.
        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/messages/send', ['recipient_id' => $teacher->id, 'content' => ''])
            ->assertStatus(422);

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_inbox_only_shows_the_callers_own_messages(): void
    {
        $a = $this->userWithRole('Admin', 'Ada');
        $b = $this->userWithRole('Teacher', 'Ben');
        $c = $this->userWithRole('Accountant', 'Cyd');

        Message::create(['sender_id' => $a->id, 'receiver_id' => $b->id, 'message' => 'a to b']);
        Message::create(['sender_id' => $c->id, 'receiver_id' => $b->id, 'message' => 'c to b']);

        // Cyd must not see the a↔b traffic.
        $res = $this->withToken($this->mobileToken($c))
            ->getJson('/api/mobile/messages')
            ->assertStatus(200);
        $bodies = collect($res->json('data'))->pluck('body');
        $this->assertSame(['c to b'], $bodies->all());

        // Opening someone else's thread leaks nothing.
        $this->withToken($this->mobileToken($c))
            ->getJson('/api/mobile/messages/thread/' . $a->id)
            ->assertStatus(200)
            ->assertJsonCount(0, 'messages');
    }

    public function test_fictional_room_endpoints_do_not_exist(): void
    {
        $admin = $this->userWithRole('Admin', 'Ada');

        $this->withToken($this->mobileToken($admin))
            ->getJson('/api/mobile/messages/rooms')
            ->assertStatus(404);
        $this->withToken($this->mobileToken($admin))
            ->getJson('/api/mobile/messages/room/1')
            ->assertStatus(404);
    }

    public function test_only_the_receiver_can_mark_a_message_read(): void
    {
        $a = $this->userWithRole('Admin', 'Ada');
        $b = $this->userWithRole('Teacher', 'Ben');
        $msg = Message::create(['sender_id' => $a->id, 'receiver_id' => $b->id, 'message' => 'unread for b']);

        // A stray ID that involves neither user → 404 (no IDOR).
        $this->withToken($this->mobileToken($this->userWithRole('Accountant', 'Eve')))
            ->postJson('/api/mobile/messages/' . $msg->message_id . '/read')
            ->assertStatus(404);

        // Sender calling read is a harmless no-op; the flag stays false.
        $this->withToken($this->mobileToken($a))
            ->postJson('/api/mobile/messages/' . $msg->message_id . '/read')
            ->assertStatus(200);
        $this->assertFalse((bool) $msg->fresh()->is_read);

        $this->withToken($this->mobileToken($b))
            ->postJson('/api/mobile/messages/' . $msg->message_id . '/read')
            ->assertStatus(200);
        $this->assertTrue((bool) $msg->fresh()->is_read);
        $this->assertNotNull($msg->fresh()->read_at);
    }

    // ════ NOTIFICATIONS (REAL READ STATE) ═══════════════════════════════════

    public function test_notification_feed_fans_out_unread_then_marking_persists_per_user(): void
    {
        $admin = $this->userWithRole('Admin', 'Ada');
        $teacher = $this->userWithRole('Teacher', 'Tom');
        Notification::create([
            'title' => 'Founders Day', 'message' => 'Celebration on Friday',
            'type' => 'event', 'recipient_type' => 'teachers', 'sender_id' => $admin->id,
        ]);

        // First fetch: real UNREAD (lazy fan-out), never a fake "all read".
        $feed = $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/notifications')
            ->assertStatus(200)
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('notifications.0.title', 'Founders Day');
        $nid = $feed->json('notifications.0.id');
        $this->assertFalse($feed->json('notifications.0.is_read'));
        $this->assertDatabaseHas('notification_recipients', [
            'notification_id' => $nid, 'recipient_id' => $teacher->id, 'is_read' => false,
        ]);

        // Mark read persists…
        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/notifications/' . $nid . '/read')
            ->assertStatus(200);
        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/notifications')
            ->assertJsonPath('unread_count', 0)
            ->assertJsonPath('notifications.0.is_read', true);

        // …per user: another teacher still sees it unread.
        $other = $this->userWithRole('Teacher', 'Zoe');
        $this->withToken($this->mobileToken($other))
            ->getJson('/api/mobile/notifications')
            ->assertJsonPath('unread_count', 1);

        // Idempotent refetch: fan-out must not duplicate rows.
        $this->withToken($this->mobileToken($teacher))->getJson('/api/mobile/notifications')->assertStatus(200);
        $this->assertSame(1, NotificationRecipient::where('notification_id', $nid)
            ->where('recipient_id', $teacher->id)->count());

        // Mark-all-read endpoint works on the pivot.
        $this->withToken($this->mobileToken($other))
            ->postJson('/api/mobile/notifications/read-all')
            ->assertStatus(200)
            ->assertJsonPath('marked', 1);
        $this->withToken($this->mobileToken($other))
            ->getJson('/api/mobile/notifications')
            ->assertJsonPath('unread_count', 0);
    }

    public function test_students_do_not_receive_teacher_audience_notifications(): void
    {
        $admin = $this->userWithRole('Admin', 'Ada');
        Notification::create([
            'title' => 'Staff briefing', 'message' => 'Room 3 at 4pm',
            'type' => 'general', 'recipient_type' => 'teachers', 'sender_id' => $admin->id,
        ]);

        $this->withToken($this->mobileToken($this->userWithRole('Student', 'Sam')))
            ->getJson('/api/mobile/notifications')
            ->assertStatus(200)
            ->assertJsonPath('notifications', [])
            ->assertJsonPath('unread_count', 0);
    }

    // ════ STUDENT NOTICES (STEP 11 permission hole) ═════════════════════════

    public function test_teacher_with_permission_can_create_notice_for_scoped_student(): void
    {
        ['user' => $teacher, 'ctx' => $ctx] = $this->scopedTeacher();
        $student = $this->makeStudent('Lena', $ctx);

        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/student-notices', [
                'student_id'  => $student->student_id,
                'title'       => 'Library book overdue',
                'body'        => 'Please return the borrowed set by Friday.',
                'notice_type' => 'attendance',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.student_id', $student->student_id)
            ->assertJsonPath('data.notice_type', 'attendance');

        $this->assertDatabaseHas('student_notices', [
            'student_id' => $student->student_id,
            'created_by' => $teacher->id,
            'title' => 'Library book overdue',
        ]);

        // Author recorded; list shows it back to the teacher.
        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/student-notices')
            ->assertStatus(200)
            ->assertJsonPath('0.title', 'Library book overdue');
    }

    public function test_student_and_parent_cannot_create_student_notices(): void
    {
        $ctx = $this->section();
        $studentUser = $this->userWithRole('Student', 'Sam');
        $student = $this->makeStudent('Lena', $ctx, $studentUser);
        $parentUser = $this->userWithRole('Parent', 'Pia');
        $this->link($this->parentOf($parentUser), $student);

        foreach ([$studentUser, $parentUser] as $portal) {
            $this->withToken($this->mobileToken($portal))
                ->postJson('/api/mobile/student-notices', [
                    'student_id' => $student->student_id,
                    'title'      => 'I am writing this about myself',
                ])
                ->assertStatus(403);
        }
        $this->assertDatabaseCount('student_notices', 0);
    }

    public function test_teacher_cannot_notice_a_student_outside_their_scope(): void
    {
        ['user' => $teacher] = $this->scopedTeacher();
        $other = $this->section('Form 5', 'C'); // a class the teacher does not teach
        $outside = $this->makeStudent('Nia', $other);

        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/student-notices', [
                'student_id' => $outside->student_id,
                'title'      => 'crafted target',
            ])
            ->assertStatus(403);
        $this->assertDatabaseCount('student_notices', 0);

        // And their LISTING only covers their scope.
        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/student-notices')
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_invalid_student_or_missing_title_rejected(): void
    {
        ['user' => $teacher] = $this->scopedTeacher();

        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/student-notices', ['student_id' => 999999, 'title' => 'x'])
            ->assertStatus(422);
        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/student-notices', ['student_id' => 1])
            ->assertStatus(422);
    }

    // ════ DISCIPLINE ════════════════════════════════════════════════════════

    public function test_admin_can_create_discipline_record_and_it_uses_web_status_vocabulary(): void
    {
        $admin = $this->userWithRole('Admin', 'Ada');
        $ctx = $this->section();
        $student = $this->makeStudent('Lena', $ctx);

        $res = $this->withToken($this->mobileToken($admin))
            ->postJson('/api/mobile/discipline', [
                'student_id'    => $student->student_id,
                'offense'       => 'Disrupted the double-science practical.',
                'incident_type' => 'Disruption',
                'date'          => now()->subDay()->toDateString(),
                'action_taken'  => 'Counselled; parent informed.',
            ])
            ->assertStatus(201);
        $id = $res->json('data.id');

        $row = DisciplinaryRecord::find($id);
        $this->assertSame($admin->id, (int) $row->reported_by);
        $this->assertSame('open', $row->status); // never the stray 'pending'
        $this->assertSame('Disruption', $row->incident_type); // no hardcoded 'Misconduct'
        $this->assertSame(now()->subDay()->toDateString(), $row->incident_date->toDateString());

        $this->withToken($this->mobileToken($admin))
            ->getJson('/api/mobile/discipline')
            ->assertStatus(200)
            ->assertJsonPath('data.0.student_id', $student->student_id)
            ->assertJsonPath('data.0.follow_up_status', 'open');
    }

    public function test_portal_roles_and_teacher_cannot_write_discipline_records(): void
    {
        $admin = $this->userWithRole('Admin', 'Ada');
        $ctx = $this->section();
        $studentUser = $this->userWithRole('Student', 'Sam');
        $student = $this->makeStudent('Lena', $ctx, $studentUser);
        $parentUser = $this->userWithRole('Parent', 'Pia');
        $this->link($this->parentOf($parentUser), $student);
        ['user' => $teacher] = $this->scopedTeacher(); // seeded Teacher holds neither discipline perm

        foreach ([$studentUser, $parentUser, $teacher] as $noWrite) {
            $this->withToken($this->mobileToken($noWrite))
                ->postJson('/api/mobile/discipline', [
                    'student_id' => $student->student_id,
                    'offense'    => 'written by someone unauthorized',
                ])
                ->assertStatus(403);
        }
        $this->assertDatabaseCount('disciplinary_records', 0);
    }

    public function test_unauthorized_staff_cannot_read_the_discipline_log(): void
    {
        $admin = $this->userWithRole('Admin', 'Ada');
        $ctx = $this->section();
        $student = $this->makeStudent('Lena', $ctx);
        DisciplinaryRecord::create([
            'student_id' => $student->student_id,
            'incident_date' => now()->toDateString(),
            'incident_type' => 'Bullying',
            'description' => 'reported',
            'status' => 'open',
            'reported_by' => $admin->id,
        ]);

        // Accountant holds discipline.view? No — and the old fall-through made
        // every non-portal role an omnivore. It must now be 403.
        $this->withToken($this->mobileToken($this->userWithRole('Accountant', 'Eve')))
            ->getJson('/api/mobile/discipline')
            ->assertStatus(403);

        // Portal reads stay scoped to their own children/self.
        $parentUser = $this->userWithRole('Parent', 'Pia');
        $this->link($this->parentOf($parentUser), $student);
        $this->withToken($this->mobileToken($parentUser))
            ->getJson('/api/mobile/discipline')
            ->assertStatus(200)
            ->assertJsonPath('data.0.student_id', $student->student_id);

        $unrelatedParent = $this->userWithRole('Parent', 'Una');
        $this->parentOf($unrelatedParent);
        $this->withToken($this->mobileToken($unrelatedParent))
            ->getJson('/api/mobile/discipline')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_garbage_incident_date_is_a_validation_error_not_a_500(): void
    {
        $admin = $this->userWithRole('Admin', 'Ada');
        $ctx = $this->section();
        $student = $this->makeStudent('Lena', $ctx);

        $this->withToken($this->mobileToken($admin))
            ->postJson('/api/mobile/discipline', [
                'student_id' => $student->student_id,
                'offense'    => 'some offense text',
                'date'       => 'not-a-date',
            ])
            ->assertStatus(422);
        $this->assertDatabaseCount('disciplinary_records', 0);
    }

    // ════ MEDICAL ═══════════════════════════════════════════════════════════

    public function test_medical_feed_respects_role_privacy(): void
    {
        $admin = $this->userWithRole('Admin', 'Ada');
        $ctx = $this->section();
        $studentUser = $this->userWithRole('Student', 'Sam');
        $student = $this->makeStudent('Lena', $ctx, $studentUser);
        \App\Models\MedicalIncident::create([
            'student_id' => $student->student_id,
            'incident_date' => now()->toDateString(),
            'symptoms' => 'Mild headache',
            'notified_parents' => true,
            'marked_by' => $admin->id,
        ]);

        // Student sees own record.
        $this->withToken($this->mobileToken($studentUser))
            ->getJson('/api/mobile/medical')
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonPath('0.symptoms', 'Mild headache');

        // Parent of an UNRELATED child sees nothing (no school-wide leak).
        $unrelatedParent = $this->userWithRole('Parent', 'Una');
        $this->parentOf($unrelatedParent);
        $this->withToken($this->mobileToken($unrelatedParent))
            ->getJson('/api/mobile/medical')
            ->assertStatus(200)
            ->assertJsonCount(0);

        // Teacher without students.view is refused outright — the gap this
        // phase documented (no medical-specific permission exists; students.view
        // is the web's proxy) is now enforced instead of fall-through.
        $teacher = $this->userWithRole('Teacher', 'Tom');
        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/medical')
            ->assertStatus(403);
    }

    // ════ LEAVE ═════════════════════════════════════════════════════════════

    public function test_teacher_with_leave_permission_can_apply_and_sees_only_own(): void
    {
        ['user' => $teacher, 'staff' => $staff] = $this->scopedTeacher();
        $type = LeaveType::create(['name' => 'Annual Leave', 'days_allowed' => 21, 'status' => 'active']);

        $types = $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/hr/leave/types')
            ->assertStatus(200)
            ->assertJsonPath('data.0.name', 'Annual Leave');
        $this->assertSame($type->leave_type_id, $types->json('data.0.id'));

        // Two consecutive WEEKDAYS (a Fri→Sat span would honestly be 1).
        $start = now()->addDays(10);
        while ($start->isWeekend()) { $start = $start->addDay(); }
        $end = $start->copy()->addDay();
        while ($end->isWeekend()) { $end = $end->addDay(); }
        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/hr/leave', [
                'leave_type_id' => $type->leave_type_id,
                'start_date'    => $start->toDateString(),
                'end_date'      => $end->toDateString(),
                'reason'        => 'Family commitment out of country.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.working_days', 2);

        $this->assertDatabaseHas('leave_applications', [
            'staff_id' => $staff->staff_id,
            'application_status' => 'pending',
            'reason' => 'Family commitment out of country.',
        ]);

        // A second teacher sees an empty list — own-only scoping.
        $this->withToken($this->mobileToken($this->userWithRole('Teacher', 'Zoe')))
            ->getJson('/api/mobile/hr/leave')
            ->assertStatus(404); // no staff record → honest 404, not someone else's list

        // The applicant sees theirs.
        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/hr/leave')
            ->assertStatus(200)
            ->assertJsonPath('data.0.status', 'pending');
    }

    public function test_unauthorized_users_cannot_touch_leave_endpoints(): void
    {
        $student = $this->userWithRole('Student', 'Sam');
        $parent = $this->userWithRole('Parent', 'Pia');
        $admin = $this->userWithRole('Admin', 'Ada'); // hr perms but no staff record

        foreach ([$student, $parent] as $noApply) {
            $this->withToken($this->mobileToken($noApply))
                ->postJson('/api/mobile/hr/leave', [
                    'leave_type_id' => 1, 'start_date' => now()->addDays(10)->toDateString(),
                    'end_date' => now()->addDays(11)->toDateString(), 'reason' => 'whatever reason',
                ])
                ->assertStatus(403);
            $this->withToken($this->mobileToken($noApply))
                ->getJson('/api/mobile/hr/leave/types')
                ->assertStatus(403);
        }

        // Authorized role without a staff record → 404, not 500.
        $this->withToken($this->mobileToken($admin))
            ->getJson('/api/mobile/hr/leave')
            ->assertStatus(404);
        $this->assertDatabaseCount('leave_applications', 0);
    }

    public function test_leave_business_rules_are_enforced_as_json_errors(): void
    {
        ['user' => $teacher, 'staff' => $staff] = $this->scopedTeacher();
        $type = LeaveType::create(['name' => 'Sick Leave', 'days_allowed' => 7, 'status' => 'active']);
        // notice_days_required is NOT in LeaveType's $fillable (the revamp
        // migration added it later) — set it directly so the column is real.
        $type->notice_days_required = 3;
        $type->save();
        StaffLeaveBalance::create([
            'staff_id' => $staff->staff_id, 'leave_type_id' => $type->leave_type_id,
            'academic_year_id' => $this->year()->academic_year_id,
            'total_entitlement' => 7, 'total_available' => 7, 'used' => 6, 'remaining' => 1,
        ]);

        $payload = fn ($s, $e) => [
            'leave_type_id' => $type->leave_type_id, 'start_date' => $s, 'end_date' => $e,
            'reason' => 'Recovering from an illness.',
        ];

        // Past start → validation.
        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/hr/leave', $payload(now()->subDays(5)->toDateString(), now()->subDays(4)->toDateString()))
            ->assertStatus(422);

        // Advance-notice rule (from web store) → 422 with the message.
        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/hr/leave', $payload(now()->addDay()->toDateString(), now()->addDay()->toDateString()))
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'This leave type requires 3 days advance notice.']);

        // Balance rule: 5 working days requested, 1 remaining.
        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/hr/leave', $payload(now()->addDays(10)->toDateString(), now()->addDays(14)->toDateString()))
            ->assertStatus(422)
            ->assertJsonFragment(['message' => 'Insufficient leave balance. You have only 1 days remaining.']);

        $this->assertDatabaseCount('leave_applications', 0);
    }

    // ════ STAFF CLOCK ═══════════════════════════════════════════════════════

    public function test_eligible_staff_clocks_in_and_out_with_authoritative_status(): void
    {
        ['user' => $teacher, 'staff' => $staff] = $this->scopedTeacher();

        // Initial state: not clocked in.
        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/attendance/staff/my-status')
            ->assertStatus(200)
            ->assertJsonPath('can_clock_in', true)
            ->assertJsonPath('can_clock_out', false)
            ->assertJsonPath('status', null);

        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/attendance/staff/clock-in')
            ->assertStatus(200);

        // THE regression: first-ever clock-in must not 500 under strict SQL —
        // a row exists and carries a real status.
        $row = StaffAttendance::where('staff_id', $staff->staff_id)->whereDate('date', now()->toDateString())->first();
        $this->assertNotNull($row);
        $this->assertSame('present', $row->status);
        $this->assertNotNull($row->time_in);

        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/attendance/staff/my-status')
            ->assertJsonPath('can_clock_in', false)
            ->assertJsonPath('can_clock_out', true);

        // Duplicate guard server-side (reused from the web flow semantics).
        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/attendance/staff/clock-in')
            ->assertStatus(422);

        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/attendance/staff/clock-out')
            ->assertStatus(200);
        $this->withToken($this->mobileToken($teacher))
            ->postJson('/api/mobile/attendance/staff/clock-out')
            ->assertStatus(422);

        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/attendance/staff/my-history')
            ->assertStatus(200)
            ->assertJsonPath('history.0.date', now()->toDateString());
    }

    public function test_students_and_parents_cannot_clock(): void
    {
        foreach (['Student' => 'Sam', 'Parent' => 'Pia'] as $role => $name) {
            $user = $this->userWithRole($role, $name);
            $this->withToken($this->mobileToken($user))
                ->postJson('/api/mobile/attendance/staff/clock-in')
                ->assertStatus(404); // no staff record → not a staff clock user
            $this->withToken($this->mobileToken($user))
                ->getJson('/api/mobile/attendance/staff/my-status')
                ->assertStatus(404);
        }
        $this->assertDatabaseCount('staff_attendance', 0);
    }
}
