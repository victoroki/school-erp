<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\StudentFee;
use App\Models\FeePayment;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Carbon\Carbon;

class FeeSeeder extends Seeder
{
    public function run()
    {
        // Ensure we have some base data
        $academicYear = AcademicYear::first() ?? AcademicYear::create(['year' => '2025-2026', 'title' => '2025-2026', 'start_date' => '2025-01-01', 'end_date' => '2025-12-31']);
        
        // Create Fee Categories if not exist
        $categories = ['Tuition Fee', 'Transport Fee', 'Library Fee', 'Exam Fee'];
        foreach ($categories as $catName) {
            FeeCategory::firstOrCreate(['name' => $catName], ['description' => $catName . ' for the year']);
        }

        // Create Fee Structures for each class
        $classes = SchoolClass::all();
        if ($classes->isEmpty()) {
            // Create a dummy class if none exists
            $classes = collect([SchoolClass::create(['name' => 'Class 1', 'numeric_value' => 1])]);
        }

        $feeStructures = [];
        foreach ($classes as $class) {
            foreach (FeeCategory::all() as $category) {
                $amount = rand(1000, 5000);
                $feeStructures[] = FeeStructure::create([
                    'academic_year_id' => $academicYear->academic_year_id,
                    'class_id' => $class->class_id,
                    'category_id' => $category->category_id,
                    'amount' => $amount,
                    'due_date' => Carbon::now()->addMonths(rand(1, 5)),
                ]);
            }
        }

        // Assign Fees to Students
        $students = Student::all();
        foreach ($students as $student) {
            // Assign random fee structures to student
            // Assuming student belongs to a class, but for simplicity assigning random structures
            // In a real app, we would check student's class.
            
            // Let's pick 2-3 fee structures for this student
            $assignedStructures = collect($feeStructures)->random(rand(2, 4));

            foreach ($assignedStructures as $structure) {
                $amount = $structure->amount;
                $discount = 0;
                
                // 20% chance of discount
                if (rand(1, 100) <= 20) {
                    $discount = $amount * 0.1; // 10% discount
                }

                $finalAmount = $amount - $discount;

                $studentFee = StudentFee::create([
                    'student_id' => $student->student_id,
                    'fee_structure_id' => $structure->fee_structure_id,
                    'amount' => $amount,
                    'discount_amount' => $discount,
                    'final_amount' => $finalAmount,
                    'due_date' => $structure->due_date,
                    'status' => 'unpaid',
                ]);

                // Create Payments
                // 30% chance of full payment
                // 30% chance of partial payment
                // 40% chance of no payment

                $rand = rand(1, 100);
                if ($rand <= 30) {
                    // Full Payment
                    FeePayment::create([
                        'student_fee_id' => $studentFee->student_fee_id,
                        'amount' => $finalAmount,
                        'payment_date' => Carbon::now()->subDays(rand(1, 30)),
                        'payment_method' => 'cash',
                        'receipt_number' => 'REC-' . uniqid(),
                        'remarks' => 'Full payment',
                    ]);
                    $studentFee->update(['status' => 'paid']);
                } elseif ($rand <= 60) {
                    // Partial Payment
                    $paidAmount = $finalAmount / 2;
                    FeePayment::create([
                        'student_fee_id' => $studentFee->student_fee_id,
                        'amount' => $paidAmount,
                        'payment_date' => Carbon::now()->subDays(rand(1, 30)),
                        'payment_method' => 'online',
                        'receipt_number' => 'REC-' . uniqid(),
                        'remarks' => 'Partial payment',
                    ]);
                    $studentFee->update(['status' => 'partially_paid']);
                }
            }
        }
    }
}
