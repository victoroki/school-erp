<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\StudentFeeAssignment;
use App\Models\FeePayment;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Staff;

class FeeSeeder extends Seeder
{
    public function run()
    {
        $year = AcademicYear::where('is_current', true)->first();
        if (!$year) {
            return;
        }

        $term = Term::where('academic_year_id', $year->academic_year_id)
            ->where('status', 'active')
            ->first();

        if (!$term) {
            $term = Term::where('academic_year_id', $year->academic_year_id)->first();
        }
        if (!$term) {
            return;
        }

        $schoolFeesIncome = \App\Models\IncomeCategory::where('name', 'School Fees')->first();

        // 1. Kenyan fee categories
        $categories = [
            ['name' => 'Tuition Fee', 'code' => 'TUITION', 'type' => 'mandatory', 'description' => 'Core tuition for CBC academic programmes', 'order' => 1],
            ['name' => 'Medical & NHIF', 'code' => 'MEDICAL', 'type' => 'mandatory', 'description' => 'School medical scheme and NHIF medical levy', 'order' => 2],
            ['name' => 'Examination (KNEC) Fee', 'code' => 'EXAM', 'type' => 'mandatory', 'description' => 'KNEC and school examination & assessment fees', 'order' => 3],
            ['name' => 'Activity & Co-curricular Levy', 'code' => 'ACTIVITY', 'type' => 'mandatory', 'description' => 'Sports, music, clubs and academic trips', 'order' => 4],
            ['name' => 'Library Levy', 'code' => 'LIBRARY', 'type' => 'mandatory', 'description' => 'Library resources and reading materials', 'order' => 5],
            ['name' => 'Science Laboratory Levy', 'code' => 'LAB', 'type' => 'mandatory', 'description' => 'Science practicals and lab consumables', 'order' => 6],
            ['name' => 'ICT & Computer Levy', 'code' => 'ICT', 'type' => 'mandatory', 'description' => 'Computer studies and digital literacy resources', 'order' => 7],
            ['name' => 'School Transport Fee', 'code' => 'TRANSPORT', 'type' => 'optional', 'description' => 'School bus transport to and from home', 'order' => 8],
            ['name' => 'Boarding & Welfare', 'code' => 'BOARDING', 'type' => 'optional', 'description' => 'Hostel accommodation, meals and welfare', 'order' => 9],
            ['name' => 'Development Levy', 'code' => 'DEVELOPMENT', 'type' => 'mandatory', 'description' => 'Infrastructure development levy', 'order' => 10],
        ];

        $categoryIds = [];
        foreach ($categories as $cat) {
            $category = FeeCategory::firstOrCreate(['name' => $cat['name']], [
                'code' => $cat['code'],
                'type' => $cat['type'],
                'description' => $cat['description'],
                'income_category_id' => $schoolFeesIncome ? $schoolFeesIncome->category_id : null,
                'display_order' => $cat['order'],
                'status' => 'active',
            ]);
            $categoryIds[$cat['code']] = $category->category_id;
        }

        // 2. Fee structures per class & category (KES, termly)
        $structures = [];
        foreach (\App\Models\SchoolClass::all() as $class) {
            $nv = (int) $class->numeric_value;
            $stage = $this->stageFor($nv);

            $amounts = [
                'TUITION' => $this->tuitionFor($stage),
                'MEDICAL' => 1200,
                'EXAM' => $this->examFeeFor($stage),
                'ACTIVITY' => 3000,
                'LIBRARY' => 1500,
                'LAB' => $stage >= 4 ? 2500 : 0,
                'ICT' => $stage >= 3 ? 2000 : 0,
                'TRANSPORT' => 9000,
                'BOARDING' => 28000,
                'DEVELOPMENT' => 5000,
            ];

            foreach ($amounts as $code => $amount) {
                if ($amount <= 0) {
                    continue;
                }
                $structure = FeeStructure::firstOrCreate(
                    [
                        'academic_year_id' => $year->academic_year_id,
                        'term' => $term->code,
                        'class_id' => $class->class_id,
                        'category_id' => $categoryIds[$code],
                    ],
                    [
                        'amount' => $amount,
                        'payment_frequency' => 'termly',
                        'due_date' => $term->fee_due_date ?: $term->start_date,
                        'status' => 'active',
                        'created_by' => $this->bursarId(),
                    ]
                );
                $structures[$class->class_id][$code] = $structure;
            }
        }

        // 3. Assign fees to students based on their enrolled class
        $students = Student::with('studentClassEnrollments.classSection')->where('status', 'active')->get();

        foreach ($students as $student) {
            $enrollment = $student->studentClassEnrollments
                ->where('status', 'active')
                ->first();

            if (!$enrollment || !$enrollment->classSection) {
                continue;
            }

            $classId = $enrollment->classSection->class_id;
            if (!isset($structures[$classId])) {
                continue;
            }

            $discountPct = $student->is_scholarship_holder ? 25 : 0;

            foreach ($structures[$classId] as $code => $structure) {
                // Only charge optional transport/boarding where the flags say so
                if ($code === 'TRANSPORT' && !$student->uses_transport) {
                    continue;
                }
                if ($code === 'BOARDING' && !$student->is_hosteller) {
                    continue;
                }

                $amount = (float) $structure->amount;
                $discountAmount = round($amount * ($discountPct / 100), 2);
                $finalAmount = $amount - $discountAmount;

                $assignment = StudentFeeAssignment::firstOrCreate(
                    [
                        'student_id' => $student->student_id,
                        'fee_structure_id' => $structure->fee_structure_id,
                        'academic_year_id' => $year->academic_year_id,
                    ],
                    [
                        'term' => $term->code,
                        'term_id' => $term->id,
                        'amount' => $amount,
                        'discount_amount' => $discountAmount,
                        'final_amount' => $finalAmount,
                        'paid_amount' => 0,
                        'assigned_by' => $this->bursarId(),
                        'assigned_date' => now(),
                        'status' => 'active',
                        'notes' => $discountPct > 0 ? 'Scholarship bursary applied' : null,
                    ]
                );

                $this->maybePay($assignment, $student, $finalAmount);
            }
        }
    }

    private function maybePay(StudentFeeAssignment $assignment, Student $student, float $finalAmount)
    {
        if ($assignment->payments()->count() > 0) {
            return;
        }

        $roll = (int) substr(str_replace(['/', 'ADM'], '', $student->admission_no), -3);

        // Deterministic payment pattern: ~55% full, ~25% partial, rest unpaid
        $bucket = ($roll + $assignment->fee_structure_id) % 10;

        if ($bucket <= 5) {
            // Full payment
            $this->recordPayment($assignment, $finalAmount, 'Full term fee payment via M-PESA Paybill.');
            $assignment->paid_amount = $finalAmount;
        } elseif ($bucket <= 7) {
            $partial = round($finalAmount * 0.6, 2);
            $this->recordPayment($assignment, $partial, 'Partial fee payment.');
            $assignment->paid_amount = $partial;
        }

        $assignment->save();
    }

    private function recordPayment(StudentFeeAssignment $assignment, float $amount, string $remarks)
    {
        FeePayment::firstOrCreate(
            [
                'student_fee_assignment_id' => $assignment->id,
                'amount' => $amount,
            ],
            [
                'payment_date' => now()->subDays(7),
                'payment_method' => 'mpesa',
                'transaction_id' => 'MP' . str_pad((string)($assignment->id * 97 % 100000000), 8, '0', STR_PAD_LEFT),
                'receipt_number' => 'RCP-' . strtoupper(dechex($assignment->id)) . '-' . str_pad((string)($amount * 100), 8, '0', STR_PAD_LEFT),
                'remarks' => $remarks,
                'collected_by' => $this->bursarId(),
            ]
        );
    }

    private function stageFor(int $nv): int
    {
        if ($nv <= 2) {
            return 1; // Pre-primary
        }
        if ($nv <= 5) {
            return 2; // Lower primary
        }
        if ($nv <= 8) {
            return 3; // Upper primary
        }
        if ($nv <= 11) {
            return 4; // Junior school
        }
        return 5; // Senior school
    }

    private function tuitionFor(int $stage): int
    {
        return match ($stage) {
            1 => 20000,
            2 => 28000,
            3 => 36000,
            4 => 50000,
            default => 65000,
        };
    }

    private function examFeeFor(int $stage): int
    {
        return match ($stage) {
            1, 2 => 1500,
            3 => 1500,
            4 => 2500,
            default => 4000,
        };
    }

    private function bursarId()
    {
        static $id = null;
        if ($id === null) {
            $staff = Staff::where('staff_type', 'administration')->first()
                ?: Staff::first();
            $id = $staff ? $staff->staff_id : null;
        }
        return $id;
    }
}
