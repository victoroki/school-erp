<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use App\Models\FeePayment;
use App\Models\Term;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use DB;

class FeeArrearsController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:fees.view');
    }

    /**
     * Arrears dashboard & student list.
     *
     * Identifies students with outstanding balances, offers class/form/fee filters,
     * sorting by largest balance, export to PDF/CSV, and per-student statement links.
     */
    public function index(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id', $currentYear ? $currentYear->academic_year_id : null);
        $classId = $request->get('class_id');
        $termId = $request->get('term_id');
        $minAmount = $request->get('min_amount');
        $search = $request->get('search');

        // Scope: active assignments within the selected year/term.
        $base = StudentFeeAssignment::query()
            ->where('status', 'active')
            ->when($yearId, fn($q) => $q->where('academic_year_id', $yearId))
            ->when($termId, fn($q) => $q->where('term_id', $termId));

        // Payment totals per assignment to compute balances in SQL (derived table
        // avoids the ONLY_FULL_GROUP_BY issue caused by correlated subqueries).
        $paidTotalsSub = DB::raw('(SELECT fp.student_fee_assignment_id,
                COALESCE(SUM(fp.amount),0) AS paid_total
            FROM fee_payments fp
            GROUP BY fp.student_fee_assignment_id) AS paid_totals');

        // Per-student aggregation of expected vs paid.
        //
        // The grouped aggregation is built as an inner subquery and then wrapped
        // in an outer derived table. This keeps ORDER BY on the computed
        // expected_total/paid_total columns outside of the GROUP BY, which would
        // otherwise break under MySQL's sql_mode=only_full_group_by when the
        // paginator adds a LIMIT (and its own count wrapper) on top.
        $inner = Student::query()
            ->join('student_fee_assignments as sfa', 'sfa.student_id', '=', 'students.student_id')
            ->leftJoin($paidTotalsSub, 'paid_totals.student_fee_assignment_id', '=', 'sfa.id')
            ->where('sfa.status', 'active')
            ->when($yearId, fn($q) => $q->where('sfa.academic_year_id', $yearId))
            ->when($termId, fn($q) => $q->where('sfa.term_id', $termId))
            ->when($classId, function ($q) use ($classId) {
                return $q->whereHas('studentClassEnrollments.classSection', fn($cq) => $cq->where('class_id', $classId));
            })
            ->when($search, function ($q) use ($search) {
                return $q->where(function ($w) use ($search) {
                    $w->where('students.first_name', 'like', "%{$search}%")
                      ->orWhere('students.last_name', 'like', "%{$search}%")
                      ->orWhere('students.admission_no', 'like', "%{$search}%");
                });
            })
            ->selectRaw('
                students.student_id, students.first_name, students.middle_name, students.last_name, students.admission_no,
                SUM(sfa.final_amount) as expected_total,
                COALESCE(SUM(paid_totals.paid_total),0) as paid_total
            ')
            ->groupBy('students.student_id', 'students.first_name', 'students.middle_name', 'students.last_name', 'students.admission_no')
            ->havingRaw('expected_total > paid_total');

        // Optional minimum outstanding amount filter.
        if ($request->filled('min_amount')) {
            $inner->havingRaw('(expected_total - paid_total) >= ?', [(float) $minAmount]);
        }

        $sort = $request->get('sort', 'largest');
        $orderDir = ($sort === 'smallest') ? 'asc' : 'desc';

        $arrearsQuery = DB::table(DB::raw('(' . $inner->toSql() . ') as a'))
            ->mergeBindings($inner->getQuery())
            ->select(
                'a.student_id',
                'a.first_name',
                'a.middle_name',
                'a.last_name',
                'a.admission_no',
                'a.expected_total',
                'a.paid_total'
            )
            ->orderByRaw('(a.expected_total - a.paid_total) ' . $orderDir);

        $arrears = $arrearsQuery->paginate(20)->withQueryString();

        // Attach the student's current class stream for display (the arrears
        // rows come from a derived-table query so the class relation isn't
        // eager-loaded automatically).
        $studentIds = collect($arrears->items())->pluck('student_id')->filter()->all();
        $classMap = \App\Models\StudentClassEnrollment::with(['classSection.schoolClass', 'classSection.section'])
            ->whereIn('student_id', $studentIds)
            ->where('is_current', true)
            ->get()
            ->mapWithKeys(function ($enr) {
                return [$enr->student_id => ($enr->classSection->schoolClass->name ?? 'N/A') . ($enr->classSection->section->name ? ' - ' . $enr->classSection->section->name : '')];
            });
        foreach ($arrears as $item) {
            $item->studentClass = $classMap[$item->student_id] ?? 'N/A';
        }

        // Summary metrics.
        $totalExpected = (clone $base)->sum('final_amount');
        $totalCollected = FeePayment::join('student_fee_assignments as sfa2', 'fee_payments.student_fee_assignment_id', '=', 'sfa2.id')
            ->where('sfa2.status', 'active')
            ->when($yearId, fn($q) => $q->where('sfa2.academic_year_id', $yearId))
            ->when($termId, fn($q) => $q->where('sfa2.term_id', $termId))
            ->sum('fee_payments.amount');
        $totalOutstanding = $totalExpected - $totalCollected;
        $collectionRate = $totalExpected > 0 ? round(($totalCollected / $totalExpected) * 100, 1) : 0;

        // Count students in arrears from the same scoped query (before pagination).
        $studentsInArrears = (new static())->countArrears($request, $yearId, $classId, $termId, $minAmount, $search);

        $academicYears = AcademicYear::pluck('name', 'academic_year_id');
        $classes = SchoolClass::orderBy('name')->pluck('name', 'class_id');
        $terms = Term::when($yearId, fn($q) => $q->where('academic_year_id', $yearId))->orderBy('display_order')->get();

        return view('fee_management.arrears.index', compact(
            'arrears', 'totalExpected', 'totalCollected', 'totalOutstanding', 'collectionRate',
            'academicYears', 'classes', 'terms', 'yearId', 'classId', 'termId', 'minAmount', 'sort', 'search', 'studentsInArrears'
        ));
    }

    protected function countArrears(Request $request, $yearId, $classId, $termId, $minAmount, $search)
    {
        $q = Student::query()
            ->join('student_fee_assignments as sfa', 'sfa.student_id', '=', 'students.student_id')
            ->leftJoin(DB::raw('(SELECT fp.student_fee_assignment_id, COALESCE(SUM(fp.amount),0) AS paid_total FROM fee_payments fp GROUP BY fp.student_fee_assignment_id) AS paid_totals'), 'paid_totals.student_fee_assignment_id', '=', 'sfa.id')
            ->where('sfa.status', 'active')
            ->when($yearId, fn($qq) => $qq->where('sfa.academic_year_id', $yearId))
            ->when($termId, fn($qq) => $qq->where('sfa.term_id', $termId))
            ->when($classId, fn($qq) => $qq->whereHas('studentClassEnrollments.classSection', fn($cq) => $cq->where('class_id', $classId)))
            ->when($search, function ($qq) use ($search) {
                return $qq->where(function ($w) use ($search) {
                    $w->where('students.first_name', 'like', "%{$search}%")
                      ->orWhere('students.last_name', 'like', "%{$search}%")
                      ->orWhere('students.admission_no', 'like', "%{$search}%");
                });
            })
            ->selectRaw('students.student_id,
                SUM(sfa.final_amount) as expected_total,
                COALESCE(SUM(paid_totals.paid_total),0) as paid_total')
            ->groupBy('students.student_id')
            ->havingRaw('expected_total > paid_total');

        if ($request->filled('min_amount')) {
            $q->havingRaw('(expected_total - paid_total) >= ?', [(float) $minAmount]);
        }

        return $q->get()->count();
    }

    public function exportPdf(Request $request)
    {
        $data = $this->arrearsDataset($request);
        $pdf = Pdf::loadView('fee_management.arrears.exports.pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('arrears-report-' . date('Y-m-d') . '.pdf');
    }

    public function exportCsv(Request $request)
    {
        $data = $this->arrearsDataset($request);

        $filename = 'arrears-report-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $rows = $data['rows'];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Admission No', 'Student Name', 'Expected', 'Paid', 'Outstanding']);
            foreach ($rows as $r) {
                fputcsv($file, [
                    $r['admission_no'],
                    $r['name'],
                    number_format($r['expected'], 2),
                    number_format($r['paid'], 2),
                    number_format($r['outstanding'], 2),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Shared query used by the PDF/CSV exports (avoids pagination quirks).
     */
    protected function arrearsDataset(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $yearId = $request->get('academic_year_id', $currentYear ? $currentYear->academic_year_id : null);
        $classId = $request->get('class_id');
        $termId = $request->get('term_id');
        $minAmount = $request->get('min_amount');
        $search = $request->get('search');

        $inner = Student::query()
            ->join('student_fee_assignments as sfa', 'sfa.student_id', '=', 'students.student_id')
            ->leftJoin(DB::raw('(SELECT fp.student_fee_assignment_id, COALESCE(SUM(fp.amount),0) AS paid_total FROM fee_payments fp GROUP BY fp.student_fee_assignment_id) AS paid_totals'), 'paid_totals.student_fee_assignment_id', '=', 'sfa.id')
            ->where('sfa.status', 'active')
            ->when($yearId, fn($q) => $q->where('sfa.academic_year_id', $yearId))
            ->when($termId, fn($q) => $q->where('sfa.term_id', $termId))
            ->when($classId, function ($q) use ($classId) {
                return $q->whereHas('studentClassEnrollments.classSection', fn($cq) => $cq->where('class_id', $classId));
            })
            ->when($search, function ($q) use ($search) {
                return $q->where(function ($w) use ($search) {
                    $w->where('students.first_name', 'like', "%{$search}%")
                      ->orWhere('students.last_name', 'like', "%{$search}%")
                      ->orWhere('students.admission_no', 'like', "%{$search}%");
                });
            })
            ->selectRaw('
                students.student_id, students.first_name, students.middle_name, students.last_name, students.admission_no,
                SUM(sfa.final_amount) as expected_total,
                COALESCE(SUM(paid_totals.paid_total),0) as paid_total
            ')
            ->groupBy('students.student_id', 'students.first_name', 'students.middle_name', 'students.last_name', 'students.admission_no')
            ->havingRaw('expected_total > paid_total');

        if ($request->filled('min_amount')) {
            $inner->havingRaw('(expected_total - paid_total) >= ?', [(float) $minAmount]);
        }

        // Wrap in a derived table so ORDER BY on the computed columns survives
        // MySQL's sql_mode=only_full_group_by.
        $query = DB::table(DB::raw('(' . $inner->toSql() . ') as a'))
            ->mergeBindings($inner->getQuery())
            ->select(
                'a.student_id', 'a.first_name', 'a.middle_name', 'a.last_name', 'a.admission_no',
                'a.expected_total', 'a.paid_total'
            )
            ->orderByRaw('(a.expected_total - a.paid_total) desc');

        $rows = [];
        foreach ($query->get() as $s) {
            $rows[] = [
                'admission_no' => $s->admission_no,
                'name' => trim($s->first_name . ' ' . $s->middle_name . ' ' . $s->last_name),
                'expected' => (float) $s->expected_total,
                'paid' => (float) $s->paid_total,
                'outstanding' => (float) $s->expected_total - (float) $s->paid_total,
            ];
        }

        return [
            'rows' => $rows,
            'totalExpected' => array_sum(array_column($rows, 'expected')),
            'totalCollected' => array_sum(array_column($rows, 'paid')),
            'totalOutstanding' => array_sum(array_column($rows, 'outstanding')),
        ];
    }
}
