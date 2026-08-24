<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentFeeAssignment;
use App\Models\StudentDiscount;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\FeeCategory;
use App\Models\FeePayment;
use App\Services\FinanceService;
use DB;
use Barryvdh\DomPDF\Facade\Pdf;

class FeeReportsController extends Controller
{
    protected $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
        $this->middleware('can:fees.view');
    }

    public function expectedRevenue(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id', $currentYear ? $currentYear->academic_year_id : null);

        $query = StudentFeeAssignment::where('status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            });

        $totalOriginal = $query->sum('amount');
        $totalDiscounts = $query->sum('discount_amount');
        $totalExpected = $query->sum('final_amount');

        $totalCollected = FeePayment::join('student_fee_assignments', 'fee_payments.student_fee_assignment_id', '=', 'student_fee_assignments.id')
            ->where('student_fee_assignments.status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('student_fee_assignments.academic_year_id', $yearId);
            })
            ->sum('fee_payments.amount');

        $totalPending = $totalExpected - $totalCollected;
        $collectionRate = $totalExpected > 0 ? round(($totalCollected / $totalExpected) * 100, 1) : 0;

        $revenueByClass = StudentFeeAssignment::join('student_class_enrollments', 'student_fee_assignments.student_id', '=', 'student_class_enrollments.student_id')
            ->join('class_sections', 'student_class_enrollments.class_section_id', '=', 'class_sections.class_section_id')
            ->join('classes', 'class_sections.class_id', '=', 'classes.class_id')
            ->where('student_fee_assignments.status', 'active')
            ->where('student_class_enrollments.status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                 return $q->where('student_fee_assignments.academic_year_id', $yearId)
                          ->where('student_class_enrollments.academic_year_id', $yearId);
            })
            ->selectRaw(
                'classes.name as class_name, COUNT(DISTINCT student_fee_assignments.student_id) as student_count, SUM(student_fee_assignments.final_amount) as expected, (SELECT COALESCE(SUM(fp.amount), 0) FROM fee_payments fp INNER JOIN student_fee_assignments sfa ON fp.student_fee_assignment_id = sfa.id INNER JOIN student_class_enrollments sce ON sfa.student_id = sce.student_id INNER JOIN class_sections cs ON sce.class_section_id = cs.class_section_id WHERE cs.class_id = classes.class_id AND sfa.status = "active"'.($yearId ? ' AND sfa.academic_year_id = ? AND sce.academic_year_id = ?' : '').') as collected',
                $yearId ? [$yearId, $yearId] : []
            )
            ->groupBy('classes.class_id', 'classes.name')
            ->orderByDesc('expected')
            ->get();

        $revenueByCategory = StudentFeeAssignment::join('fee_structures', 'student_fee_assignments.fee_structure_id', '=', 'fee_structures.fee_structure_id')
            ->join('fee_categories', 'fee_structures.category_id', '=', 'fee_categories.category_id')
            ->where('student_fee_assignments.status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('student_fee_assignments.academic_year_id', $yearId);
            })
            ->select(
                'fee_categories.name as category_name',
                'fee_categories.type as category_type',
                DB::raw('COUNT(*) as assignment_count'),
                DB::raw('SUM(student_fee_assignments.final_amount) as total')
            )
            ->groupBy('fee_categories.category_id', 'fee_categories.name', 'fee_categories.type')
            ->orderByDesc('total')
            ->get();

        $paymentStatusBreakdown = [
            'paid' => 0,
            'partial' => 0,
            'unpaid' => 0,
        ];

        $paidCount = DB::table('student_fee_assignments as sfa')
            ->distinct()
            ->select('sfa.student_id')
            ->join(DB::raw('(SELECT student_fee_assignment_id, COALESCE(SUM(amount), 0) as total_paid FROM fee_payments GROUP BY student_fee_assignment_id) as p'), 'p.student_fee_assignment_id', '=', 'sfa.id')
            ->where('sfa.status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('sfa.academic_year_id', $yearId);
            })
            ->whereRaw('p.total_paid >= sfa.final_amount')
            ->count();

        $partialCount = DB::table('student_fee_assignments as sfa')
            ->distinct()
            ->select('sfa.student_id')
            ->join(DB::raw('(SELECT student_fee_assignment_id, COALESCE(SUM(amount), 0) as total_paid FROM fee_payments GROUP BY student_fee_assignment_id) as p'), 'p.student_fee_assignment_id', '=', 'sfa.id')
            ->where('sfa.status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('sfa.academic_year_id', $yearId);
            })
            ->whereRaw('p.total_paid > 0 AND p.total_paid < sfa.final_amount')
            ->count();

        $paymentStatusBreakdown['paid'] = $paidCount;
        $paymentStatusBreakdown['partial'] = $partialCount;

        $totalStudentsWithAssignments = (clone $query)->distinct('student_id')->count('student_id');
        $paymentStatusBreakdown['unpaid'] = max(0, $totalStudentsWithAssignments - $paymentStatusBreakdown['paid'] - $paymentStatusBreakdown['partial']);

        $academicYears = AcademicYear::pluck('name', 'academic_year_id');

        return view('fee_management.reports.expected_revenue', compact(
            'totalExpected',
            'totalOriginal',
            'totalDiscounts',
            'totalCollected',
            'totalPending',
            'collectionRate',
            'revenueByClass',
            'revenueByCategory',
            'paymentStatusBreakdown',
            'academicYears',
            'yearId'
        ));
    }

    public function assignmentStatus(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id', $currentYear ? $currentYear->academic_year_id : null);
        $classId = $request->get('class_id');
        $statusFilter = $request->get('payment_status');

        $query = StudentFeeAssignment::with(['student.studentClassEnrollments.classSection.schoolClass', 'student.studentClassEnrollments.classSection.section', 'feeStructure.category', 'payments'])
            ->where('status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            });

        if ($classId) {
            $query->whereHas('student.studentClassEnrollments.classSection', function($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        // Join with payment totals subquery to avoid only_full_group_by issues
        $query->leftJoin(DB::raw('(SELECT student_fee_assignment_id, COALESCE(SUM(amount), 0) as total_paid FROM fee_payments GROUP BY student_fee_assignment_id) as payment_totals'), function($join) {
            $join->on('student_fee_assignments.id', '=', 'payment_totals.student_fee_assignment_id');
        });

        if ($statusFilter === 'paid') {
            $query->whereRaw('COALESCE(payment_totals.total_paid, 0) >= student_fee_assignments.final_amount');
        } elseif ($statusFilter === 'partial') {
            $query->whereRaw('COALESCE(payment_totals.total_paid, 0) > 0 AND COALESCE(payment_totals.total_paid, 0) < student_fee_assignments.final_amount');
        } elseif ($statusFilter === 'unpaid') {
            $query->where(function($q) {
                $q->whereRaw('COALESCE(payment_totals.total_paid, 0) = 0')
                  ->orWhereNull('payment_totals.total_paid');
            });
        }

        $assignments = $query->distinct('student_fee_assignments.id')->paginate(20);
        $classes = SchoolClass::pluck('name', 'class_id');
        $academicYears = AcademicYear::pluck('name', 'academic_year_id');

        $stats = [
            'total' => (clone (clone $query)->getQuery())->count(),
            'paid' => 0,
            'partial' => 0,
            'unpaid' => 0,
        ];

        foreach ($assignments as $a) {
            if ($a->payment_status === 'paid') $stats['paid']++;
            elseif ($a->payment_status === 'partial') $stats['partial']++;
            else $stats['unpaid']++;
        }

        return view('fee_management.reports.assignment_status', compact('assignments', 'classes', 'academicYears', 'yearId', 'classId', 'statusFilter', 'stats'));
    }

    public function discountSummary(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id', $currentYear ? $currentYear->academic_year_id : null);

        $query = StudentFeeAssignment::with(['student.studentClassEnrollments.classSection.schoolClass', 'discount'])
            ->where('status', 'active')
            ->where('discount_amount', '>', 0)
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            });

        $discounts = $query->paginate(20);
        $totalDiscounts = (clone $query)->sum('discount_amount');
        $totalOriginalForDiscounted = (clone $query)->sum('amount');

        $discountSchemes = StudentFeeAssignment::join('discount_schemes', 'student_fee_assignments.discount_id', '=', 'discount_schemes.id')
            ->where('student_fee_assignments.status', 'active')
            ->where('student_fee_assignments.discount_amount', '>', 0)
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('student_fee_assignments.academic_year_id', $yearId);
            })
            ->select(
                'discount_schemes.name as scheme_name',
                'discount_schemes.eligibility_criteria as criteria',
                DB::raw('COUNT(*) as student_count'),
                DB::raw('SUM(student_fee_assignments.discount_amount) as total_discount')
            )
            ->groupBy('discount_schemes.id', 'discount_schemes.name', 'discount_schemes.eligibility_criteria')
            ->orderByDesc('total_discount')
            ->get();

        $academicYears = AcademicYear::pluck('name', 'academic_year_id');

        return view('fee_management.reports.discount_summary', compact(
            'discounts',
            'totalDiscounts',
            'totalOriginalForDiscounted',
            'discountSchemes',
            'academicYears',
            'yearId'
        ));
    }

    public function exportExpectedRevenuePdf(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id', $currentYear ? $currentYear->academic_year_id : null);

        $query = StudentFeeAssignment::where('status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            });

        $totalOriginal = $query->sum('amount');
        $totalDiscounts = $query->sum('discount_amount');
        $totalExpected = $query->sum('final_amount');

        $totalCollected = FeePayment::join('student_fee_assignments', 'fee_payments.student_fee_assignment_id', '=', 'student_fee_assignments.id')
            ->where('student_fee_assignments.status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('student_fee_assignments.academic_year_id', $yearId);
            })
            ->sum('fee_payments.amount');

        $revenueByClass = StudentFeeAssignment::join('student_class_enrollments', 'student_fee_assignments.student_id', '=', 'student_class_enrollments.student_id')
            ->join('class_sections', 'student_class_enrollments.class_section_id', '=', 'class_sections.class_section_id')
            ->join('classes', 'class_sections.class_id', '=', 'classes.class_id')
            ->where('student_fee_assignments.status', 'active')
            ->where('student_class_enrollments.status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                 return $q->where('student_fee_assignments.academic_year_id', $yearId)
                          ->where('student_class_enrollments.academic_year_id', $yearId);
            })
            ->select('classes.name as class_name', DB::raw('SUM(student_fee_assignments.final_amount) as total'))
            ->groupBy('classes.name')
            ->orderByDesc('total')
            ->get();

        $revenueByCategory = StudentFeeAssignment::join('fee_structures', 'student_fee_assignments.fee_structure_id', '=', 'fee_structures.fee_structure_id')
            ->join('fee_categories', 'fee_structures.category_id', '=', 'fee_categories.category_id')
            ->where('student_fee_assignments.status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('student_fee_assignments.academic_year_id', $yearId);
            })
            ->select('fee_categories.name as category_name', DB::raw('SUM(student_fee_assignments.final_amount) as total'))
            ->groupBy('fee_categories.name')
            ->orderByDesc('total')
            ->get();

        $collectionRate = $totalExpected > 0 ? round(($totalCollected / $totalExpected) * 100, 1) : 0;

        $pdf = Pdf::loadView('fee_management.reports.exports.expected_revenue_pdf', compact(
            'totalExpected', 'totalOriginal', 'totalDiscounts', 'totalCollected',
            'revenueByClass', 'revenueByCategory', 'collectionRate', 'yearId'
        ));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('expected-revenue-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportAssignmentStatusPdf(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id', $currentYear ? $currentYear->academic_year_id : null);
        $classId = $request->get('class_id');

        $query = StudentFeeAssignment::with(['student.studentClassEnrollments.classSection.schoolClass', 'feeStructure.category', 'payments'])
            ->where('status', 'active')
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            });

        if ($classId) {
            $query->whereHas('student.studentClassEnrollments.classSection', function($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $assignments = $query->get();

        $pdf = Pdf::loadView('fee_management.reports.exports.assignment_status_pdf', compact('assignments', 'yearId'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('assignment-status-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportDiscountSummaryPdf(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id', $currentYear ? $currentYear->academic_year_id : null);

        $query = StudentFeeAssignment::with(['student.studentClassEnrollments.classSection.schoolClass', 'discount'])
            ->where('status', 'active')
            ->where('discount_amount', '>', 0)
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('academic_year_id', $yearId);
            });

        $discounts = $query->get();
        $totalDiscounts = $query->sum('discount_amount');

        $discountSchemes = StudentFeeAssignment::join('discount_schemes', 'student_fee_assignments.discount_id', '=', 'discount_schemes.id')
            ->where('student_fee_assignments.status', 'active')
            ->where('student_fee_assignments.discount_amount', '>', 0)
            ->when($yearId, function($q) use ($yearId) {
                return $q->where('student_fee_assignments.academic_year_id', $yearId);
            })
            ->select(
                'discount_schemes.name as scheme_name',
                'discount_schemes.eligibility_criteria as criteria',
                DB::raw('COUNT(*) as student_count'),
                DB::raw('SUM(student_fee_assignments.discount_amount) as total_discount')
            )
            ->groupBy('discount_schemes.id', 'discount_schemes.name', 'discount_schemes.eligibility_criteria')
            ->orderByDesc('total_discount')
            ->get();

        $pdf = Pdf::loadView('fee_management.reports.exports.discount_summary_pdf', compact('discounts', 'totalDiscounts', 'discountSchemes', 'yearId'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('discount-summary-report-' . date('Y-m-d') . '.pdf');
    }
}
