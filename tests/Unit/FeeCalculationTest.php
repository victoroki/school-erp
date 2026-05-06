<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use App\Models\FeePayment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeeCalculationTest extends TestCase
{
    // use RefreshDatabase; // Commented out to use seeded data or manual cleanup

    public function test_fee_calculations()
    {
        // Create a student
        $student = Student::factory()->create();

        // Assign a fee
        $feeAmount = 1000;
        $discount = 100;
        $finalAmount = $feeAmount - $discount;

        $assignment = StudentFeeAssignment::create([
            'student_id' => $student->student_id,
            'fee_structure_id' => 1,
            'academic_year_id' => 1,
            'term' => 'Term 1',
            'amount' => $feeAmount,
            'discount_amount' => $discount,
            'final_amount' => $finalAmount,
            'assigned_by' => 1,
            'assigned_date' => now(),
            'status' => 'active',
        ]);

        // Verify initial totals
        $this->assertEquals($finalAmount, $student->total_fee);
        $this->assertEquals(0, $student->paid_fee);
        $this->assertEquals($finalAmount, $student->balance_fee);
        $this->assertEquals('Unpaid', $student->payment_status);

        // Make a partial payment
        $paymentAmount = 400;
        FeePayment::create([
            'student_fee_assignment_id' => $assignment->student_fee_assignment_id,
            'amount' => $paymentAmount,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'receipt_number' => 'TEST-REC-1',
        ]);

        // Refresh student instance
        $student->refresh();

        // Verify totals after partial payment
        $this->assertEquals($finalAmount, $student->total_fee);
        $this->assertEquals($paymentAmount, $student->paid_fee);
        $this->assertEquals($finalAmount - $paymentAmount, $student->balance_fee);
        $this->assertEquals('Partial', $student->payment_status);

        // Make remaining payment
        $remainingAmount = $finalAmount - $paymentAmount;
        FeePayment::create([
            'student_fee_assignment_id' => $assignment->student_fee_assignment_id,
            'amount' => $remainingAmount,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'receipt_number' => 'TEST-REC-2',
        ]);

        // Refresh student instance
        $student->refresh();

        // Verify totals after full payment
        $this->assertEquals($finalAmount, $student->total_fee);
        $this->assertEquals($finalAmount, $student->paid_fee);
        $this->assertEquals(0, $student->balance_fee);
        $this->assertEquals('Paid', $student->payment_status);
        
        // Cleanup
        $student->delete();
    }
}
