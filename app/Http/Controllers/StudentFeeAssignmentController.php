<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentFeeAssignment;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\DiscountScheme;
use DB;
use Flash;

class StudentFeeAssignmentController extends Controller
{
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
        
        return view('fee_management.assignments.create', compact('classes', 'currentYear', 'feeCategories'));
    }

    public function store(Request $request)
    {
        // Handle Bulk and Individual assignments
        $request->validate([
            'assignment_type' => 'required|in:bulk_class,individual',
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
                })->where('status', 'active')->get();
                
                $fees = FeeStructure::whereIn('fee_structure_id', $request->fees)->get();

                foreach ($students as $student) {
                    foreach ($fees as $fee) {
                        // Check duplicate
                        $exists = StudentFeeAssignment::where('student_id', $student->student_id)
                            ->where('fee_structure_id', $fee->fee_structure_id)
                            ->where('academic_year_id', $academicYearId)
                            ->where('term', $term)
                            ->exists();

                        if (!$exists) {
                            $assignment = StudentFeeAssignment::create([
                                'student_id' => $student->student_id,
                                'fee_structure_id' => $fee->fee_structure_id,
                                'academic_year_id' => $academicYearId,
                                'term' => $term,
                                'amount' => $fee->amount,
                                'final_amount' => $fee->amount, // No discount initially in bulk
                                'assigned_by' => auth()->id(),
                                'status' => 'active'
                            ]);
                            
                            // Sync with student_fees (Collection Table)
                            \App\Models\StudentFee::updateOrCreate(
                                [
                                    'student_id' => $student->student_id,
                                    'fee_structure_id' => $fee->fee_structure_id,
                                ],
                                [
                                    'amount' => $fee->amount,
                                    'final_amount' => $fee->amount,
                                    'status' => 'unpaid',
                                    'due_date' => $fee->due_date
                                ]
                            );
                            
                            $count++;
                        }
                    }
                }
            } elseif ($request->assignment_type == 'individual') {
                 $request->validate([
                    'student_id' => 'required',
                    'fees' => 'required|array'
                ]);

                $student = Student::find($request->student_id);
                $fees = FeeStructure::whereIn('fee_structure_id', $request->fees)->get();

                 foreach ($fees as $fee) {
                        $exists = StudentFeeAssignment::where('student_id', $student->student_id)
                            ->where('fee_structure_id', $fee->fee_structure_id)
                            ->where('academic_year_id', $academicYearId)
                            ->where('term', $term)
                            ->exists();

                        if (!$exists) {
                            $assignment = StudentFeeAssignment::create([
                                'student_id' => $student->student_id,
                                'fee_structure_id' => $fee->fee_structure_id,
                                'academic_year_id' => $academicYearId,
                                'term' => $term,
                                'amount' => $fee->amount,
                                'final_amount' => $fee->amount,
                                'assigned_by' => auth()->id(),
                                'status' => 'active'
                            ]);
                            
                            // Sync with student_fees (Collection Table)
                            \App\Models\StudentFee::updateOrCreate(
                                [
                                    'student_id' => $student->student_id,
                                    'fee_structure_id' => $fee->fee_structure_id,
                                ],
                                [
                                    'amount' => $fee->amount,
                                    'final_amount' => $fee->amount,
                                    'status' => 'unpaid',
                                    'due_date' => $fee->due_date
                                ]
                            );
                            
                            $count++;
                        }
                 }
            }

            DB::commit();
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
            ->where('status', 'active')
            ->get();
            
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
        
        // Mock payment data until integration provided
        // In real scenario, we calculate payments from 'income' table
        $totalPaid = \App\Models\Income::where('student_id', $id)->sum('amount');
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
        
        // Sync with student_fees (Collection Table)
        \App\Models\StudentFee::where('student_id', $assignment->student_id)
            ->where('fee_structure_id', $assignment->fee_structure_id)
            ->delete();

        $assignment->delete(); // Or update status
        
        Flash::success('Fee assignment removed successfully.');
        return redirect()->back();
    }
}
