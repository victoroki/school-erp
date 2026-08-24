<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\DiscountScheme;
use App\Models\FeeInvoice;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\StudentDiscount;
use App\Models\StudentFeeAssignment;
use App\Models\Term;
use Illuminate\Support\Facades\DB;

class FeeAssignmentService
{
    public function assignFeeToStudent(int $studentId, int $feeStructureId, int $academicYearId, string $term, float $discountAmount = 0, ?int $assignedBy = null): StudentFeeAssignment
    {
        $feeStructure = FeeStructure::findOrFail($feeStructureId);
        $finalAmount = max(0, $feeStructure->amount - $discountAmount);
        $termId = $this->resolveTermId($academicYearId, $term);

        return DB::transaction(function () use ($studentId, $feeStructure, $academicYearId, $term, $termId, $discountAmount, $finalAmount, $assignedBy) {
            return StudentFeeAssignment::create([
                'student_id' => $studentId,
                'fee_structure_id' => $feeStructure->fee_structure_id,
                'academic_year_id' => $academicYearId,
                'term' => $term,
                'term_id' => $termId,
                'amount' => $feeStructure->amount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'assigned_by' => $assignedBy ?? auth()->id(),
                'assigned_date' => now(),
                'status' => 'active',
            ]);
        });
    }

    public function autoAssignFeesToStudent(Student $student, ?int $academicYearId = null, ?string $termCode = null): array
    {
        $academicYear = $academicYearId
            ? AcademicYear::find($academicYearId)
            : AcademicYear::where('is_current', true)->first();

        if (! $academicYear) {
            return ['success' => false, 'message' => 'No academic year found', 'count' => 0];
        }

        $term = $termCode
            ? Term::where('code', $termCode)->where('academic_year_id', $academicYear->academic_year_id)->first()
            : Term::forCurrentAcademicYear()->active()->first();

        $termValue = $term?->code ?? 'Term 1';

        $enrollment = $student->studentClassEnrollments()
            ->where('academic_year_id', $academicYear->academic_year_id)
            ->where('is_current', true)
            ->first();

        if (! $enrollment) {
            return ['success' => false, 'message' => 'Student has no active enrollment for this academic year', 'count' => 0];
        }

        $classId = $enrollment->classSection->class_id;

        $feeStructures = FeeStructure::where('academic_year_id', $academicYear->academic_year_id)
            ->where('status', 'active')
            ->where(function ($query) use ($classId) {
                $query->where('class_id', $classId)
                    ->orWhereNull('class_id');
            })
            ->where(function ($query) use ($termValue) {
                $query->where('term', $termValue)
                    ->orWhereNull('term');
            })
            ->get();

        if ($feeStructures->isEmpty()) {
            return ['success' => true, 'message' => 'No fee structures found for this class and term', 'count' => 0];
        }

        $count = $this->bulkAssign($feeStructures, collect([$student]), $academicYear->academic_year_id, $termValue);

        $this->applyAutoDiscounts($student, $academicYear->academic_year_id);

        return ['success' => true, 'message' => "Successfully assigned {$count} fees", 'count' => $count];
    }

    public function bulkAssignToClass(int $classId, int $academicYearId, string $term, ?array $feeStructureIds = null): array
    {
        $students = Student::whereHas('studentClassEnrollments.classSection', function ($query) use ($classId) {
            $query->where('class_id', $classId)
                ->where('is_current', true);
        })->where('is_active', true)->get();

        if ($students->isEmpty()) {
            return ['success' => false, 'message' => 'No active students found in this class', 'count' => 0];
        }

        $query = FeeStructure::where('academic_year_id', $academicYearId)
            ->where('status', 'active');

        if ($feeStructureIds) {
            $query->whereIn('fee_structure_id', $feeStructureIds);
        }

        $feeStructures = $query->get();

        if ($feeStructures->isEmpty()) {
            return ['success' => false, 'message' => 'No active fee structures found', 'count' => 0];
        }

        $count = $this->bulkAssign($feeStructures, $students, $academicYearId, $term);

        return ['success' => true, 'message' => "Assigned fees to {$count} student-fee combinations", 'count' => $count];
    }

    public function bulkAssignToMultipleClasses(array $classIds, int $academicYearId, string $term, ?array $feeStructureIds = null): array
    {
        $students = Student::whereHas('studentClassEnrollments.classSection', function ($query) use ($classIds) {
            $query->whereIn('class_id', $classIds)
                ->where('is_current', true);
        })->where('is_active', true)->get();

        if ($students->isEmpty()) {
            return ['success' => false, 'message' => 'No active students found in selected classes', 'count' => 0];
        }

        $query = FeeStructure::where('academic_year_id', $academicYearId)
            ->where('status', 'active');

        if ($feeStructureIds) {
            $query->whereIn('fee_structure_id', $feeStructureIds);
        }

        $feeStructures = $query->get();

        if ($feeStructures->isEmpty()) {
            return ['success' => false, 'message' => 'No active fee structures found', 'count' => 0];
        }

        $count = $this->bulkAssign($feeStructures, $students, $academicYearId, $term, true);

        return ['success' => true, 'message' => "Assigned fees to {$count} student-fee combinations", 'count' => $count];
    }

    public function autoAssignFeesToAllStudents(int $academicYearId, string $term, ?array $feeStructureIds = null): array
    {
        $academicYear = AcademicYear::find($academicYearId);

        if (! $academicYear) {
            return ['success' => false, 'message' => 'Academic year not found', 'count' => 0, 'students_processed' => 0];
        }

        $students = Student::where('is_active', true)
            ->whereHas('studentClassEnrollments', function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId)
                    ->where('is_current', true);
            })
            ->with(['studentClassEnrollments' => function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId)
                    ->where('is_current', true)
                    ->with('classSection');
            }])
            ->get();

        if ($students->isEmpty()) {
            return ['success' => false, 'message' => 'No active students with current enrollment found', 'count' => 0, 'students_processed' => 0];
        }

        $query = FeeStructure::where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->where(function ($q) use ($term) {
                $q->where('term', $term)
                    ->orWhereNull('term');
            });

        if ($feeStructureIds) {
            $query->whereIn('fee_structure_id', $feeStructureIds);
        }

        $feeStructures = $query->get();

        if ($feeStructures->isEmpty()) {
            return ['success' => false, 'message' => 'No active fee structures found for this academic year and term', 'count' => 0, 'students_processed' => 0];
        }

        $count = 0;
        $studentsProcessed = 0;
        $skippedCount = 0;
        $termId = $this->resolveTermId($academicYearId, $term);

        DB::transaction(function () use ($feeStructures, $students, $academicYearId, $term, $termId, &$count, &$studentsProcessed, &$skippedCount) {
            foreach ($students->chunk(100) as $studentChunk) {
                foreach ($studentChunk as $student) {
                    $enrollment = $student->studentClassEnrollments->first();

                    if (! $enrollment || ! $enrollment->classSection) {
                        $skippedCount++;

                        continue;
                    }

                    $studentClassId = $enrollment->classSection->class_id;

                    $studentFeeStructures = $feeStructures->filter(function ($fee) use ($studentClassId) {
                        return ! $fee->class_id || $fee->class_id == $studentClassId;
                    });

                    if ($studentFeeStructures->isEmpty()) {
                        $skippedCount++;

                        continue;
                    }

                    $studentAssigned = false;

                    foreach ($studentFeeStructures as $fee) {
                        $exists = StudentFeeAssignment::where('student_id', $student->student_id)
                            ->where('fee_structure_id', $fee->fee_structure_id)
                            ->where('academic_year_id', $academicYearId)
                            ->where('term', $term)
                            ->exists();

                        if (! $exists) {
                            StudentFeeAssignment::create([
                                'student_id' => $student->student_id,
                                'fee_structure_id' => $fee->fee_structure_id,
                                'academic_year_id' => $academicYearId,
                                'term' => $term,
                                'term_id' => $termId,
                                'amount' => $fee->amount,
                                'final_amount' => $fee->amount,
                                'assigned_by' => auth()->id(),
                                'assigned_date' => now(),
                                'status' => 'active',
                            ]);

                            $count++;
                            $studentAssigned = true;
                        }
                    }

                    if ($studentAssigned) {
                        $this->applyAutoDiscounts($student, $academicYearId);
                        $studentsProcessed++;
                    } else {
                        $skippedCount++;
                    }
                }
            }
        });

        return [
            'success' => true,
            'message' => "Successfully assigned {$count} fees to {$studentsProcessed} students ({$skippedCount} skipped - already assigned or no matching fees)",
            'count' => $count,
            'students_processed' => $studentsProcessed,
            'skipped' => $skippedCount,
        ];
    }

    public function bulkAssign($feeStructures, $students, int $academicYearId, string $term, bool $matchClass = false): int
    {
        $count = 0;
        $termId = $this->resolveTermId($academicYearId, $term);

        DB::transaction(function () use ($feeStructures, $students, $academicYearId, $term, $termId, $matchClass, &$count) {
            foreach ($students->chunk(100) as $studentChunk) {
                foreach ($studentChunk as $student) {
                    $enrollment = $student->studentClassEnrollments()
                        ->where('is_current', true)
                        ->where('academic_year_id', $academicYearId)
                        ->with('classSection')
                        ->first();

                    // Only assign to students with a current enrollment in the
                    // assignment's academic year.
                    if (! $enrollment) {
                        continue;
                    }

                    $studentClassId = $enrollment->classSection?->class_id;

                    foreach ($feeStructures as $fee) {
                        if ($matchClass && $fee->class_id && $fee->class_id != $studentClassId) {
                            continue;
                        }

                        $exists = StudentFeeAssignment::where('student_id', $student->student_id)
                            ->where('fee_structure_id', $fee->fee_structure_id)
                            ->where('academic_year_id', $academicYearId)
                            ->where('term', $term)
                            ->exists();

                        if (! $exists) {
                            StudentFeeAssignment::create([
                                'student_id' => $student->student_id,
                                'fee_structure_id' => $fee->fee_structure_id,
                                'academic_year_id' => $academicYearId,
                                'term' => $term,
                                'term_id' => $termId,
                                'amount' => $fee->amount,
                                'final_amount' => $fee->amount,
                                'assigned_by' => auth()->id(),
                                'assigned_date' => now(),
                                'status' => 'active',
                            ]);

                            $count++;
                        }
                    }
                }
            }
        });

        return $count;
    }

    public function applyAutoDiscounts(Student $student, int $academicYearId): void
    {
        $autoDiscounts = DiscountScheme::where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->where('auto_apply', true)
            ->get()
            ->filter(fn ($scheme) => $this->isEligibleForDiscount($student, $scheme))
            ->values();

        if ($autoDiscounts->isEmpty()) {
            return;
        }

        $this->applyDiscountToStudentAssignments($student, $autoDiscounts, $academicYearId);
    }

    public function generateInvoicesForTerm(int $academicYearId, string $term, ?int $classId = null): int
    {
        $query = Student::whereHas('studentFeeAssignments', function ($q) use ($academicYearId, $term) {
            $q->where('academic_year_id', $academicYearId)
                ->where('term', $term)
                ->where('status', 'active');
        })->where('is_active', true);

        if ($classId) {
            $query->whereHas('studentClassEnrollments.classSection', function ($q) use ($classId) {
                $q->where('class_id', $classId)
                    ->where('is_current', true);
            });
        }

        $students = $query->get();
        $count = 0;

        foreach ($students as $student) {
            $existingInvoice = FeeInvoice::where('student_id', $student->student_id)
                ->where('academic_year_id', $academicYearId)
                ->where('term', $term)
                ->first();

            if ($existingInvoice) {
                continue;
            }

            $assignments = StudentFeeAssignment::where('student_id', $student->student_id)
                ->where('academic_year_id', $academicYearId)
                ->where('term', $term)
                ->where('status', 'active')
                ->get();

            if ($assignments->isEmpty()) {
                continue;
            }

            $totalAmount = $assignments->sum('amount');
            $discountAmount = $assignments->sum('discount_amount');
            $netAmount = $assignments->sum('final_amount');

            $termModel = Term::where('academic_year_id', $academicYearId)
                ->where('code', $term)
                ->first();

            FeeInvoice::create([
                'student_id' => $student->student_id,
                'academic_year_id' => $academicYearId,
                'term' => $term,
                'invoice_number' => FeeInvoice::generateInvoiceNumber(),
                'invoice_date' => now(),
                'due_date' => $termModel?->fee_due_date,
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'net_amount' => $netAmount,
                'payment_status' => 'unpaid',
                'generated_by' => auth()->id(),
            ]);

            $count++;
        }

        return $count;
    }

    protected function isEligibleForDiscount(Student $student, DiscountScheme $scheme): bool
    {
        $existingCount = StudentDiscount::where('student_id', $student->student_id)
            ->where('discount_scheme_id', $scheme->id)
            ->where('academic_year_id', $scheme->academic_year_id)
            ->count();

        if ($scheme->max_instances && $existingCount >= $scheme->max_instances) {
            return false;
        }

        if ($scheme->valid_from && now()->lt($scheme->valid_from)) {
            return false;
        }

        if ($scheme->valid_to && now()->gt($scheme->valid_to)) {
            return false;
        }

        $criteria = $scheme->eligibility_criteria;

        return match ($criteria) {
            'staff_child' => $this->isStaffChild($student),
            'sibling' => $this->hasSibling($student),
            'merit' => $student->is_scholarship_holder,
            'financial_aid' => true,
            default => true,
        };
    }

    protected function isStaffChild(Student $student): bool
    {
        return $student->parents()->whereHas('user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->where('role_name', 'staff');
            });
        })->exists();
    }

    protected function hasSibling(Student $student): bool
    {
        return $student->siblings()->where('is_active', true)->exists();
    }

    protected function applyDiscountToStudentAssignments(Student $student, $schemes, int $academicYearId): void
    {
        $assignments = StudentFeeAssignment::where('student_id', $student->student_id)
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->get();

        $primarySchemeId = $schemes->first()->id;

        foreach ($assignments as $assignment) {
            // Manual discount protection: assignments carrying a manual
            // reduction (discount_id null but discount_amount > 0, e.g. from an
            // approved fee adjustment) are never touched by auto-apply.
            if (! $assignment->discount_id && (float) $assignment->discount_amount > 0) {
                continue;
            }

            $totalDiscount = 0;

            foreach ($schemes as $scheme) {
                $totalDiscount += $this->calculateDiscount($assignment->amount, $scheme);
            }

            $totalDiscount = min($totalDiscount, $assignment->amount);

            if ($totalDiscount > 0) {
                $assignment->update([
                    'discount_id' => $primarySchemeId,
                    'discount_amount' => $totalDiscount,
                    'final_amount' => $assignment->amount - $totalDiscount,
                ]);
            }
        }

        foreach ($schemes as $scheme) {
            $schemeTotal = 0;

            foreach ($assignments as $assignment) {
                if (! $assignment->discount_id && (float) $assignment->discount_amount > 0) {
                    continue;
                }

                $schemeTotal += $this->calculateDiscount($assignment->amount, $scheme);
            }

            if ($schemeTotal > 0 && $this->hasStudentDiscountRecord($student, $scheme, $academicYearId)) {
                continue;
            }

            if ($schemeTotal > 0) {                StudentDiscount::create([
                    'student_id' => $student->student_id,
                    'discount_scheme_id' => $scheme->id,
                    'academic_year_id' => $academicYearId,
                    'applied_amount' => $schemeTotal,
                    'justification' => "Auto-applied: {$scheme->name}",
                    'requested_by' => auth()->id(),
                    'requested_date' => now(),
                    'approval_status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_date' => now(),
                    'status' => 'active',
                ]);
            }
        }
    }

    protected function hasStudentDiscountRecord(Student $student, DiscountScheme $scheme, int $academicYearId): bool
    {
        return StudentDiscount::where('student_id', $student->student_id)
            ->where('discount_scheme_id', $scheme->id)
            ->where('academic_year_id', $academicYearId)
            ->exists();
    }

    protected function resolveTermId(int $academicYearId, ?string $term): ?int
    {
        if (! $term) {
            return null;
        }

        return Term::where('academic_year_id', $academicYearId)
            ->where('code', $term)
            ->value('id');
    }

    protected function calculateDiscount(float $amount, DiscountScheme $scheme): float
    {
        return match ($scheme->type) {
            'percentage' => round($amount * ($scheme->value / 100), 2),
            'fixed' => min($scheme->value, $amount),
            'full_waiver' => $amount,
            default => 0,
        };
    }

    public function getUnassignedStudents(int $academicYearId, ?string $term = null)
    {
        $query = StudentFeeAssignment::where('academic_year_id', $academicYearId)
            ->where('status', 'active');

        if ($term) {
            $query->where('term', $term);
        }

        $assignedStudentIds = $query->pluck('student_id')->unique();

        return Student::whereNotIn('student_id', $assignedStudentIds)
            ->where('is_active', true)
            ->with(['studentClassEnrollments' => function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId)
                    ->where('is_current', true)
                    ->with(['classSection.schoolClass']);
            }])
            ->get();
    }

    public function getAutoAssignmentPreview(int $academicYearId, string $term, ?array $feeStructureIds = null): array
    {
        $studentsWithEnrollment = Student::where('is_active', true)
            ->whereHas('studentClassEnrollments', function ($query) use ($academicYearId) {
                $query->where('academic_year_id', $academicYearId)
                    ->where('is_current', true);
            })
            ->count();

        $query = FeeStructure::where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->where(function ($q) use ($term) {
                $q->where('term', $term)
                    ->orWhereNull('term');
            });

        if ($feeStructureIds) {
            $query->whereIn('fee_structure_id', $feeStructureIds);
        }

        $feeStructuresCount = $query->count();

        $alreadyAssigned = StudentFeeAssignment::where('academic_year_id', $academicYearId)
            ->where('term', $term)
            ->where('status', 'active')
            ->distinct('student_id')
            ->count('student_id');

        $potentialAssignments = $studentsWithEnrollment * $feeStructuresCount;

        return [
            'students_with_enrollment' => $studentsWithEnrollment,
            'fee_structures_count' => $feeStructuresCount,
            'already_assigned_students' => $alreadyAssigned,
            'potential_new_assignments' => max(0, $potentialAssignments - $alreadyAssigned),
        ];
    }

    public function getStudentFeeSummary(int $studentId, ?int $academicYearId = null): array
    {
        $query = StudentFeeAssignment::with(['feeStructure.category', 'academicYear', 'discount'])
            ->where('student_id', $studentId)
            ->where('status', 'active');

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $assignments = $query->get();

        $totalAmount = $assignments->sum('amount');
        $totalDiscount = $assignments->sum('discount_amount');
        $netPayable = $assignments->sum('final_amount');
        $totalPaid = $assignments->sum('paid_amount');

        return [
            'assignments' => $assignments,
            'total_amount' => $totalAmount,
            'total_discount' => $totalDiscount,
            'net_payable' => $netPayable,
            'total_paid' => $totalPaid,
            'balance' => $netPayable - $totalPaid,
        ];
    }
}
