<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Student;
use App\Models\ExamResult;
use App\Models\ClassSection;
use App\Models\School;
use App\Models\StudentAttendance;
use App\Services\TeacherScopeService;
use App\Services\CbeGradingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamReportController extends Controller
{
    private TeacherScopeService $teacherScope;

    public function __construct(TeacherScopeService $teacherScope)
    {
        $this->teacherScope = $teacherScope;
        $this->middleware('can:exams.results.view-own')->only(['generate', 'individual']);
        $this->middleware('can:exams.report-cards.export')->only(['bulk', 'bulkPdf']);
    }

    public function generate(Request $request)
    {
        [$exams, $classSections] = $this->filterOptions();

        return view('exam_reports.generate', compact('exams', 'classSections'));
    }

    /**
     * Class roster for an exam with REAL readiness status per learner
     * (how many papers have marks vs how many were scheduled).
     */
    public function bulk(Request $request)
    {
        $request->validate([
            'exam_id'          => 'required|integer',
            'class_section_id' => 'required|integer',
            'options'          => 'nullable|array',
        ]);

        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if (!$viewAll && !$hasSettings) {
            $allowedIds = $this->teacherScope->getClassSectionIds($user);
            if (!$allowedIds->contains((int) $request->class_section_id)) {
                abort(403, 'You are not authorized to generate reports for this class.');
            }
        }

        $exam = Exam::findOrFail($request->exam_id);
        $classSection = ClassSection::with(['schoolClass', 'section'])->findOrFail($request->class_section_id);
        $options = $request->input('options', []);

        $students = Student::whereHas('studentClassEnrollments', function ($q) use ($request) {
            $q->where('class_section_id', $request->class_section_id)
              ->where('status', 'active');
        })->orderBy('last_name')->get();

        // Scheduled papers define what a complete report looks like.
        $scheduledCount = \App\Models\ExamSchedule::where('exam_id', $exam->exam_id)
            ->where('class_id', $classSection->class_id)
            ->distinct('subject_id')
            ->count('subject_id');

        $recorded = ExamResult::where('exam_id', $exam->exam_id)
            ->where('class_section_id', $classSection->class_section_id)
            ->get()
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->count());

        $approved = ExamResult::where('exam_id', $exam->exam_id)
            ->where('class_section_id', $classSection->class_section_id)
            ->where('is_approved', true)
            ->get()
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->count());

        $expected = max(1, $scheduledCount);

        return view('exam_reports.bulk_list', compact(
            'exam', 'classSection', 'students', 'recorded', 'approved', 'expected', 'options'
        ));
    }

    /**
     * Single learner's progress report (screen / browser print).
     */
    public function individual(Request $request, $exam_id, $student_id)
    {
        $exam = Exam::findOrFail($exam_id);
        $student = Student::findOrFail($student_id);

        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if (!$viewAll && !$hasSettings) {
            $studentClassIds = $student->studentClassEnrollments()->pluck('class_section_id');
            $allowedIds = $this->teacherScope->getClassSectionIds($user);
            if ($studentClassIds->intersect($allowedIds)->isEmpty()) {
                abort(403, 'You are not authorized to view this student\'s report.');
            }
        }

        $classSectionId = $request->input('class_section_id')
            ?? $student->current_enrollment?->class_section_id;

        $data = $this->buildReportData($exam, $student, (int) $classSectionId, $request->input('options', []));

        return view('exam_reports.templates.page', ['data' => $data]);
    }

    /**
     * One PDF containing every learner's report card for the exam × class.
     */
    public function bulkPdf(Request $request)
    {
        $request->validate([
            'exam_id'          => 'required|integer',
            'class_section_id' => 'required|integer',
            'options'          => 'nullable|array',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $classSection = ClassSection::with(['schoolClass', 'section'])->findOrFail($request->class_section_id);

        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if (!$viewAll && !$hasSettings) {
            $allowedIds = $this->teacherScope->getClassSectionIds($user);
            if (!$allowedIds->contains((int) $classSection->class_section_id)) {
                abort(403, 'You are not authorized to generate reports for this class.');
            }
        }

        $students = Student::whereHas('studentClassEnrollments', function ($q) use ($classSection) {
            $q->where('class_section_id', $classSection->class_section_id)
              ->where('status', 'active');
        })->orderBy('last_name')->get();

        $reports = $students
            ->map(fn ($student) => $this->buildReportData(
                $exam, $student, $classSection->class_section_id, $request->input('options', [])
            ))
            ->filter(fn ($data) => count($data['rows']) > 0)
            ->values();

        if ($reports->isEmpty()) {
            return redirect()->back()->with('error', 'No recorded marks found for this class in the selected exam.');
        }

        $pdf = Pdf::loadView('exam_reports.pdf.class', [
            'reports' => $reports,
        ])->setPaper('a4');

        $fileName = str_replace(' ', '_', $classSection->schoolClass->name ?? 'Class')
            . '_Reports_' . str_replace(' ', '_', $exam->name) . '.pdf';

        return $pdf->stream($fileName);
    }

    /**
     * Assemble everything one progress report needs.
     */
    private function buildReportData(Exam $exam, Student $student, int $classSectionId, array $options): array
    {
        $classSection = ClassSection::with(['schoolClass', 'section', 'classTeacher'])->find($classSectionId);

        $results = ExamResult::with(['subject', 'grade'])
            ->where('exam_id', $exam->exam_id)
            ->where('student_id', $student->student_id)
            ->when($classSectionId, fn ($q) => $q->where('class_section_id', $classSectionId))
            ->get();

        $cbe = app(CbeGradingService::class);
        $isCbeLearner = strtoupper((string) $student->education_system) === 'CBC';

        $rows = $results->map(function ($res) use ($cbe, $isCbeLearner) {
            $pct = $res->percentage;
            $level = $cbe->describe($pct);

            return [
                'subject'   => $res->subject?->name ?? '—',
                'marks'     => (float) $res->marks_obtained,
                'max'       => $res->max_marks,
                'percent'   => $pct,
                'level'     => $level,
                'grade'     => $res->grade?->name,
                'remarks'   => $res->remarks,
                'approved'  => (bool) $res->is_approved,
                'is_cbe'    => $isCbeLearner,
            ];
        })->sortBy('subject')->values();

        $total = (float) $results->sum('marks_obtained');
        $outOf = (float) $results->sum(fn ($r) => $r->max_marks);
        $meanPct = $outOf > 0 ? round(($total / $outOf) * 100, 1) : 0;

        [$position, $classSize] = $this->classPosition($exam, $classSectionId, $student->student_id);

        $bestRow = $rows->sortByDesc('percent')->first();
        $worstRow = $rows->sortBy('percent')->first();
        $overall = $cbe->overallBand($meanPct);

        $attendance = null;
        if (in_array('attendance', $options)) {
            $query = StudentAttendance::where('student_id', $student->student_id)
                ->whereBetween('date', [$exam->start_date, $exam->end_date ?? now()]);

            $present = (clone $query)->where('status', 'present')->count();
            $open = (clone $query)->whereIn('status', ['present', 'absent', 'late'])->count();

            if ($open > 0) {
                $attendance = ['present' => $present, 'open' => $open];
            }
        }

        $fee = null;
        if (in_array('fee', $options)) {
            $summary = $student->fee_summary;
            $fee = ['balance' => (float) $summary['balance']];
        }

        return [
            'exam'           => $exam,
            'student'        => $student,
            'classSection'   => $classSection,
            'school'         => School::first(),
            'school_meta'    => $this->schoolMeta(),
            'rows'           => $rows,
            'total'          => $total,
            'out_of'         => $outOf,
            'mean_pct'       => $meanPct,
            'overall'        => $overall,
            'position'       => $position,
            'class_size'     => $classSize,
            'is_cbe'         => $isCbeLearner,
            'attendance'     => $attendance,
            'fee'            => $fee,
            'teacher_remark' => $cbe->teacherRemark(
                $meanPct,
                $bestRow['subject'] ?? null,
                $worstRow['subject'] ?? null
            ),
            'principal_remark' => $cbe->principalRemark($meanPct),
            'generated_at'   => now(),
        ];
    }

    /**
     * Optional school letterhead details from the settings table.
     */
    private function schoolMeta(): array
    {
        $meta = [
            'po_box'  => '____',
            'motto'   => null,
            'phone'   => null,
            'email'   => null,
        ];

        try {
            $settings = DB::table('settings')
                ->whereIn('setting_key', ['school_po_box', 'school_motto', 'school_phone', 'school_email'])
                ->pluck('setting_value', 'setting_key');

            foreach (array_keys($meta) as $key) {
                $dbKey = 'school_' . $key;
                if ($settings->has($dbKey) && filled($settings[$dbKey])) {
                    $meta[$key] = $settings[$dbKey];
                }
            }
        } catch (\Throwable) {
            // Settings table not migrated yet — fall back to placeholders.
        }

        return $meta;
    }

    /**
     * Position of a learner within their class stream for an exam,
     * ranked by total marks (ties share a position).
     *
     * @return array{0: int|null, 1: int}
     */
    private function classPosition(Exam $exam, int $classSectionId, int $studentId): array
    {
        $totals = ExamResult::where('exam_id', $exam->exam_id)
            ->where('class_section_id', $classSectionId)
            ->groupBy('student_id')
            ->selectRaw('student_id, SUM(marks_obtained) AS total_marks')
            ->orderByDesc('total_marks')
            ->get();

        $position = null;
        $lastTotal = null;
        $lastRank = 0;

        foreach ($totals as $index => $row) {
            $rank = $row->total_marks === $lastTotal ? $lastRank : $index + 1;
            $lastTotal = $row->total_marks;
            $lastRank = $rank;

            if ((int) $row->student_id === $studentId) {
                $position = $rank;
                break;
            }
        }

        return [$position, $totals->count()];
    }

    private function filterOptions(): array
    {
        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all')
            || $user->hasPermission('academics.settings.manage');

        $exams = Exam::orderByDesc('exam_id')->pluck('name', 'exam_id');

        $classSectionQuery = ClassSection::with(['schoolClass', 'section']);
        if (!$viewAll) {
            $classSectionQuery->whereIn('class_section_id', $this->teacherScope->getClassSectionIds($user));
        }

        $classSections = $classSectionQuery->get()->mapWithKeys(function ($cs) {
            return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
        });

        return [$exams, $classSections];
    }
}
