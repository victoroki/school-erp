<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\FeeCategory;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use App\Models\StudentFeeAssignment;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE 3 — MOBILE FEE COLLECTION: idempotency + money correctness.
 *
 * These tests are the guard for the non-negotiable part of the phase: the
 * server is the final authority on whether a payment exists. A lost response
 * after a successful insert MUST be retryable without double-charging, the
 * accountant's intended payment date MUST survive an offline sync, and no
 * response may claim success without a real server-issued RCP- receipt.
 */
class MobileFeeIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    // ── Fixtures ────────────────────────────────────────────────────────────

    private function portalUser(string $roleName): User
    {
        $user = User::factory()->create();
        $user->roles()->sync(Role::where('role_name', $roleName)->pluck('role_id'));
        if (in_array($roleName, ['Accountant', 'Admin'], true)) {
            $user->staff()->create([
                'first_name' => $roleName,
                'last_name' => 'Tester',
                'date_of_birth' => '1990-01-01',
                'gender' => 'female',
                'phone_primary' => '0712340000',
                'work_email' => strtolower($roleName) . '.tester.' . uniqid() . '@test.local',
                'personal_email' => null,
                'current_address' => '',
                'city' => '',
                'country' => '',
                'employee_number' => null,
                'tsc_number' => null,
                'designation' => null,
                'qualification' => null,
                'date_of_joining' => now()->toDateString(),
                'staff_type' => 'non-teaching',
                'employment_type' => 'full_time',
                'employment_status' => 'active',
            ]);
        }

        return $user->load('roles.permissions');
    }

    private function mobileToken(User $user): string
    {
        return $user->createToken('mobile', ['mobile:access'])->plainTextToken;
    }

    private function studentWithFees(float ...$finalAmounts): Student
    {
        $year = AcademicYear::firstOrCreate(
            ['name' => '2026'],
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true]
        );
        $class = SchoolClass::create(['name' => 'Form 1', 'numeric_value' => 1]);
        $section = Section::create(['class_id' => $class->class_id, 'name' => 'A']);
        $cs = ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);

        $student = Student::create([
            'admission_no' => 'ADM-' . substr(md5((string) uniqid('', true)), 0, 12),
            'first_name' => 'Mary',
            'last_name' => 'Wanjiru',
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
            'class_section_id' => $cs->class_section_id,
            'academic_year_id' => $year->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);

        foreach ($finalAmounts as $i => $amount) {
            $structure = FeeStructure::create([
                'academic_year_id' => $year->academic_year_id,
                'class_id' => $class->class_id,
                'category_id' => FeeCategory::create(['name' => 'Cat-' . uniqid() . $i, 'type' => 'mandatory'])->category_id,
                'amount' => $amount,
                'term' => 'Term 1',
                'payment_frequency' => 'termly',
                'due_date' => now()->addDays(30),
                'status' => 'active',
            ]);
            StudentFeeAssignment::create([
                'student_id' => $student->student_id,
                'fee_structure_id' => $structure->fee_structure_id,
                'academic_year_id' => $year->academic_year_id,
                'term' => 'Term 1',
                'amount' => $amount,
                'final_amount' => $amount,
                'paid_amount' => 0,
                'assigned_date' => now()->subDays(40 - $i), // oldest-first ordering is meaningful
                'status' => 'active',
            ]);
        }

        return $student;
    }

    private function collectPayload(User $accountant, Student $student, array $overrides = []): array
    {
        return array_merge([
            'student_id' => $student->student_id,
            'amount'     => 5000,
            'method'     => 'cash',
            'client_uuid' => '11111111-2222-3333-4444-555555555555',
        ], $overrides);
    }

    // ── Happy path: server ack with a real receipt ─────────────────────────

    public function test_collect_records_payment_through_the_ledger_path_and_returns_a_real_receipt(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(10000);

        $res = $this->withToken($this->mobileToken($accountant))
            ->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student));

        $res->assertOk()
            ->assertJson(['ok' => true, 'duplicate' => false])
            ->assertJsonPath('receipt_no', fn ($v) => is_string($v) && str_starts_with($v, 'RCP-'))
            ->assertJsonPath('amount', 5000)
            ->assertJsonPath('method', 'cash');

        // Server-side truth: exactly one payment row, real method stored,
        // client_reference persisted, assignment paid_amount updated.
        $this->assertSame(1, FeePayment::count());
        $payment = FeePayment::first();
        $this->assertSame('11111111-2222-3333-4444-555555555555', $payment->client_reference);
        $this->assertSame('cash', $payment->payment_method);
        $this->assertStringStartsWith('RCP-', $payment->receipt_number);
        $this->assertSame('5000.00', (string) StudentFeeAssignment::where('student_id', $student->student_id)->value('paid_amount'));

        // Ledger entries exist (web-parity: this is what the old collect() skipped).
        $this->assertDatabaseHas('ledger_entries', [
            'student_id' => $student->student_id,
            'entry_type' => 'payment',
            'reference_id' => $payment->payment_id,
        ]);
        $this->assertDatabaseHas('payment_allocations', [
            'payment_id' => $payment->payment_id,
            'amount' => 5000,
        ]);
    }

    // ── STEP 10: idempotency (the non-negotiable) ───────────────────────────

    public function test_the_same_client_uuid_twice_creates_only_one_payment(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(10000);
        $token = $this->mobileToken($accountant);

        $first = $this->withToken($token)->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student));
        $first->assertOk()->assertJson(['ok' => true, 'duplicate' => false]);

        // Retry with the SAME client UUID (the lost-response-retry scenario).
        $second = $this->withToken($token)->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student));
        $second->assertOk()
            ->assertJson(['ok' => true, 'duplicate' => true])
            ->assertJsonPath('receipt_no', $first->json('receipt_no'))
            ->assertJsonPath('payment_id', $first->json('payment_id'));

        // The whole point: one KSh 5,000 payment, not two KSh 10,000.
        $this->assertSame(1, FeePayment::count());
        $this->assertSame('5000.00', (string) StudentFeeAssignment::where('student_id', $student->student_id)->value('paid_amount'));
    }

    public function test_a_retry_after_a_lost_response_can_replay_by_client_reference_lookup(): void
    {
        // Simulates: server inserted the row, the response never arrived, the
        // outbox flushes again. The replay must be indistinguishable from the
        // original success apart from `duplicate: true`.
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(10000);

        // Pre-existing payment with the client reference (as if accepted earlier).
        $assignment = StudentFeeAssignment::where('student_id', $student->student_id)->first();
        $payment = FeePayment::create([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 5000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'receipt_number' => 'RCP-PREEXISTING',
            'client_reference' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
        ]);

        $res = $this->withToken($this->mobileToken($accountant))->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student, [
            'client_uuid' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
        ]));

        $res->assertOk()
            ->assertJson(['duplicate' => true, 'receipt_no' => 'RCP-PREEXISTING', 'payment_id' => $payment->payment_id]);
        $this->assertSame(1, FeePayment::count());
    }

    public function test_different_client_uuids_are_two_legitimate_payments(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(10000);
        $token = $this->mobileToken($accountant);

        $this->withToken($token)->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student, ['amount' => 3000]))
            ->assertOk()->assertJson(['duplicate' => false]);
        $this->withToken($token)->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student, [
            'amount' => 2000,
            'client_uuid' => '99999999-8888-7777-6666-555555555555',
        ]))->assertOk()->assertJson(['duplicate' => false]);

        $this->assertSame(2, FeePayment::count());
        $this->assertSame('5000.00', (string) StudentFeeAssignment::where('student_id', $student->student_id)->value('paid_amount'));
    }

    public function test_client_uuid_is_required(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(10000);

        $payload = $this->collectPayload($accountant, $student);
        unset($payload['client_uuid']);

        $this->withToken($this->mobileToken($accountant))
            ->postJson('/api/mobile/fees/collect', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['client_uuid']);

        $this->assertSame(0, FeePayment::count());
    }

    // ── STEP 11: paid_at preservation ───────────────────────────────────────

    public function test_paid_at_from_the_client_is_preserved_not_replaced_with_sync_time(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(10000);

        // An offline payment collected 3 days ago, synced today.
        $intended = now()->subDays(3)->toDateString();
        $this->withToken($this->mobileToken($accountant))
            ->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student, ['paid_at' => $intended . 'T14:30:00']))
            ->assertOk()
            ->assertJsonPath('paid_at', $intended);

        $this->assertSame($intended, FeePayment::first()->payment_date->toDateString());
    }

    public function test_future_paid_at_is_rejected(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(10000);

        $this->withToken($this->mobileToken($accountant))
            ->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student, [
                'paid_at' => now()->addDays(2)->toDateString(),
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['paid_at']);

        $this->assertSame(0, FeePayment::count());
    }

    public function test_missing_paid_at_defaults_to_server_today(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(10000);

        $this->withToken($this->mobileToken($accountant))
            ->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student))
            ->assertOk()
            ->assertJsonPath('paid_at', now()->toDateString());
    }

    // ── Method enum: only values the backend actually supports ──────────────

    public function test_unsupported_payment_methods_are_rejected(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(10000);

        // The exact values Phase 2 mobile sent — each would have been silently
        // coerced to '' by the MySQL enum (the live DB shows 209 such rows).
        foreach (['mpesa', 'cheque', 'bank', 'm-pesa'] as $bad) {
            $this->withToken($this->mobileToken($accountant))
                ->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student, [
                    'method' => $bad,
                    'client_uuid' => 'uuid-' . md5($bad),
                ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['method']);
        }
        $this->assertSame(0, FeePayment::count());
    }

    public function test_every_supported_method_round_trips(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(100000);

        foreach (FeePayment::PAYMENT_METHODS as $i => $method) {
            $this->withToken($this->mobileToken($accountant))
                ->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student, [
                    'amount' => 1000,
                    'method' => $method,
                    'client_uuid' => sprintf('method-%02d-%s', $i, str_repeat('a', 20)),
                ]))
                ->assertOk()
                ->assertJsonPath('method', $method);
        }

        // None coerced to ''.
        $this->assertSame(5, FeePayment::whereIn('payment_method', FeePayment::PAYMENT_METHODS)->count());
    }

    // ── Invalid amount ──────────────────────────────────────────────────────

    public function test_amount_must_be_positive(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(10000);

        foreach ([0, -500] as $amount) {
            $this->withToken($this->mobileToken($accountant))
                ->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student, [
                    'amount' => $amount,
                    'client_uuid' => 'amount-' . md5((string) $amount),
                ]))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(['amount']);
        }
        $this->assertSame(0, FeePayment::count());
    }

    public function test_collecting_beyond_the_outstanding_balance_is_rejected_like_the_web_form(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(3000);

        // Web form blocks amount > balance; mobile stays consistent (STEP 12).
        $this->withToken($this->mobileToken($accountant))
            ->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student, [
                'amount' => 5000,
                'client_uuid' => 'overpay-' . str_repeat('b', 20),
            ]))
            ->assertStatus(422)
            ->assertJsonPath('balance', 3000);

        $this->assertSame(0, FeePayment::count());
    }

    public function test_collecting_for_a_student_with_no_balance_returns_422(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(2000);

        $this->withToken($this->mobileToken($accountant))
            ->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student, ['amount' => 2000]))
            ->assertOk();

        $this->withToken($this->mobileToken($accountant))
            ->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student, [
                'amount' => 2000,
                'client_uuid' => 'second-' . str_repeat('c', 20),
            ]))
            ->assertStatus(422)
            ->assertJsonPath('message', 'No outstanding fees for this student.');
    }

    // ── STEP 19: server-enforced authorization ──────────────────────────────

    public function test_teacher_parent_student_cannot_collect_fees(): void
    {
        $student = $this->studentWithFees(10000);
        $payload = $this->collectPayload($this->portalUser('Accountant'), $student);

        foreach (['Teacher', 'Parent', 'Student'] as $role) {
            $user = $this->portalUser($role);
            $this->withToken($this->mobileToken($user))
                ->postJson('/api/mobile/fees/collect', $payload)
                ->assertForbidden();
        }

        $this->assertSame(0, FeePayment::count());
    }

    public function test_anonymous_cannot_collect_fees(): void
    {
        // Separate test method: withToken() sets default headers for the
        // whole test, so a leaked bearer token would turn the 401 into 403.
        $student = $this->studentWithFees(10000);

        $this->postJson('/api/mobile/fees/collect', $this->collectPayload($this->portalUser('Accountant'), $student))
            ->assertUnauthorized();
        $this->assertSame(0, FeePayment::count());
    }

    public function test_fees_view_endpoints_require_fees_view_and_scope_students(): void
    {
        $student = $this->studentWithFees(10000);
        $teacher = $this->portalUser('Teacher');

        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/fees/students')->assertForbidden();
        $this->withToken($this->mobileToken($teacher))
            ->getJson("/api/mobile/fees/student/{$student->student_id}")->assertForbidden();
        $this->withToken($this->mobileToken($teacher))
            ->getJson("/api/mobile/fees/student/{$student->student_id}/history")->assertForbidden();
    }

    // ── Fee-oriented student search ─────────────────────────────────────────

    public function test_fee_student_search_returns_fee_positions_without_private_fields(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(10000, 6000);
        $token = $this->mobileToken($accountant);

        $this->withToken($token)->getJson('/api/mobile/fees/students?search=mary')
            ->assertOk()
            ->assertJsonPath('students.0.student_id', $student->student_id)
            ->assertJsonPath('students.0.name', 'Mary Wanjiru')
            ->assertJsonPath('students.0.total_assigned', 16000)
            ->assertJsonPath('students.0.balance', 16000)
            ->assertJsonPath('students.0.fee_status', 'unpaid');

        // Admission-no search, and the response carries NOTHING beyond the
        // fee workflow fields (no phone, DOB, guardian...).
        $res = $this->withToken($token)->getJson('/api/mobile/fees/students?search=' . urlencode($student->admission_no));
        $res->assertOk()->assertJsonCount(1, 'students');
        $row = $res->json('students.0');
        $this->assertEqualsCanonicalizing(
            ['student_id', 'admission_no', 'name', 'total_assigned', 'total_paid', 'balance', 'fee_status'],
            array_keys($row)
        );

        // A payment moves the position and the status flips to partial.
        $this->withToken($token)->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student, ['amount' => 4000]))->assertOk();
        $this->withToken($token)->getJson('/api/mobile/fees/students?search=mary')
            ->assertOk()
            ->assertJsonPath('students.0.total_paid', 4000)
            ->assertJsonPath('students.0.balance', 12000)
            ->assertJsonPath('students.0.fee_status', 'partial');
    }

    // ── History endpoint regression: broken relation fixed ──────────────────

    public function test_payment_history_returns_real_receipts(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(10000);
        $token = $this->mobileToken($accountant);

        // This endpoint 500'd on every call before the relation fix.
        $this->withToken($token)->getJson("/api/mobile/fees/student/{$student->student_id}/history")
            ->assertOk()->assertJsonCount(0, 'payments');

        $this->withToken($token)->postJson('/api/mobile/fees/collect', $this->collectPayload($accountant, $student))
            ->assertOk();

        $this->withToken($token)->getJson("/api/mobile/fees/student/{$student->student_id}/history")
            ->assertOk()
            ->assertJsonCount(1, 'payments')
            ->assertJsonPath('payments.0.amount', 5000)
            ->assertJsonPath('payments.0.method', 'cash')
            ->assertJsonPath('payments.0.receipt_no', FeePayment::first()->receipt_number);
    }

    public function test_detailed_endpoint_exposes_working_assignment_ids(): void
    {
        $accountant = $this->portalUser('Accountant');
        $student = $this->studentWithFees(7000);

        $res = $this->withToken($this->mobileToken($accountant))
            ->getJson("/api/mobile/fees/student/{$student->student_id}");

        $res->assertOk()->assertJsonCount(1, 'breakdown');
        $this->assertNotNull($res->json('breakdown.0.assignment_id'));
        $this->assertSame(
            (int) StudentFeeAssignment::where('student_id', $student->student_id)->value('id'),
            (int) $res->json('breakdown.0.assignment_id')
        );
    }
}
