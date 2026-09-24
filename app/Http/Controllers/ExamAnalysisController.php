<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Student;
use App\Models\ExamResult;
use App\Models\ClassSection;
use App\Services\CurriculumService;
use App\Services\TeacherScopeService;
use Illuminate\Http\Request;

class ExamAnalysisController extends Controller
{
    private TeacherScopeService $teacherScope;

    public function __construct(TeacherScopeService $teacherScope)
    {
        $this->teacherScope = $teacherScope;
        $this->middleware('can:exams.analysis.view');
    }

    public function performance(Request $request)
    {
        $exams = Exam::pluck('name', 'exam_id');
        $analysis = $request->filled('exam_id')
            ? $this->buildAnalysis((int) $request->exam_id)
            : null;

        return view('exam_analysis.performance', compact('exams', 'analysis'));
    }

    public function subject(Request $request)
    {
        $exams = Exam::pluck('name', 'exam_id');
        $analysis = $request->filled('exam_id')
            ? $this->buildAnalysis((int) $request->exam_id)
            : null;

        return view('exam_analysis.subject', compact('exams', 'analysis'));
    }

    /**
     * Real aggregates for the performance and subject analysis screens.
     *
     * Both views previously rendered entirely fabricated figures — 450 students,
     * a 78.5% pass rate, a 65.2 average, 12 subjects tested, fixed subject lists
     * and hardcoded chart arrays — while the controller passed only $exams. None
     * of it came from the database.
     *
     * Percentages are computed per paper against the max_marks on exam_schedules,
     * because raw marks are out of whatever maximum the paper defines. Pass
     * thresholds come from each paper's own passing_marks.
     *
     * @return array{subjects: \Illuminate\Support\Collection, overall: array, grades: array, trend: array}
     */
    private function buildAnalysis(int $examId): array
    {
        $exam = Exam::with('examSchedules')->findOrFail($examId);

        $scheduleBySubject = $exam->examSchedules->groupBy('subject_id');

        $maxBySubject = $scheduleBySubject->map(fn ($rows) => (float) $rows->min('max_marks'));

        $passBySubject = $scheduleBySubject->map(function ($rows) {
            $max = (float) $rows->min('max_marks');
            $pass = (float) $rows->min('passing_marks');

            if ($max <= 0) {
                return 40.0;
            }

            return $pass > 0 ? round(($pass / $max) * 100, 2) : 40.0;
        });

        // Lookup maps rather than relations, so this does not depend on relation
        // names and costs two queries instead of one per row.
        $subjectNames = \App\Models\Subject::pluck('name', 'subject_id');
        $gradeNames = \App\Models\GradingScale::pluck('name', 'grade_id');

        $results = ExamResult::where('exam_id', $examId)
            ->get(['result_id', 'student_id', 'subject_id', 'marks_obtained', 'grade_id']);

        $percentageOf = function ($result) use ($maxBySubject) {
            $max = (float) ($maxBySubject[$result->subject_id] ?? 100);

            return $max > 0 ? round(((float) $result->marks_obtained / $max) * 100, 2) : 0.0;
        };

        $subjects = $results->groupBy('subject_id')
            ->map(function ($rows) use ($percentageOf, $passBySubject, $subjectNames) {
                $percentages = $rows->map($percentageOf);
                $threshold = (float) ($passBySubject[$rows->first()->subject_id] ?? 40.0);

                $mean = $percentages->avg();
                $variance = $percentages->map(fn ($p) => ($p - $mean) ** 2)->avg();
                $mode = $percentages->countBy()->sortDesc()->keys()->first();

                return [
                    'name' => $subjectNames[$rows->first()->subject_id] ?? 'Unknown subject',
                    'students' => $rows->pluck('student_id')->unique()->count(),
                    'average' => round($mean, 1),
                    'highest' => round($percentages->max(), 1),
                    'lowest' => round($percentages->min(), 1),
                    'median' => round($percentages->median(), 1),
                    'mode' => $mode === null ? 0.0 : round((float) $mode, 1),
                    'std_dev' => round(sqrt((float) $variance), 1),
                    // Resolved through the same grading engine the results use, so
                    // this cannot disagree with the grades actually stored.
                    'grade' => \App\Models\GradingScale::resolveForPercentage(
                        round($mean, 2),
                        app(CurriculumService::class)->current()
                    )?->name,
                    'pass_rate' => $percentages->isNotEmpty()
                        ? round($percentages->filter(fn ($p) => $p >= $threshold)->count() / $percentages->count() * 100, 1)
                        : 0.0,
                ];
            })
            ->sortByDesc('average')
            ->values();

        $allPercentages = $results->map($percentageOf);

        $gradeCounts = $results
            ->groupBy(fn ($result) => $gradeNames[$result->grade_id] ?? 'Ungraded')
            ->map->count()
            ->sortDesc();

        // Built here rather than in the view: an inline array literal inside a
        // Blade @json() directive does not compile.
        $palette = [
            'rgb(40, 167, 69)', 'rgb(23, 162, 184)', 'rgb(255, 193, 7)',
            'rgb(253, 126, 20)', 'rgb(220, 53, 69)', 'rgb(108, 117, 125)',
        ];

        $grades = [
            'labels' => $gradeCounts->keys()->values(),
            'counts' => $gradeCounts->values(),
            'colours' => $gradeCounts->keys()->values()
                ->map(fn ($label, $index) => $palette[$index % count($palette)]),
        ];

        // Average score per exam, oldest first. Real data — the previous series
        // was a hardcoded [62, 65, 68, 65.2] labelled "Term 1..Current", and
        // exam results carry no term of their own.
        $recentExamIds = Exam::orderByDesc('start_date')->limit(6)->pluck('exam_id');

        $schedulesPerExam = \App\Models\ExamSchedule::whereIn('exam_id', $recentExamIds)
            ->get(['exam_id', 'subject_id', 'max_marks'])
            ->groupBy('exam_id')
            ->map(fn ($rows) => $rows->groupBy('subject_id')->map(fn ($r) => (float) $r->min('max_marks')));

        $trend = ExamResult::whereIn('exam_id', $recentExamIds)
            ->get(['exam_id', 'subject_id', 'marks_obtained'])
            ->groupBy('exam_id')
            ->map(function ($rows, $examKey) use ($schedulesPerExam) {
                $percentages = $rows->map(function ($row) use ($schedulesPerExam, $examKey) {
                    $max = (float) ($schedulesPerExam[$examKey][$row->subject_id] ?? 100);

                    return $max > 0 ? ((float) $row->marks_obtained / $max) * 100 : 0.0;
                });

                return round($percentages->avg(), 1);
            });

        $trendLabels = Exam::whereIn('exam_id', $trend->keys())
            ->orderBy('start_date')
            ->pluck('name', 'exam_id');

        return [
            'subjects' => $subjects,
            'overall' => [
                'students' => $results->pluck('student_id')->unique()->count(),
                'subjects_tested' => $subjects->count(),
                'average' => $allPercentages->isNotEmpty() ? round($allPercentages->avg(), 1) : 0.0,
                'highest' => $allPercentages->isNotEmpty() ? round($allPercentages->max(), 1) : null,
                'lowest' => $allPercentages->isNotEmpty() ? round($allPercentages->min(), 1) : null,
                'pass_rate' => $allPercentages->isNotEmpty()
                    ? round(
                        $results->filter(function ($result, $index) use ($allPercentages, $passBySubject) {
                            $threshold = (float) ($passBySubject[$result->subject_id] ?? 40.0);

                            return $allPercentages[$index] >= $threshold;
                        })->count() / $allPercentages->count() * 100,
                        1
                    )
                    : 0.0,
            ],
            'grades' => $grades,
            'trend' => [
                'labels' => $trendLabels->values(),
                'data' => $trend->sortBy(fn ($value, $examKey) => $trendLabels->keys()->search($examKey))->values(),
            ],
        ];
    }

    public function rankings(Request $request)
    {
        $exams = Exam::pluck('name', 'exam_id');

        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if ($viewAll || $hasSettings) {
            $classSections = ClassSection::with(['schoolClass', 'section'])->get()->mapWithKeys(function ($cs) {
                return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
            });
        } else {
            $classSectionIds = $this->teacherScope->getClassSectionIds($user);
            $classSections = ClassSection::with(['schoolClass', 'section'])
                ->whereIn('class_section_id', $classSectionIds)
                ->get()
                ->mapWithKeys(function ($cs) {
                    return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
                });
        }

        $rankings = [];

        if ($request->filled(['exam_id', 'class_section_id'])) {
            if (!$viewAll && !$hasSettings) {
                $allowedIds = $this->teacherScope->getClassSectionIds($user);
                if (!$allowedIds->contains((int) $request->class_section_id)) {
                    abort(403, 'You are not authorized to view rankings for this class.');
                }
            }

            // Percentages per paper, not raw marks.
            //
            // The previous version summed marks_obtained across papers and ordered
            // by that total, which ranks a paper marked out of 100 above one marked
            // out of 20 for the same proportion of correct answers. It also printed
            // that raw mean with a "%" sign, and the view hardcoded a green
            // "Passed" badge on every row.
            $exam = Exam::with('examSchedules')->findOrFail($request->exam_id);

            $maxBySubject = $exam->examSchedules
                ->groupBy('subject_id')
                ->map(fn ($rows) => (float) $rows->min('max_marks'));

            // Each paper's own pass mark, converted to a percentage of its maximum
            // — the same definition buildAnalysis() uses.
            $passBySubject = $exam->examSchedules
                ->groupBy('subject_id')
                ->map(function ($rows) {
                    $max = (float) $rows->min('max_marks');
                    $pass = (float) $rows->min('passing_marks');

                    if ($max <= 0) {
                        return 40.0;
                    }

                    return $pass > 0 ? round(($pass / $max) * 100, 2) : 40.0;
                });

            $results = ExamResult::where('exam_id', $request->exam_id)
                ->where('class_section_id', $request->class_section_id)
                ->get(['result_id', 'student_id', 'subject_id', 'marks_obtained']);

            $students = Student::whereIn('student_id', $results->pluck('student_id')->unique()->all())
                ->get()
                ->keyBy('student_id');

            $curriculum = app(CurriculumService::class)->current();

            $rankings = $results->groupBy('student_id')
                ->map(function ($rows, $studentId) use ($maxBySubject, $passBySubject, $students, $curriculum) {
                    $percentages = $rows->map(function ($row) use ($maxBySubject) {
                        $max = (float) ($maxBySubject[$row->subject_id] ?? 100);

                        return $max > 0 ? round(((float) $row->marks_obtained / $max) * 100, 2) : 0.0;
                    });

                    // A learner sits papers with different maxima and different
                    // pass marks, so the standard to hold them to is the average of
                    // the papers they actually sat.
                    $thresholds = $rows->map(fn ($row) => (float) ($passBySubject[$row->subject_id] ?? 40.0));

                    $mean = round($percentages->avg(), 2);
                    $threshold = round($thresholds->avg(), 2);

                    return (object) [
                        'student' => $students[$studentId] ?? null,
                        'subjects_count' => $rows->pluck('subject_id')->unique()->count(),
                        'total_percentage' => round($percentages->sum(), 1),
                        'mean_percentage' => $mean,
                        'threshold' => $threshold,
                        'passed' => $mean >= $threshold,
                        // Resolved through the grading engine the results use, so a
                        // learner's grade here cannot disagree with their report card.
                        'grade' => \App\Models\GradingScale::resolveForPercentage($mean, $curriculum)?->name,
                    ];
                })
                ->filter(fn ($row) => $row->student !== null)
                // Ranked on the mean, so a learner sitting more papers is not
                // advantaged by the number of papers alone.
                ->sortByDesc('mean_percentage')
                ->values();
        }

        return view('exam_analysis.rankings', compact('exams', 'classSections', 'rankings'));
    }
}
