<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\PaymentAllocation;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentFeeAssignment;
use App\Models\User;
use App\Services\FeeBalanceService;
use App\Services\FinanceService;
use App\Services\LedgerService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fee balance integrity around payment reversal.
 *
 * Before this batch a reversal was invisible to every figure except
 * student_fee_assignments.paid_amount:
 *   - the payment row kept its amount, its receipt number and a normal receipt,
 *   - "SUM(fee_payments.amount)" kept counting the reversed money, so the
 *     student profile, arrears and collection metrics disagreed with the fee
 *     screen,
 *   - re-allocating a second payment recomputed paid_amount from a bare
 *     PaymentAllocation::sum(), which resurrected the reversed amount,
 *   - double submissions created two payments and two receipts.
 */
class FeeReversalIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    private function accountant(): User
    {
        $user = User::factory()->create(['name' => 'Fee Reversal Accountant']);
        $user->roles()->sync(Role::where('role_name', 'Accountant')->pluck('role_id'));
        $user->staff()->create([
            'first_name' => 'Fee',
            'middle_name' => null,
            'last_name' => 'Accountant',
            'date_of_birth' => '2000-01-01',
            'gender' => 'female',
            'phone_primary' => '0711000000',
            'work_email' => 'fee-reversal.' . uniqid() . '@test.local',
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

        return $user->load('roles.permissions', 'staff');
    }

    private function createStudent(int $suffix = 0): Student
    {
        // academic_years.name is unique, and a test may create more than one student.
        $year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);
        $class = SchoolClass::create(['name' => 'Grade ' . (1 + $suffix), 'numeric_value' => 1 + $suffix]);
        $section = Section::create(['name' => 'A' . $suffix]);
        $classSection = ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);

        $student = Student::create([
            'admission_no' => 'ADM-' . uniqid(),
            'first_name' => 'Reversal',
            'last_name' => 'Test' . $suffix,
            'date_of_birth' => '2010-01-01',
            'gender' => 'male',
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

    private function createAssignment(Student $student, float $finalAmount, ?User $user = null): StudentFeeAssignment
    {
        $user = $user ?? $this->accountant();

        $category = \App\Models\FeeCategory::create([
            'name' => 'Category-' . uniqid(),
            'type' => 'mandatory',
        ]);

        $enrollment = $student->studentClassEnrollments()->first();

        $structure = FeeStructure::create([
            'academic_year_id' => $enrollment->academic_year_id,
            'class_id' => $enrollment->classSection->class_id,
            'category_id' => $category->category_id,
            'amount' => $finalAmount,
            'term' => 'Term 1',
            'payment_frequency' => 'termly',
            'due_date' => now()->addDays(30),
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        return StudentFeeAssignment::create([
            'student_id' => $student->student_id,
            'fee_structure_id' => $structure->fee_structure_id,
            'academic_year_id' => $structure->academic_year_id,
            'term' => 'Term 1',
            'amount' => $finalAmount,
            'final_amount' => $finalAmount,
            'assigned_by' => $user->id,
            'assigned_date' => now(),
            'status' => 'active',
        ]);
    }

    private function pay(StudentFeeAssignment $assignment, float $amount, array $extra = []): FeePayment
    {
        return app(FinanceService::class)->recordPayment(array_merge([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => $amount,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ], $extra));
    }

    public function test_reversing_a_payment_restores_the_outstanding_balance(): void
    {
        $accountant = $this->accountant();
        $student = $this->createStudent();
        $assignment = $this->createAssignment($student, 10000, $accountant);

        $payment = $this->pay($assignment, 4000);

        $this->assertSame(6000.0, (float) $student->fresh()->balance_fee);

        app(LedgerService::class)->reversePayment($payment, 'Cheque bounced', $accountant->id);

        $this->assertSame(
            10000.0,
            (float) $student->fresh()->balance_fee,
            'Reversing a payment must put the balance back.'
        );
        $this->assertSame(0.0, (float) $assignment->fresh()->paid_amount);
        $this->assertSame(0.0, app(FeeBalanceService::class)->paidForStudent((int) $student->student_id));
    }

    public function test_reversed_payment_is_marked_on_its_own_row(): void
    {
        $accountant = $this->accountant();
        $student = $this->createStudent();
        $assignment = $this->createAssignment($student, 10000, $accountant);

        $payment = $this->pay($assignment, 4000);
        $this->assertFalse($payment->fresh()->isReversed());

        app(LedgerService::class)->reversePayment($payment, 'Duplicate entry', $accountant->id);

        $fresh = $payment->fresh();

        $this->assertTrue($fresh->isReversed());
        $this->assertNotNull($fresh->reversed_at);
        $this->assertSame('Duplicate entry', $fresh->reversal_reason);
        $this->assertSame($accountant->id, (int) $fresh->reversed_by);
        // The receipt number is retained for audit, but the row is now void.
        $this->assertNotNull($fresh->receipt_number);
    }

    public function test_reversed_payment_is_excluded_from_collected_metrics(): void
    {
        $accountant = $this->accountant();
        $student = $this->createStudent();
        $other = $this->createStudent(2);
        $assignment = $this->createAssignment($student, 10000, $accountant);
        $otherAssignment = $this->createAssignment($other, 5000, $accountant);

        $reversed = $this->pay($assignment, 4000);
        $this->pay($otherAssignment, 3000);

        $this->assertSame(7000.0, app(FeeBalanceService::class)->totalCollected());

        app(LedgerService::class)->reversePayment($reversed, 'RTGS returned', $accountant->id);

        $this->assertSame(
            3000.0,
            app(FeeBalanceService::class)->totalCollected(),
            'Reversed money must not count as collected.'
        );

        $metrics = app(FinanceService::class)->getMetrics();
        $this->assertSame(3000.0, (float) $metrics['total_collected']);
        $this->assertSame(12000.0, (float) $metrics['total_pending']);
        $this->assertLessThanOrEqual(100, (float) $metrics['collection_rate']);
    }

    public function test_a_payment_cannot_be_reversed_twice(): void
    {
        $accountant = $this->accountant();
        $student = $this->createStudent();
        $assignment = $this->createAssignment($student, 10000, $accountant);

        $payment = $this->pay($assignment, 4000);

        $ledger = app(LedgerService::class);
        $ledger->reversePayment($payment, 'First reversal', $accountant->id);

        $this->expectException(\Exception::class);
        $ledger->reversePayment($payment->fresh(), 'Second reversal', $accountant->id);
    }

    public function test_allocating_after_a_reversal_does_not_recount_the_reversed_amount(): void
    {
        $accountant = $this->accountant();
        $student = $this->createStudent();
        $assignment = $this->createAssignment($student, 10000, $accountant);

        $reversed = $this->pay($assignment, 4000);
        app(LedgerService::class)->reversePayment($reversed, 'Bounced', $accountant->id);

        // A second, unrelated payment. Recomputing paid_amount from a bare
        // PaymentAllocation::sum() used to resurrect the reversed 4000.
        $this->pay($assignment, 2000);

        $this->assertSame(
            2000.0,
            (float) $assignment->fresh()->paid_amount,
            'The reversed payment must not come back into paid_amount.'
        );
        $this->assertSame(8000.0, (float) $student->fresh()->balance_fee);
    }

    public function test_allocate_payment_returns_the_allocations_it_created(): void
    {
        $accountant = $this->accountant();
        $student = $this->createStudent();
        $assignment = $this->createAssignment($student, 10000, $accountant);

        $payment = FeePayment::create([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 1500,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'receipt_number' => 'RCP-TEST-' . uniqid(),
        ]);

        $created = app(LedgerService::class)->allocatePayment(
            $payment,
            [['id' => $assignment->id, 'amount' => 1500]],
            'manual'
        );

        $this->assertCount(1, $created, 'allocatePayment must return what it created, not an undefined variable.');
        $this->assertInstanceOf(PaymentAllocation::class, $created[0]);
        $this->assertSame(1500.0, (float) $created[0]->amount);
    }

    public function test_student_fee_accessors_agree_with_the_balance_service(): void
    {
        $accountant = $this->accountant();
        $student = $this->createStudent();
        $first = $this->createAssignment($student, 10000, $accountant);
        $second = $this->createAssignment($student, 5000, $accountant);

        $this->pay($first, 2500);

        $balances = app(FeeBalanceService::class);
        $id = (int) $student->student_id;

        $fresh = $student->fresh();

        $this->assertSame($balances->assignedForStudent($id), (float) $fresh->total_fee);
        $this->assertSame($balances->paidForStudent($id), (float) $fresh->paid_fee);
        $this->assertSame($balances->balanceForStudent($id), (float) $fresh->balance_fee);
        $this->assertSame(15000.0, (float) $fresh->total_fee);
        $this->assertSame(12500.0, (float) $fresh->balance_fee);
    }

    public function test_double_submission_of_the_same_form_creates_one_payment(): void
    {
        $accountant = $this->accountant();
        $student = $this->createStudent();
        $assignment = $this->createAssignment($student, 10000, $accountant);

        $payload = [

            'student_fee_assignment_id' => $assignment->id,
            'amount' => 4000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'client_reference' => 'form-token-abc-123',
        ];

        $this->actingAs($accountant)
            ->post("/fee-management/{$student->student_id}/store-payment", $payload)
            ->assertRedirect(route('fee-management.receipt', FeePayment::first()->payment_id));

        $this->actingAs($accountant)
            ->post("/fee-management/{$student->student_id}/store-payment", $payload)
            ->assertRedirect(route('fee-management.receipt', FeePayment::first()->payment_id));

        $this->assertSame(1, FeePayment::count(), 'A resubmitted form must not create a second payment.');
        $this->assertSame(4000.0, (float) $assignment->fresh()->paid_amount);
        $this->assertSame(6000.0, (float) $student->fresh()->balance_fee);
    }

    public function test_payment_amount_is_validated(): void
    {
        $accountant = $this->accountant();
        $student = $this->createStudent();
        $assignment = $this->createAssignment($student, 10000, $accountant);

        $this->actingAs($accountant)
            ->post("/fee-management/{$student->student_id}/store-payment", [
                'student_fee_assignment_id' => $assignment->id,
                'amount' => 0,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'cash',
            ])
            ->assertSessionHasErrors('amount');

        $this->actingAs($accountant)
            ->post("/fee-management/{$student->student_id}/store-payment", [
                'student_fee_assignment_id' => $assignment->id,
                'amount' => 100,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'crypto',
            ])
            ->assertSessionHasErrors('payment_method');

        $this->assertSame(0, FeePayment::count());
    }

    public function test_payment_cannot_target_another_students_fee_assignment(): void
    {
        $accountant = $this->accountant();
        $victim = $this->createStudent();
        $attackerTarget = $this->createStudent(3);
        $victimAssignment = $this->createAssignment($victim, 10000, $accountant);

        // URL says student B, but the posted assignment belongs to student A.
        $this->actingAs($accountant)
            ->post("/fee-management/{$attackerTarget->student_id}/store-payment", [
                'student_fee_assignment_id' => $victimAssignment->id,
                'amount' => 500,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'cash',
            ])
            ->assertRedirect(route('fee-management.show', $attackerTarget->student_id));

        $this->assertSame(0, FeePayment::count(), 'A fee assignment from another student must be rejected.');
        $this->assertSame(0.0, (float) $victimAssignment->fresh()->paid_amount);
    }
}
