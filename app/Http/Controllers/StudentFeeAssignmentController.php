<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentFeeAssignment;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\DiscountScheme;
use App\Models\Term;
use App\Models\AuditTrail;
use App\Services\FeeAssignmentService;
use DB;
use Flash;

class StudentFeeAssignmentController extends Controller
{
    protected $feeAssignmentService;

    public function __construct(FeeAssignmentService $feeAssignmentService)
    {
        $this->feeAssignmentService = $feeAssignmentService;
        $this->middleware('can:fees.view')->only(['index', 'show']);
        $this->middleware('can:fees.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $query = StudentFeeAssignment::with(['student.studentClassEnrollments.classSection.schoolClass', 'feeStructure.category', 'academicYear']);

        if ($request->filled('class_id')) {
            $query->whereHas('student.studentClassEnrollments.classSection', function($q) use ($request) {
                $q->where('class_id', $request->class_id)
                  ->where('is_current', true);
            });
        }
        
        if ($request->filled('student_name')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->student_name}%")
                  ->orWhere('last_name', 'like', "%{$request->student_name}%");
            });
        }

        $assignments = $query->orderBy('created_at', 'desc')->paginate(15);
        $classes = SchoolClass::pluck('name', 'class_id');

        return view('fee_management.assignments.index', compact('assignments', 'classes'));
    }

    public function create()
    {
        $classes = SchoolClass::pluck('name', 'class_id');
        $currentYear = AcademicYear::where('is_current', true)->first();
        $feeCategories = \App\Models\FeeCategory::pluck('name', 'category_id');
        $terms = Term::forCurrentAcademicYear()->active()->get();
        
        return view('fee_management.assignments.create', compact('classes', 'currentYear', 'feeCategories', 'terms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'assignment_type' => 'required|in:bulk_class,bulk_all,auto_all_classes,individual',
            'academic_year_id' => 'required',
            'term' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $count = 0;
            $academicYearId = $request->academic_year_id;
            $term = $request->term;

            if ($request->assignment_type == 'bulk_class') {
                $request->validate([
                    'class_id' => 'required',
                    'fees' => 'required|array'
                ]);

                $students = Student::whereHas('studentClassEnrollments.classSection', function($q) use ($request) {
                    $q->where('class_id', $request->class_id)
                      ->where('is_current', true);
                })->where('is_active', true)->get();

                $fees = FeeStructure::whereIn('fee_structure_id', $request->fees)->get();

                $count = $this->assignFeesToStudents($students, $fees, $academicYearId, $term, $count);

            } elseif ($request->assignment_type == 'bulk_all') {
                $request->validate([
                    'class_ids' => 'required|array|min:1',
                    'fees' => 'required|array'
                ]);

                $classIds = $request->class_ids;

                $students = Student::whereHas('studentClassEnrollments.classSection', function($q) use ($classIds) {
                    $q->whereIn('class_id', $classIds)
                      ->where('is_current', true);
                })->where('is_active', true)->get();

                $fees = FeeStructure::whereIn('fee_structure_id', $request->fees)->get();

                $count = $this->assignFeesToStudents($students, $fees, $academicYearId, $term, $count, true);

            } elseif ($request->assignment_type == 'auto_all_classes') {
                $feeStructureIds = $request->filled('fees') ? $request->fees : null;

                $result = $this->feeAssignmentService->autoAssignFeesToAllStudents(
                    $academicYearId,
                    $term,
                    $feeStructureIds
                );

                DB::commit();

                if ($result['success']) {
                    Flash::success($result['message']);
                } else {
                    Flash::error($result['message']);
                }

                return redirect()->route('fees.assignments.index');

            } elseif ($request->assignment_type == 'individual') {
                $request->validate([
                    'student_id' => 'required',
                    'fees' => 'required|array'
                ]);

                $student = Student::find($request->student_id);
                $fees = FeeStructure::whereIn('fee_structure_id', $request->fees)->get();

                $count = $this->assignFeesToStudents(collect([$student]), $fees, $academicYearId, $term, $count);
            }

            DB::commit();
            AuditTrail::log('Fee Assignment', 'BULK CREATE', null, null, [
                'assignment_type' => $request->assignment_type,
                'academic_year_id' => $academicYearId,
                'term' => $term,
                'assignments_created' => $count,
            ]);
            Flash::success("$count assignments created successfully.");
            return redirect()->route('fees.assignments.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Flash::error("Error assigning fees: " . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    // Ajax method to get fees for a class
    public function getFeesByClass(Request $request)
    {
        $fees = FeeStructure::with('category')
            ->where('class_id', $request->class_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where(function ($query) use ($request) {
                if ($request->filled('term')) {
                    $query->where('term', $request->term)
                          ->orWhereNull('term');
                }
            })
            ->where('status', 'active')
            ->get();

        return response()->json($fees);
    }

    // Ajax method to get fees for multiple classes
    public function getFeesByClasses(Request $request)
    {
        $classIds = $request->input('class_ids', []);

        $query = FeeStructure::with(['category', 'schoolClass'])
            ->where('academic_year_id', $request->academic_year_id)
            ->where(function ($q) use ($request) {
                if ($request->filled('term')) {
                    $q->where('term', $request->term)
                      ->orWhereNull('term');
                }
            })
            ->where('status', 'active');

        if (!empty($classIds)) {
            $query->whereIn('class_id', $classIds);
        }

        $fees = $query->get();

        return response()->json($fees);
    }

    // Ajax method to get auto-assignment preview
    public function getAutoAssignmentPreview(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required',
            'term' => 'required',
        ]);

        $feeStructureIds = $request->filled('fees') ? $request->fees : null;

        $preview = $this->feeAssignmentService->getAutoAssignmentPreview(
            $request->academic_year_id,
            $request->term,
            $feeStructureIds
        );

        return response()->json($preview);
    }

    // Ajax method to get all fee structures for auto-assignment
    public function getAllFeeStructures(Request $request)
    {
        $query = FeeStructure::with(['category', 'schoolClass'])
            ->where('academic_year_id', $request->academic_year_id)
            ->where(function ($q) use ($request) {
                if ($request->filled('term')) {
                    $q->where('term', $request->term)
                      ->orWhereNull('term');
                }
            })
            ->where('status', 'active');

        $fees = $query->get();

        return response()->json($fees);
    }

    public function studentSummary($id)
    {
        $student = Student::findOrFail($id);
        $assignments = StudentFeeAssignment::with(['feeStructure.category', 'academicYear', 'discount'])
            ->where('student_id', $id)
            ->where('status', 'active')
            ->get();
            
        $totalAmount = $assignments->sum('amount');
        $totalDiscount = $assignments->sum('discount_amount');
        $netPayable = $assignments->sum('final_amount');
        
        // Calculate payments directly from fee_payments table via assignments
        $totalPaid = StudentFeeAssignment::where('student_id', $id)
            ->with('payments')
            ->get()
            ->sum(function ($assignment) {
                return $assignment->payments->sum('amount');
            });
        $balance = $netPayable - $totalPaid;

        return view('fee_management.assignments.student_summary', compact('student', 'assignments', 'totalAmount', 'totalDiscount', 'netPayable', 'totalPaid', 'balance'));
    }

    public function unassigned()
    {
        // Find students who have NO assignments for current active year
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $currentYear ? $currentYear->academic_year_id : 0;
        
        $assignedStudentIds = StudentFeeAssignment::where('academic_year_id', $yearId)
            ->where('status', 'active')
            ->pluck('student_id')
            ->unique();
            
        $students = Student::whereNotIn('student_id', $assignedStudentIds)
            ->where('status', 'active') // Active students only
            ->with(['studentClassEnrollments' => function($q) use ($yearId) {
                $q->where('academic_year_id', $yearId)->where('is_current', true);
            }, 'studentClassEnrollments.classSection.schoolClass'])
            ->paginate(20);
            
        return view('fee_management.assignments.unassigned', compact('students', 'currentYear'));
    }

    public function destroy($id)
    {
        $assignment = StudentFeeAssignment::findOrFail($id);

        $oldData = $assignment->toArray();
        $assignment->delete();

        AuditTrail::log('Fee Assignment', 'DELETE', $id, $oldData, null);

        Flash::success('Fee assignment removed successfully.');
        return redirect()->back();
    }

    protected function assignFeesToStudents($students, $fees, $academicYearId, $term, $count = 0, $isBulkAll = false)
    {
        $termId = \App\Models\Term::where('academic_year_id', $academicYearId)->where('code', $term)->value('id');

        $studentChunks = $students->chunk(100);

        foreach ($studentChunks as $chunk) {
            foreach ($chunk as $student) {
                foreach ($fees as $fee) {
                    if ($isBulkAll && $fee->class_id) {
                        $enrollment = $student->studentClassEnrollments()
                            ->where('is_current', true)
                            ->with('classSection')
                            ->first();

                        $studentClassId = $enrollment?->classSection?->class_id;

                        if ($fee->class_id != $studentClassId) {
                            continue;
                        }
                    }

                    $exists = StudentFeeAssignment::where('student_id', $student->student_id)
                        ->where('fee_structure_id', $fee->fee_structure_id)
                        ->where('academic_year_id', $academicYearId)
                        ->where('term', $term)
                        ->exists();

                    if (!$exists) {
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
                            'status' => 'active'
                        ]);

                        $count++;
                    }
                }
            }
        }

        return $count;
    }
}
