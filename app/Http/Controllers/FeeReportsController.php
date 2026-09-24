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

    /**
     * Mirrors the grouped layout of /fees/assignments: a ROLL-UP of one row
     * per class by default, with a per-student detail drill-down (detail=1).
     * All money comes from the maintained paid_amount — which already
     * excludes reversed payments — never from raw fee_payments sums.
     */
    public function expectedRevenue(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id', $currentYear ? $currentYear->academic_year_id : null);
        $classId = $request->filled('class_id') ? (int) $request->class_id : null;

        $academicYears = AcademicYear::orderBy('academic_year_id', 'desc')->pluck('name', 'academic_year_id');
        $classes = SchoolClass::orderBy('name')->pluck('name', 'class_id');
        $detailMode = $request->boolean('detail');

        // ---- Headline metrics over the WHOLE filtered set.
        $base = StudentFeeAssignment::where('status', 'active')
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId));

        $stats = (clone $base)
            ->selectRaw('COUNT(DISTINCT student_id) as students_billed')
            ->selectRaw('COALESCE(SUM(final_amount), 0) as total_expected')
            ->selectRaw('COALESCE(SUM(COALESCE(paid_amount, 0)), 0) as total_collected')
            ->selectRaw('COALESCE(SUM(final_amount - COALESCE(paid_amount, 0)), 0) as total_pending')
            ->selectRaw('COALESCE(SUM(amount), 0) as total_original')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as total_discounts')
            ->selectRaw("COALESCE(SUM(CASE WHEN COALESCE(paid_amount, 0) >= final_amount THEN 1 ELSE 0 END), 0) as paid_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN COALESCE(paid_amount, 0) > 0 AND COALESCE(paid_amount, 0) < final_amount THEN 1 ELSE 0 END), 0) as partial_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN COALESCE(paid_amount, 0) = 0 THEN 1 ELSE 0 END), 0) as unpaid_count")
            ->first();

        $collectionRate = $stats->total_expected > 0 ? round(($stats->total_collected / $stats->total_expected) * 100, 1) : 0;

        if ($detailMode) {
            // ---- Per-student lines for one class (or all, if unfiltered).
            $assignments = StudentFeeAssignment::with(['student.studentClassEnrollments.classSection.schoolClass', 'feeStructure.category', 'academicYear', 'termModel'])
                ->where('status', 'active')
                ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
                ->when($classId, function ($q) use ($classId) {
                    $q->whereHas('student.studentClassEnrollments.classSection', function ($sq) use ($classId) {
                        $sq->where('class_id', $classId)->where('is_current', true);
                    });
                })
                ->orderByDesc('final_amount')
                ->paginate(25)
                ->withQueryString();

            $groupClass = $classId ? SchoolClass::find($classId) : null;

            return view('fee_management.reports.expected_revenue', compact(
                'stats', 'collectionRate', 'classes', 'academicYears', 'yearId', 'detailMode', 'assignments', 'groupClass'
            ) + ['rollups' => collect(), 'categories' => collect()]);
        }

        // ---- Roll-up: one row per class.
        $rollupQuery = DB::table('student_fee_assignments as sfa')
            ->join('student_class_enrollments as sce', function ($j) {
                $j->on('sce.student_id', '=', 'sfa.student_id')
                  ->where('sce.status', 'active');
            })
            ->join('class_sections as cs', 'cs.class_section_id', '=', 'sce.class_section_id')
            ->join('classes as c', 'c.class_id', '=', 'cs.class_id')
            ->where('sfa.status', 'active')
            ->when($yearId, function ($q) use ($yearId) {
                $q->where('sfa.academic_year_id', $yearId)->where('sce.academic_year_id', $yearId);
            })
            ->groupBy('c.class_id', 'c.name')
            ->orderBy('c.name')
            ->selectRaw('c.class_id')
            ->selectRaw('c.name as class_name')
            ->selectRaw('COUNT(DISTINCT sfa.student_id) as student_count')
            ->selectRaw('COALESCE(SUM(sfa.final_amount), 0) as total_expected')
            ->selectRaw('COALESCE(SUM(COALESCE(sfa.paid_amount, 0)), 0) as total_collected')
            ->selectRaw('COALESCE(SUM(sfa.final_amount - COALESCE(sfa.paid_amount, 0)), 0) as total_balance')
            ->selectRaw('COUNT(DISTINCT CASE WHEN COALESCE(sfa.paid_amount, 0) >= sfa.final_amount THEN sfa.student_id END) as fully_paid_students');

        // COUNT(*) over a grouped query returns per-group counts, so the
        // paginator total comes from a subquery.
        $perPage = 20;
        $page = max(1, (int) $request->get('page', 1));
        $total = DB::query()->fromSub($rollupQuery->clone(), 'rollup_classes')->count();
        $rollups = new \Illuminate\Pagination\LengthAwarePaginator(
            $rollupQuery->forPage($page, $perPage)->get(),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Secondary breakdown by fee category (expected amounts only — no
        // payment data involved, so no reversal exposure).
        $categories = StudentFeeAssignment::join('fee_structures', 'student_fee_assignments.fee_structure_id', '=', 'fee_structures.fee_structure_id')
            ->join('fee_categories', 'fee_structures.category_id', '=', 'fee_categories.category_id')
            ->where('student_fee_assignments.status', 'active')
            ->when($yearId, fn ($q) => $q->where('student_fee_assignments.academic_year_id', $yearId))
            ->select(
                'fee_categories.name as category_name',
                'fee_categories.type as category_type',
                DB::raw('COUNT(*) as assignment_count'),
                DB::raw('SUM(student_fee_assignments.final_amount) as total')
            )
            ->groupBy('fee_categories.category_id', 'fee_categories.name', 'fee_categories.type')
            ->orderByDesc('total')
            ->get();

        return view('fee_management.reports.expected_revenue', compact(
            'stats', 'collectionRate', 'rollups', 'categories', 'classes', 'academicYears', 'yearId', 'detailMode'
        ) + ['assignments' => collect(), 'groupClass' => null]);
    }

    /**
     * Mirrors the grouped layout of /fees/assignments: a ROLL-UP of one row
     * per fee + class + term + year by default, with a per-student detail
     * drill-down (detail=1 or a student-name search). The old version was a
     * flat per-student list that also summed raw fee_payments — counting
     * reversed payments — instead of the maintained paid_amount.
     */
    public function assignmentStatus(Request $request)
    {
        $classes = SchoolClass::orderBy('name')->pluck('name', 'class_id');
        $academicYears = AcademicYear::orderBy('academic_year_id', 'desc')->get(['academic_year_id', 'name']);
        $terms = \App\Models\Term::orderBy('display_order')->get(['academic_year_id', 'code', 'name']);

        $detailMode = $request->boolean('detail') || $request->filled('student_name');
        $statusFilter = $request->filled('payment_status') ? $request->payment_status : null;

        // ---- Headline metrics over the WHOLE filtered set (not just the page).
        $statFilters = fn ($q) => $q
            ->when($request->filled('academic_year_id'), fn ($q) => $q->where('academic_year_id', $request->academic_year_id))
            ->when($request->filled('term'), fn ($q) => $q->where('term', $request->term))
            ->when($request->filled('class_id'), function ($q) use ($request) {
                $q->whereHas('student.studentClassEnrollments.classSection', function ($sq) use ($request) {
                    $sq->where('class_id', $request->class_id)->where('is_current', true);
                });
            });

        $stats = StudentFeeAssignment::where('status', 'active')
            ->where($statFilters)
            ->selectRaw('COUNT(*) as assignments_total')
            ->selectRaw('COUNT(DISTINCT student_id) as students_billed')
            ->selectRaw('COALESCE(SUM(final_amount), 0) as total_net')
            ->selectRaw('COALESCE(SUM(COALESCE(paid_amount, 0)), 0) as total_collected')
            ->selectRaw('COALESCE(SUM(final_amount - COALESCE(paid_amount, 0)), 0) as total_balance')
            ->selectRaw('COUNT(DISTINCT CASE WHEN COALESCE(paid_amount, 0) < final_amount THEN student_id END) as students_owing')
            ->first();

        if ($detailMode) {
            // ---- Per-student lines for one group (or a name search).
            $query = StudentFeeAssignment::with(['student.studentClassEnrollments.classSection.schoolClass', 'feeStructure.category', 'academicYear', 'termModel'])
                ->where('status', 'active')
                ->where($statFilters);

            $query->when($request->filled('fee_structure_id'), fn ($q) => $q->where('fee_structure_id', $request->fee_structure_id));

            $query->when($request->filled('student_name'), function ($q) use ($request) {
                $q->whereHas('student', function ($nq) use ($request) {
                    $nq->where(function ($w) use ($request) {
                        $w->where('first_name', 'like', "%{$request->student_name}%")
                          ->orWhere('last_name', 'like', "%{$request->student_name}%")
                          ->orWhere('admission_no', 'like', "%{$request->student_name}%");
                    });
                });
            });

            // Payment-status filter uses the maintained paid_amount, so a
            // reversed payment no longer flips a student back to "paid".
            $query->when($statusFilter, function ($q) use ($statusFilter) {
                if ($statusFilter === 'paid') {
                    $q->whereRaw('COALESCE(paid_amount, 0) >= final_amount');
                } elseif ($statusFilter === 'partial') {
                    $q->whereRaw('COALESCE(paid_amount, 0) > 0 AND COALESCE(paid_amount, 0) < final_amount');
                } elseif ($statusFilter === 'unpaid') {
                    $q->whereRaw('COALESCE(paid_amount, 0) = 0');
                }
            });

            $assignments = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

            $groupFee = $request->filled('fee_structure_id')
                ? \App\Models\FeeStructure::with(['category', 'schoolClass'])->find($request->fee_structure_id)
                : null;

            return view('fee_management.reports.assignment_status', compact(
                'assignments', 'classes', 'academicYears', 'terms', 'stats', 'detailMode', 'groupFee'
            ) + ['rollups' => collect()]);
        }

        // ---- Roll-up: one row per fee + class + term + year, money from the
        // maintained paid_amount (reversed payments already excluded there).
        $rollupQuery = DB::table('student_fee_assignments as sfa')
            ->join('fee_structures as fs', 'fs.fee_structure_id', '=', 'sfa.fee_structure_id')
            ->join('fee_categories as fc', 'fc.category_id', '=', 'fs.category_id')
            ->leftJoin('classes as c', 'c.class_id', '=', 'fs.class_id')
            ->leftJoin('academic_years as ay', 'ay.academic_year_id', '=', 'sfa.academic_year_id')
            ->leftJoin('terms as t', function ($j) {
                $j->on('t.academic_year_id', '=', 'sfa.academic_year_id')
                  ->on('t.code', '=', 'sfa.term');
            })
            ->where('sfa.status', 'active')
            ->when($request->filled('academic_year_id'), fn ($q) => $q->where('sfa.academic_year_id', $request->academic_year_id))
            ->when($request->filled('term'), fn ($q) => $q->where('sfa.term', $request->term))
            ->when($request->filled('class_id'), function ($q) use ($request) {
                $q->where(function ($sq) use ($request) {
                    $sq->where('fs.class_id', $request->class_id)->orWhereNull('fs.class_id');
                });
            })
            ->groupBy('fs.fee_structure_id', 'sfa.term', 'sfa.academic_year_id', 'fc.name', 'c.name', 't.name', 'ay.name')
            ->orderByRaw("COALESCE(c.name, 'zzz')")
            ->orderBy('fc.name')
            ->orderBy('sfa.term')
            ->selectRaw('fs.fee_structure_id')
            ->selectRaw('fs.class_id')
            ->selectRaw('fc.name as category_name')
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

        // COUNT(*) over a grouped query returns per-group counts, so the
        // paginator total comes from a subquery.
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

        return view('fee_management.reports.assignment_status', compact(
            'rollups', 'classes', 'academicYears', 'terms', 'stats', 'detailMode'
        ) + ['assignments' => collect(), 'groupFee' => null]);
    }

    /**
     * Mirrors the grouped layout of /fees/assignments: a ROLL-UP of one row
     * per discount scheme by default, with a per-student drill-down per
     * scheme (detail=1). No payment sums involved — discounts live on the
     * assignment itself, so there is no reversal exposure here.
     */
    public function discountSummary(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id', $currentYear ? $currentYear->academic_year_id : null);
        $schemeId = $request->filled('scheme_id') ? (int) $request->scheme_id : null;
        $detailMode = $request->boolean('detail') || $request->filled('student_name');

        $academicYears = AcademicYear::orderBy('academic_year_id', 'desc')->pluck('name', 'academic_year_id');

        // ---- Headline metrics over the WHOLE filtered set.
        $stats = StudentFeeAssignment::where('status', 'active')
            ->where('discount_amount', '>', 0)
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->selectRaw('COUNT(DISTINCT student_id) as students_count')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as total_discounts')
            ->selectRaw('COALESCE(SUM(amount), 0) as total_original')
            ->selectRaw('COALESCE(SUM(final_amount), 0) as total_final')
            ->first();

        if ($detailMode) {
            // ---- Per-student lines for one scheme (or a name search).
            $discounts = StudentFeeAssignment::with(['student.studentClassEnrollments.classSection.schoolClass', 'discount'])
                ->where('status', 'active')
                ->where('discount_amount', '>', 0)
                ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
                ->when($schemeId, fn ($q) => $q->where('discount_id', $schemeId))
                ->when($request->filled('student_name'), function ($q) use ($request) {
                    $q->whereHas('student', function ($nq) use ($request) {
                        $nq->where(function ($w) use ($request) {
                            $w->where('first_name', 'like', "%{$request->student_name}%")
                              ->orWhere('last_name', 'like', "%{$request->student_name}%")
                              ->orWhere('admission_no', 'like', "%{$request->student_name}%");
                        });
                    });
                })
                ->orderByDesc('discount_amount')
                ->paginate(20)
                ->withQueryString();

            $groupScheme = $schemeId ? \App\Models\DiscountScheme::find($schemeId) : null;

            return view('fee_management.reports.discount_summary', compact(
                'stats', 'academicYears', 'yearId', 'detailMode', 'discounts', 'groupScheme'
            ) + ['schemes' => collect()]);
        }

        // ---- Roll-up: one row per discount scheme. Manual discounts
        // (discount_id null) group under their own row.
        $schemes = StudentFeeAssignment::query()
            ->where('student_fee_assignments.status', 'active')
            ->where('student_fee_assignments.discount_amount', '>', 0)
            ->when($yearId, fn ($q) => $q->where('student_fee_assignments.academic_year_id', $yearId))
            ->leftJoin('discount_schemes', 'student_fee_assignments.discount_id', '=', 'discount_schemes.id')
            ->select(
                'discount_schemes.id as scheme_id',
                DB::raw("COALESCE(discount_schemes.name, 'Manual / Unlinked') as scheme_name"),
                DB::raw("COALESCE(discount_schemes.eligibility_criteria, '') as criteria"),
                DB::raw('COUNT(DISTINCT student_fee_assignments.student_id) as student_count'),
                DB::raw('SUM(student_fee_assignments.discount_amount) as total_discount'),
                DB::raw('SUM(student_fee_assignments.amount) as total_original')
            )
            ->groupBy('discount_schemes.id', 'discount_schemes.name', 'discount_schemes.eligibility_criteria')
            ->orderByDesc('total_discount')
            ->get();

        return view('fee_management.reports.discount_summary', compact(
            'stats', 'schemes', 'academicYears', 'yearId', 'detailMode'
        ) + ['discounts' => collect(), 'groupScheme' => null]);
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
            ->notReversed()
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

    /**
     * Daily / term / period collection report.
     */
    public function collections(Request $request)
    {
        $paymentQuery = FeePayment::query()
            ->with(['studentFeeAssignment.student', 'studentFeeAssignment.feeStructure.category'])
            ->when($request->filled('date'), function ($q) use ($request) {
                return $q->whereDate('payment_date', $request->date);
            })
            ->when($request->filled('from'), function ($q) use ($request) {
                return $q->whereDate('payment_date', '>=', $request->from);
            })
            ->when($request->filled('to'), function ($q) use ($request) {
                return $q->whereDate('payment_date', '<=', $request->to);
            })
            ->when($request->filled('payment_method'), function ($q) use ($request) {
                // "__unspecified" is the sentinel for legacy rows imported before
                // the ENUM was enforced; MySQL stored those as ''.
                if ($request->payment_method === '__unspecified') {
                    return $q->where(fn ($sub) => $sub->where('payment_method', '')->orWhereNull('payment_method'));
                }

                return $q->where('payment_method', $request->payment_method);
            });

        // Money figures exclude reversed payments; the paginated list below keeps
        // them so a voided receipt stays visible for audit (marked VOID in the
        // view). This is the invariant documented on FeePayment::scopeNotReversed.
        $totalCollected = (clone $paymentQuery)->notReversed()->sum('amount');
        $paymentCount = (clone $paymentQuery)->notReversed()->count();
        $reversedTotal = (clone $paymentQuery)->reversed()->sum('amount');
        $reversedCount = (clone $paymentQuery)->reversed()->count();

        $payments = (clone $paymentQuery)->orderByDesc('payment_date')->paginate(25)->withQueryString();

        // Payment method breakdown for the selected period. This must honour the
        // method filter too — it previously applied only the date filters while
        // being labelled "By Method (Filtered)".
        $byMethod = (clone $paymentQuery)
            ->notReversed()
            ->reorder()
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                // Legacy rows imported before the ENUM was enforced carry ''.
                $row->label = $row->payment_method === '' || $row->payment_method === null
                    ? 'Unspecified'
                    : \Illuminate\Support\Str::title(str_replace('_', ' ', $row->payment_method));

                return $row;
            });

        $todayTotal = FeePayment::notReversed()->whereDate('payment_date', today())->sum('amount');
        $yesterdayTotal = FeePayment::notReversed()->whereDate('payment_date', today()->subDay())->sum('amount');
        $growth = $yesterdayTotal > 0 ? round((($todayTotal - $yesterdayTotal) / $yesterdayTotal) * 100, 1) : 0;

        // Money paid back out over the same period, so "net cash position"
        // can be shown next to the gross collected figure.
        $refundQuery = \App\Models\Refund::query()
            ->where('status', 'completed')
            ->when($request->filled('from'), fn ($q) => $q->whereDate('completed_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('completed_at', '<=', $request->to))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('completed_at', $request->date));
        $refundedTotal = (float) (clone $refundQuery)->sum('amount');
        $refundedCount = (clone $refundQuery)->count();

        return view('fee_management.reports.collections', compact(
            'payments', 'totalCollected', 'paymentCount', 'byMethod', 'todayTotal', 'growth',
            'reversedTotal', 'reversedCount', 'refundedTotal', 'refundedCount'
        ));
    }

    /**
     * Collections grouped by payment method (over a period or overall).
     * Mirrors the grouped layout of /fees/assignments: a ROLL-UP of one row
     * per method by default, with a per-day drill-down per method (detail=1).
     * All money excludes reversed payments via notReversed().
     */
    public function paymentMethod(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id', $currentYear ? $currentYear->academic_year_id : null);
        $detailMode = $request->boolean('detail');
        $methodFilter = $request->filled('payment_method') ? $request->payment_method : null;

        $applyPeriod = fn ($q) => $q
            ->when($yearId, fn ($w) => $w->where('sfa.academic_year_id', $yearId))
            ->when($request->filled('from'), fn ($w) => $w->whereDate('fee_payments.payment_date', '>=', $request->from))
            ->when($request->filled('to'), fn ($w) => $w->whereDate('fee_payments.payment_date', '<=', $request->to));

        $baseQuery = fn () => FeePayment::query()
            ->join('student_fee_assignments as sfa', 'fee_payments.student_fee_assignment_id', '=', 'sfa.id')
            ->notReversed();

        $academicYears = AcademicYear::orderBy('academic_year_id', 'desc')->pluck('name', 'academic_year_id');

        // ---- Headline metrics over the WHOLE filtered set.
        $stats = $baseQuery()
            ->where($applyPeriod)
            ->selectRaw('COUNT(*) as payments_count')
            ->selectRaw('COALESCE(SUM(fee_payments.amount), 0) as grand_total')
            ->selectRaw('COUNT(DISTINCT fee_payments.payment_method) as methods_used')
            ->selectRaw('COUNT(DISTINCT CASE WHEN fee_payments.payment_date = CURDATE() THEN sfa.student_id END) as today_payers')
            ->first();

        $methodLabels = collect(FeePayment::PAYMENT_METHODS)
            ->mapWithKeys(fn ($m) => [$m => ucwords(str_replace('_', ' ', $m))]);

        if ($detailMode && $methodFilter) {
            // ---- Per-day breakdown for one method.
            $byDay = $baseQuery()
                ->where($applyPeriod)
                ->where('fee_payments.payment_method', $methodFilter)
                ->selectRaw('DATE(fee_payments.payment_date) as day')
                ->selectRaw('COUNT(*) as count')
                ->selectRaw('SUM(fee_payments.amount) as total')
                ->groupByRaw('DATE(fee_payments.payment_date)')
                ->orderByDesc('day')
                ->paginate(20);

            $byDay->withQueryString();

            return view('fee_management.reports.payment_method', compact(
                'stats', 'methodLabels', 'academicYears', 'yearId', 'detailMode', 'byDay', 'methodFilter'
            ) + ['byMethod' => collect()]);
        }

        // ---- Roll-up: one row per method (plus legacy '' rows as Unspecified).
        $byMethod = $baseQuery()
            ->where($applyPeriod)
            ->select('fee_payments.payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(fee_payments.amount) as total'), DB::raw('MIN(fee_payments.payment_date) as first_payment'), DB::raw('MAX(fee_payments.payment_date) as last_payment'))
            ->groupBy('fee_payments.payment_method')
            ->orderByDesc('total')
            ->get();

        $grandTotal = $byMethod->sum('total');

        return view('fee_management.reports.payment_method', compact(
            'byMethod', 'grandTotal', 'stats', 'methodLabels', 'academicYears', 'yearId', 'detailMode'
        ) + ['byDay' => collect(), 'methodFilter' => null]);
    }

    /**
     * Receipt register — lists all receipts issued.
     */
    public function receiptRegister(Request $request)
    {
        // Shared filters for every query below. Reversed receipts are retained
        // for audit (marked VOID in the view) but never counted as money.
        $applyFilters = function ($q) use ($request) {
            $q->when($request->filled('date'), fn ($w) => $w->whereDate('payment_date', $request->date))
                ->when($request->filled('from'), fn ($w) => $w->whereDate('payment_date', '>=', $request->from))
                ->when($request->filled('to'), fn ($w) => $w->whereDate('payment_date', '<=', $request->to))
                ->when($request->filled('receipt_number'), fn ($w) => $w->where('receipt_number', 'like', '%' . $request->receipt_number . '%'))
                ->when($request->filled('payment_method'), fn ($w) => $w->where('payment_method', $request->payment_method))
                ->when($request->filled('collected_by'), fn ($w) => $w->where('collected_by', $request->collected_by));
        };

        $classes = collect();
        $academicYears = collect();
        $terms = collect();
        $methods = collect(FeePayment::PAYMENT_METHODS)
            ->mapWithKeys(fn ($m) => [$m => ucwords(str_replace('_', ' ', $m))]);
        $collectors = \App\Models\Staff::query()
            ->select('staff_id')
            ->selectRaw("CONCAT(first_name, ' ', last_name) as dropdown_name")
            ->orderBy('first_name')
            ->pluck('dropdown_name', 'staff_id');

        $detailMode = $request->boolean('detail') || $request->filled('receipt_number');

        // ---- Headline metrics over the WHOLE filtered set.
        $stats = FeePayment::query()
            ->where($applyFilters)
            ->selectRaw('COUNT(*) as receipts_total')
            ->selectRaw('SUM(CASE WHEN reversed_at IS NULL THEN 1 ELSE 0 END) as valid_count')
            ->selectRaw('SUM(CASE WHEN reversed_at IS NOT NULL THEN 1 ELSE 0 END) as void_count')
            ->selectRaw("COALESCE(SUM(CASE WHEN reversed_at IS NULL THEN amount ELSE 0 END), 0) as total_collected")
            ->selectRaw("COALESCE(SUM(CASE WHEN reversed_at IS NOT NULL THEN amount ELSE 0 END), 0) as voided_amount")
            ->selectRaw('COUNT(DISTINCT CASE WHEN reversed_at IS NULL THEN collected_by END) as collectors_count')
            ->first();

        if ($detailMode) {
            // ---- Per-receipt lines for one day (or a receipt-number search).
            $receipts = FeePayment::query()
                ->with(['studentFeeAssignment.student', 'studentFeeAssignment.feeStructure.category', 'collectedBy'])
                ->where($applyFilters)
                ->orderByDesc('payment_date')
                ->orderByDesc('payment_id')
                ->paginate(25)
                ->withQueryString();

            $detailLabel = $request->filled('receipt_number')
                ? 'Receipt search: "' . $request->receipt_number . '"'
                : ($request->date
                    ? 'Receipts issued on ' . \Carbon\Carbon::parse($request->date)->format('d M Y')
                    : 'All matching receipts');

            return view('fee_management.reports.receipt_register', compact(
                'receipts', 'stats', 'detailMode', 'detailLabel', 'methods', 'collectors'
            ) + ['rollups' => collect(), 'classes' => $classes, 'academicYears' => $academicYears, 'terms' => $terms]);
        }

        // ---- Roll-up: one row per issue day.
        $rollupQuery = DB::table('fee_payments as fp')
            ->where($applyFilters)
            ->selectRaw('DATE(fp.payment_date) as day')
            ->selectRaw('COUNT(*) as receipts_total')
            ->selectRaw('SUM(CASE WHEN fp.reversed_at IS NULL THEN 1 ELSE 0 END) as valid_count')
            ->selectRaw('SUM(CASE WHEN fp.reversed_at IS NOT NULL THEN 1 ELSE 0 END) as void_count')
            ->selectRaw("COALESCE(SUM(CASE WHEN fp.reversed_at IS NULL THEN fp.amount ELSE 0 END), 0) as total_collected")
            ->selectRaw("COALESCE(SUM(CASE WHEN fp.reversed_at IS NOT NULL THEN fp.amount ELSE 0 END), 0) as voided_amount")
            ->selectRaw('GROUP_CONCAT(DISTINCT fp.payment_method) as methods')
            ->groupByRaw('DATE(fp.payment_date)')
            ->orderByDesc('day');

        // COUNT(*) over a grouped query returns per-group counts, so the
        // paginator total comes from a subquery.
        $perPage = 20;
        $page = max(1, (int) $request->get('page', 1));
        $total = DB::query()->fromSub($rollupQuery->clone(), 'rollup_days')->count();
        $rollups = new \Illuminate\Pagination\LengthAwarePaginator(
            $rollupQuery->forPage($page, $perPage)->get(),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Voided receipts must stay visible on the register's main screen. The
        // roll-up groups by day, so without this list a voided receipt could
        // only be found after drilling into its day — which makes the reversal
        // audit trail hard to reach. Capped and flagged by the view.
        $voidedReceipts = FeePayment::query()
            ->with(['studentFeeAssignment.student'])
            ->where($applyFilters)
            ->reversed()
            ->orderByDesc('payment_date')
            ->orderByDesc('payment_id')
            ->limit(100)
            ->get();

        return view('fee_management.reports.receipt_register', compact(
            'rollups', 'stats', 'detailMode', 'methods', 'collectors', 'voidedReceipts'
        ) + ['receipts' => collect(), 'detailLabel' => null, 'classes' => $classes, 'academicYears' => $academicYears, 'terms' => $terms]);
    }

    /**
     * Print-friendly PDF of the receipt register honouring the same filters
     * as the page. Day-grouped when the result set is small enough to stay
     * printable; flat chronologically otherwise (so a full-year register
     * cannot exhaust memory).
     */
    public function exportReceiptRegisterPdf(Request $request)
    {
        $applyFilters = function ($q) use ($request) {
            $q->when($request->filled('date'), fn ($w) => $w->whereDate('payment_date', $request->date))
                ->when($request->filled('from'), fn ($w) => $w->whereDate('payment_date', '>=', $request->from))
                ->when($request->filled('to'), fn ($w) => $w->whereDate('payment_date', '<=', $request->to))
                ->when($request->filled('receipt_number'), fn ($w) => $w->where('receipt_number', 'like', '%' . $request->receipt_number . '%'))
                ->when($request->filled('payment_method'), fn ($w) => $w->where('payment_method', $request->payment_method))
                ->when($request->filled('collected_by'), fn ($w) => $w->where('collected_by', $request->collected_by));
        };

        $baseQuery = FeePayment::query()
            ->with(['studentFeeAssignment.student', 'studentFeeAssignment.feeStructure.category', 'collectedBy'])
            ->where($applyFilters);

        $count = (clone $baseQuery)->count();

        if ($count <= 300) {
            $receipts = (clone $baseQuery)->orderBy('payment_date')->orderBy('payment_id')->get()
                ->groupBy(fn ($p) => optional($p->payment_date)->format('Y-m-d') ?? 'undated');
            $chunked = false;
        } else {
            // Large registers print flat to bound memory: DomPDF cannot hold
            // thousands of eager-loaded rows grouped in memory comfortably.
            $receipts = (clone $baseQuery)->orderBy('payment_date')->orderBy('payment_id')->get();
            $chunked = true;
        }

        $validTotal = (clone $baseQuery)->notReversed()->sum('amount');
        $validCount = (clone $baseQuery)->notReversed()->count();
        $voidedTotal = (clone $baseQuery)->reversed()->sum('amount');
        $voidedCount = (clone $baseQuery)->reversed()->count();

        $filterLabel = collect([
            'date' => $request->filled('date') ? 'Date: ' . \Carbon\Carbon::parse($request->date)->format('d M Y') : null,
            'from' => $request->filled('from') ? 'From: ' . \Carbon\Carbon::parse($request->from)->format('d M Y') : null,
            'to' => $request->filled('to') ? 'To: ' . \Carbon\Carbon::parse($request->to)->format('d M Y') : null,
            'receipt' => $request->filled('receipt_number') ? 'Receipt: ' . $request->receipt_number : null,
            'method' => $request->filled('payment_method') ? 'Method: ' . ucwords(str_replace('_', ' ', $request->payment_method)) : null,
            'collector' => $request->filled('collected_by') ? 'Collector: ' . (\App\Models\Staff::find($request->collected_by)?->full_name ?? $request->collected_by) : null,
        ])->filter()->values()->implode(' · '); 

        $pdf = Pdf::loadView('fee_management.reports.exports.receipt_register_pdf', compact(
            'receipts', 'chunked', 'validTotal', 'validCount', 'voidedTotal', 'voidedCount', 'filterLabel', 'count'
        ));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('receipt-register-' . date('Y-m-d') . '.pdf');
    }
}
