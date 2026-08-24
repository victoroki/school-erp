<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Student;
use App\Models\Subject;
use App\Models\ExamResult;
use App\Models\ClassSection;
use App\Models\ExamSchedule;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\TeacherScopeService;
use App\Services\CbeGradingService;
use Illuminate\Http\Request;

class GradeBookController extends Controller
{
    private TeacherScopeService $teacherScope;

    public function __construct(TeacherScopeService $teacherScope)
    {
        $this->teacherScope = $teacherScope;
        $this->middleware('can:exams.results.view-own');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if ($viewAll || $hasSettings) {
            $exams = Exam::orderByDesc('exam_id')->pluck('name', 'exam_id');
            $classSections = ClassSection::with(['schoolClass', 'section'])->get()->mapWithKeys(function ($cs) {
                return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
            });
        } else {
            $classSectionIds = $this->teacherScope->getClassSectionIds($user);

            $exams = $this->teacherScope->scopeExams(Exam::query(), $user)->pluck('name', 'exam_id');

            $classSections = ClassSection::with(['schoolClass', 'section'])
                ->whereIn('class_section_id', $classSectionIds)
                ->get()
                ->mapWithKeys(function ($cs) {
                    return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
                });
        }

        $students = collect();
        $subjects = collect();
        $results = [];
        $ranks = [];
        $subjectAverages = [];
        $subjectMax = [];
        $studentStats = [];
        $stats = null;

        if ($request->filled(['exam_id', 'class_section_id'])) {
            if (!$viewAll && !$hasSettings) {
                $allowedIds = $this->teacherScope->getClassSectionIds($user);
                if (!$allowedIds->contains((int) $request->class_section_id)) {
                    abort(403, 'You are not authorized to view grade book for this class.');
                }
            }

            $classSection = ClassSection::with('schoolClass')->findOrFail($request->class_section_id);

            // Every paper SCHEDULED for this class in this exam is a column,
            // even when no marks were recorded yet — plus anything recorded.
            $scheduledIds = ExamSchedule::where('exam_id', $request->exam_id)
                ->where('class_id', $classSection->class_id)
                ->distinct()
                ->pluck('subject_id');

            $recordedIds = ExamResult::where('exam_id', $request->exam_id)
                ->where('class_section_id', $request->class_section_id)
                ->distinct()
                ->pluck('subject_id');

            $subjectIds = $scheduledIds->merge($recordedIds)->unique()->values();

            $subjects = Subject::whereIn('subject_id', $subjectIds)->orderBy('name')->get();

            $subjectMax = ExamSchedule::where('exam_id', $request->exam_id)
                ->where('class_id', $classSection->class_id)
                ->whereIn('subject_id', $subjectIds)
                ->groupBy('subject_id')
                ->selectRaw('subject_id, MIN(max_marks) as max_marks')
                ->pluck('max_marks', 'subject_id');

            $enrollmentQuery = fn ($q) => $q
                ->where('class_section_id', $request->class_section_id)
                ->where('status', 'active');

            $allStudents = Student::whereHas('studentClassEnrollments', $enrollmentQuery)
                ->with(['studentClassEnrollments' => $enrollmentQuery])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            $examResults = ExamResult::with('grade')
                ->where('exam_id', $request->exam_id)
                ->where('class_section_id', $request->class_section_id)
                ->get();

            $cbe = app(CbeGradingService::class);

            $totals = [];
            $studentStats = [];

            foreach ($examResults as $res) {
                $max = (float) ($subjectMax[$res->subject_id] ?? $res->getMaxMarksAttribute());
                $pct = $max > 0 ? ((float) $res->marks_obtained / $max) * 100 : 0;

                $results[$res->student_id][$res->subject_id] = [
                    'marks'   => (float) $res->marks_obtained,
                    'max'     => $max,
                    'percent' => round($pct, 1),
                    'color'   => $this->bandColor($pct),
                    'grade'   => $res->grade->name ?? '-',
                    'level'   => $cbe->describe($pct)['code'],
                ];

                $totals[$res->student_id] = ($totals[$res->student_id] ?? 0) + (float) $res->marks_obtained;
                $studentStats[$res->student_id]['total'] = ($studentStats[$res->student_id]['total'] ?? 0) + (float) $res->marks_obtained;
                $studentStats[$res->student_id]['out_of'] = ($studentStats[$res->student_id]['out_of'] ?? 0) + $max;
            }

            foreach ($studentStats as $studentId => &$stat) {
                $stat['mean'] = $stat['out_of'] > 0
                    ? round(($stat['total'] / $stat['out_of']) * 100, 1)
                    : 0;
            }
            unset($stat);

            foreach ($subjects as $subject) {
                $paperResults = $examResults->where('subject_id', $subject->subject_id);
                $sum = $paperResults->sum('marks_obtained');
                $count = $paperResults->count();
                $maxForSubject = (float) ($subjectMax[$subject->subject_id] ?? 100);

                $subjectAverages[$subject->subject_id] = $count > 0 && $maxForSubject > 0
                    ? round((($sum / $count) / $maxForSubject) * 100, 1)
                    : null;
            }

            // Ranks by mean percentage across the WHOLE class, not just the page.
            $sorted = collect($studentStats)->sortByDesc('mean');
            $position = 0;
            $lastMean = null;
            $lastRank = 0;
            foreach ($sorted as $studentId => $stat) {
                $position++;
                $ranks[$studentId] = $stat['mean'] === $lastMean ? $lastRank : $position;
                $lastMean = $stat['mean'];
                $lastRank = $ranks[$studentId];
            }

            $learnerCount = max(1, count($studentStats));
            $classMean = $learnerCount > 0
                ? round(collect($studentStats)->avg('mean'), 1)
                : 0;

            $stats = [
                'learners_with_results' => count($studentStats),
                'class_mean_percent'    => $classMean,
                'top_learner'           => $sorted->keys()->isNotEmpty()
                    ? optional(Student::find($sorted->keys()->first()))->full_name
                    : null,
            ];

            // Paginate the matrix so very large classes stay readable.
            $perPage = 30;
            $page = LengthAwarePaginator::resolveCurrentPage();
            $students = new LengthAwarePaginator(
                $allStudents->slice(($page - 1) * $perPage, $perPage)->values(),
                $allStudents->count(),
                $perPage,
                $page,
                ['path' => url('grade-book'), 'query' => $request->query()]
            );
        }

        return view('grade_book.index', compact(
            'exams', 'classSections', 'students', 'subjects', 'results',
            'ranks', 'subjectAverages', 'subjectMax', 'studentStats', 'stats'
        ));
    }

    private function bandColor(float $percentage): string
    {
        return match (true) {
            $percentage >= 75 => '#047857',
            $percentage >= 58 => '#2563eb',
            $percentage >= 41 => '#7c3aed',
            $percentage >= 21 => '#d97706',
            default           => '#dc2626',
        };
    }
}
