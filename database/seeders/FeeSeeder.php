<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\StudentFeeAssignment;
use App\Models\FeePayment;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Carbon\Carbon;

class FeeSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data to ensure clean demo state
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        FeePayment::truncate();
        StudentFeeAssignment::truncate();
        FeeStructure::truncate();
        FeeCategory::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ensure we have an active academic year
        $academicYear = AcademicYear::where('is_current', true)->first() 
            ?? AcademicYear::first() 
            ?? AcademicYear::create([
                'name' => '2025/2026 Academic Year',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'is_current' => true,
                'status' => 'active'
            ]);

        // 1. Create realistic Fee Categories
        $categories = [
            ['name' => 'Tuition Fee', 'desc' => 'General tuition for academic activities'],
            ['name' => 'Transport Fee', 'desc' => 'School bus transportation services'],
            ['name' => 'Library Fee', 'desc' => 'Access to library resources and books'],
            ['name' => 'Lab Fee', 'desc' => 'Science and computer lab equipment usage'],
            ['name' => 'Extracurricular Fee', 'desc' => 'Sports, arts, and club activities'],
            ['name' => 'Exam Fee', 'desc' => 'Examination processing and materials'],
        ];

        foreach ($categories as $cat) {
            FeeCategory::create([
                'name' => $cat['name'],
                'description' => $cat['desc']
            ]);
        }

        $allCategories = FeeCategory::all();
        $classes = SchoolClass::all();

        if ($classes->isEmpty()) {
            return;
        }

        // 2. Create Fee Structures for each class
        // Different classes might have different tuition amounts
        foreach ($classes as $class) {
            foreach ($allCategories as $category) {
                // Tuition increases with higher classes
                $baseAmount = $category->name === 'Tuition Fee' ? (2000 + ($class->class_id * 500)) : rand(200, 1000);
                
                FeeStructure::create([
                    'academic_year_id' => $academicYear->academic_year_id,
                    'class_id' => $class->class_id,
                    'category_id' => $category->category_id,
                    'amount' => $baseAmount,
                    'due_date' => Carbon::now()->addMonths(rand(0, 3))->startOfMonth()->addDays(14), // Middle of the month
                ]);
            }
        }

        // 3. Assign Fees to Students based on their actual Class Enrollment
        $students = Student::with(['studentClassEnrollments.classSection.schoolClass'])->get();
        
        foreach ($students as $student) {
            $enrollment = $student->studentClassEnrollments->first();
            if (!$enrollment) continue;

            $classId = $enrollment->classSection->schoolClass->class_id;
            $structures = FeeStructure::where('class_id', $classId)->get();

            foreach ($structures as $structure) {
                $discount = 0;
                // Randomly assign scholarships/discounts
                if (rand(1, 10) > 8) {
                    $discount = $structure->amount * (rand(5, 20) / 100);
                }

                $finalAmount = $structure->amount - $discount;

                $assignment = StudentFeeAssignment::create([
                    'student_id' => $student->student_id,
                    'fee_structure_id' => $structure->fee_structure_id,
                    'academic_year_id' => $academicYear->academic_year_id,
                    'term' => 'Term 1',
                    'amount' => $structure->amount,
                    'discount_amount' => $discount,
                    'final_amount' => $finalAmount,
                    'assigned_by' => 1,
                    'assigned_date' => now(),
                    'status' => 'active',
                ]);

                // 4. Create realistic Payments
                $paymentStatusChance = rand(1, 10);
                
                if ($paymentStatusChance <= 4) {
                    // Paid (40% chance)
                    FeePayment::create([
                        'student_fee_assignment_id' => $assignment->id,
                        'amount' => $finalAmount,
                        'payment_date' => Carbon::now()->subDays(rand(1, 15)),
                        'payment_method' => collect(['cash', 'bank_transfer', 'online'])->random(),
                        'receipt_number' => 'RCP-' . strtoupper(uniqid()),
                        'remarks' => 'Full payment received.',
                        'collected_by' => 1, // SuperAdmin
                    ]);
                } elseif ($paymentStatusChance <= 7) {
                    // Partial (30% chance)
                    $partialAmount = round($finalAmount * (rand(30, 70) / 100), 2);
                    FeePayment::create([
                        'student_fee_assignment_id' => $assignment->id,
                        'amount' => $partialAmount,
                        'payment_date' => Carbon::now()->subDays(rand(1, 5)),
                        'payment_method' => collect(['cash', 'bank_transfer'])->random(),
                        'receipt_number' => 'RCP-' . strtoupper(uniqid()),
                        'remarks' => 'Partial payment.',
                        'collected_by' => 1,
                    ]);
                }
                // Else: Unpaid (30% chance)
            }
        }
    }
}
