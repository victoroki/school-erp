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
        // Reads include the per-student summary, the unassigned-students list
        // and the four AJAX lookups the assignment screens call. None of them
        // had a gate, so a Teacher/Parent/Student could read the whole school
        // fee structure and per-student assignment details by URL.
        $this->middleware('can:fees.view')->only([
            'index', 'show', 'studentSummary', 'unassigned',
            'getFeesByClass', 'getFeesByClasses', 'getAutoAssignmentPreview', 'getAllFeeStructures',
        ]);
        $this->middleware('can:fees.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * The list defaults to a ROLL-UP: one row per class + fee + term + year
     * with the student count and money totals, instead of one raw row per
     * student-fee pair (which buried everything in thousands of lines).
     * Passing detail=1 — or searching by student name — switches to the
     * per-student rows for a group.
     */
    public function index(Request $request)
    {
        $classes = SchoolClass::orderBy('name')->pluck('name', 'class_id');
        $academicYears = AcademicYear::orderBy('academic_year_id', 'desc')->get(['academic_year_id', 'name']);
        $terms = Term::orderBy('display_order')->get(['academic_year_id', 'code', 'name']);

        $detailMode = $request->boolean('detail') || $request->filled('student_name');

        // ---- Aggregate metrics over the WHOLE filtered set. They used to be
        // sum() calls on $assignments, i.e. the current 15-row page only, which
        // made the cards change value as you paged.
        $statusFilter = $request->filled('status') && $request->status !== 'all' ? $request->status : null;
        $stats = StudentFeeAssignment::when($statusFilter, fn($q) => $q->where('status', $statusFilter))
            ->when($request->filled('class_id'), function ($q) use ($request) {
                $q->whereHas('student.studentClassEnrollments.classSection', function ($sq) use ($request) {
                    $sq->where('class_id', $request->class_id)->where('is_current', true);
                });
            })
            ->when($request->filled('academic_year_id'), fn($q) => $q->where('academic_year_id', $request->academic_year_id))
            ->when($request->filled('term'), fn($q) => $q->where('term', $request->term))
            ->selectRaw('COUNT(*) as assignments_total')
            ->selectRaw('COUNT(DISTINCT student_id) as students_billed')
            ->selectRaw('COALESCE(SUM(final_amount), 0) as total_net')
            ->selectRaw('COALESCE(SUM(COALESCE(paid_amount, 0)), 0) as total_collected')
            ->selectRaw('COUNT(DISTINCT CASE WHEN COALESCE(paid_amount, 0) < final_amount THEN student_id END) as students_owing')
            ->first();

        if ($detailMode) {
            $query = StudentFeeAssignment::with(['student.studentClassEnrollments.classSection.schoolClass', 'feeStructure.category', 'academicYear'])
                ->when($statusFilter, fn($q) => $q->where('status', $statusFilter), fn($q) => $q->where('status', 'active'));

            if ($request->filled('class_id')) {
                $query->whereHas('student.studentClassEnrollments.classSection', function($q) use ($request) {
                    $q->where('class_id', $request->class_id)
                      ->where('is_current', true);
                });
            }

            if ($request->filled('fee_structure_id')) {
                $query->where('fee_structure_id', $request->fee_structure_id);
            }
            if ($request->filled('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }
            if ($request->filled('term')) {
                $query->where('term', $request->term);
            }

            if ($request->filled('student_name')) {
                $query->whereHas('student', function($q) use ($request) {
                    $q->where(function ($nq) use ($request) {
                        $nq->where('first_name', 'like', "%{$request->student_name}%")
                           ->orWhere('last_name', 'like', "%{$request->student_name}%")
                           ->orWhere('admission_no', 'like', "%{$request->student_name}%");
                    });
                });
            }

            $assignments = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();
            $groupFee = $request->filled('fee_structure_id')
                ? FeeStructure::with(['category', 'schoolClass'])->find($request->fee_structure_id)
                : null;

            return view('fee_management.assignments.index', compact(
                'assignments', 'classes', 'academicYears', 'terms', 'stats', 'detailMode', 'groupFee'
            ) + ['rollups' => collect()]);
        }

        // ---- Roll-up: group student_fee_assignments by fee structure + term + year.
        // fs.class_id carries the target class; NULL means a global fee, which is
        // shown as "All Classes" and kept when filtering by a specific class since
        // those students were billed it too.
        $rollupQuery = DB::table('student_fee_assignments as sfa')
            ->join('fee_structures as fs', 'fs.fee_structure_id', '=', 'sfa.fee_structure_id')
            ->join('fee_categories as fc', 'fc.category_id', '=', 'fs.category_id')
            ->leftJoin('classes as c', 'c.class_id', '=', 'fs.class_id')
            ->leftJoin('academic_years as ay', 'ay.academic_year_id', '=', 'sfa.academic_year_id')
            ->leftJoin('terms as t', function ($j) {
                $j->on('t.academic_year_id', '=', 'sfa.academic_year_id')
                  ->on('t.code', '=', 'sfa.term');
            })
            ->when($statusFilter, fn($q) => $q->where('sfa.status', $statusFilter), fn($q) => $q->where('sfa.status', 'active'))
            ->when($request->filled('class_id'), function ($q) use ($request) {
                $q->where(function ($sq) use ($request) {
                    $sq->where('fs.class_id', $request->class_id)->orWhereNull('fs.class_id');
                });
            })
            ->when($request->filled('academic_year_id'), fn($q) => $q->where('sfa.academic_year_id', $request->academic_year_id))
            ->when($request->filled('term'), fn($q) => $q->where('sfa.term', $request->term))
            ->groupBy('fs.fee_structure_id', 'sfa.term', 'sfa.academic_year_id', 'fc.name', 'c.name', 't.name', 'ay.name')
            ->orderByRaw("COALESCE(c.name, 'zzz')")
            ->orderBy('fc.name')
            ->orderBy('sfa.term')
            ->selectRaw('fs.fee_structure_id')
            ->selectRaw('fs.class_id')
            ->selectRaw('fc.name as category_name')
            ->selectRaw('fs.payment_frequency')
            ->selectRaw('COALESCE(c.name, ?) as class_name', ['All Classes'])
            ->selectRaw('sfa.term')
            ->selectRaw('COALESCE(t.name, sfa.term) as term_name')
            ->selectRaw('sfa.academic_year_id')
            ->selectRaw('COALESCE(ay.name, ?) as year_name', ['—'])
            ->selectRaw('COUNT(DISTINCT sfa.student_id) as student_count')
            ->selectRaw('COUNT(DISTINCT CASE WHEN COALESCE(sfa.paid_amount, 0) >= sfa.final_amount THEN sfa.student_id END) as fully_paid_students')
            ->selectRaw('COALESCE(SUM(sfa.final_amount), 0) as total_net')
            ->selectRaw('COALESCE(SUM(COALESCE(sfa.paid_amount, 0)), 0) as total_collected')
            ->selectRaw('COALESCE(SUM(sfa.final_amount - COALESCE(sfa.paid_amount, 0)), 0) as total_balance');

        // COUNT(*) over a grouped query returns per-group counts, not the
        // number of groups, so the paginator total comes from a subquery.
        $perPage = 20;
        $page = max(1, (int) $request->get('page', 1));
        $total = DB::query()->fromSub($rollupQuery->clone(), 'rollup_groups')->count();
        $rollups = new \Illuminate\Pagination\LengthAwarePaginator(
            $rollupQuery->forPage($page, $perPage)->get(),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('fee_management.assignments.index', compact(
            'rollups', 'classes', 'academicYears', 'terms', 'stats', 'detailMode'
        ) + ['assignments' => collect()]);
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
        // The term is validated against the codes that actually exist for the
        // submitted academic year. It used to be accepted as a free string while
        // the form offered "Term 1"/"Term 2"/"Term 3" and terms.code holds
        // "T1".."T3" — so the resolver in assignFeesToStudents() never matched,
        // and every assignment was written with term = 'Term 1' and
        // term_id = NULL, dropping it out of every term-filtered arrears view.
        $validTermCodes = Term::where('academic_year_id', $request->academic_year_id)
            ->pluck('code')
            ->all();

        if ($validTermCodes === []) {
            Flash::error('No terms are defined for the selected academic year, so fees cannot be assigned against a term.');

            return redirect()->back()->withInput();
        }

        $request->validate([
            'assignment_type' => 'required|in:bulk_class,bulk_all,auto_all_classes,individual',
            'academic_year_id' => 'required',
            'term' => 'required|in:' . implode(',', $validTermCodes),
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
        
        // Paid and balance come from the single balance authority, so this screen
        // cannot disagree with the student profile, the fee list or the statement.
        // Summing $assignment->payments counted reversed payments (the rows are
        // deliberately kept for audit) and ignored payment_allocations, so a
        // student whose receipt had been voided, or who had paid a total balance
        // split across fees, was shown a different balance here.
        $summary = app(\App\Services\FeeBalanceService::class)->summaryForStudent((int) $id);
        $totalPaid = $summary['paid'];
        $balance = $summary['balance'];

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

        // Defence in depth. A NULL term_id silently removes the assignment from
        // term-filtered arrears, statements and reports, so refuse rather than
        // write an unscoped fee record.
        if (! $termId) {
            throw new \RuntimeException("The selected term ({$term}) does not exist for this academic year.");
        }

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
