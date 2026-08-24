<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ClassSection;
use App\Models\AuditTrail;
use App\Services\TeacherScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Flash;
use Auth;

class MarksApprovalController extends Controller
{
    private TeacherScopeService $teacherScope;

    public function __construct(TeacherScopeService $teacherScope)
    {
        $this->teacherScope = $teacherScope;
        $this->middleware('can:exams.approve');
    }

    /**
     * Pending entries grouped per exam × class stream so approvers see a
     * batch-level overview (with learner demographics) instead of scrolling
     * through hundreds of individual student × subject rows.
     */
    public function index(Request $request)
    {
        [$viewAll, $scopeIds] = $this->accessContext();

        $exams = Exam::orderByDesc('exam_id')->pluck('name', 'exam_id');

        $classSectionQuery = ClassSection::with(['schoolClass', 'section']);
        if ($scopeIds !== null) {
            $classSectionQuery->whereIn('class_section_id', $scopeIds);
        }
        $classSections = $classSectionQuery->get()->mapWithKeys(function ($cs) {
            return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
        });

        // One grouped query per batch (exam × class section).
        $batchQuery = DB::table('exam_results')
            ->selectRaw('exam_id, class_section_id,
                COUNT(*) AS pending_count,
                SUM(marks_obtained IS NULL) AS incomplete_count,
                COUNT(DISTINCT student_id) AS learners_count,
                MIN(created_at) AS oldest_entry,
                MAX(updated_at) AS latest_entry')
            ->where('is_approved', false)
            ->groupBy('exam_id', 'class_section_id');

        // Demographics per batch: how many girls / boys are affected.
        $genderQuery = DB::table('exam_results')
            ->join('students', 'students.student_id', '=', 'exam_results.student_id')
            ->selectRaw('exam_results.exam_id, exam_results.class_section_id,
                students.gender,
                COUNT(DISTINCT exam_results.student_id) AS learners_count')
            ->where('exam_results.is_approved', false)
            ->groupBy('exam_results.exam_id', 'exam_results.class_section_id', 'students.gender');

        if ($scopeIds !== null) {
            $batchQuery->whereIn('class_section_id', $scopeIds);
            $genderQuery->whereIn('exam_results.class_section_id', $scopeIds);
        }

        if ($request->filled('exam_id')) {
            $batchQuery->where('exam_id', $request->exam_id);
            $genderQuery->where('exam_results.exam_id', $request->exam_id);
        }

        if ($request->filled('class_section_id')) {
            $batchQuery->where('class_section_id', $request->class_section_id);
            $genderQuery->where('exam_results.class_section_id', $request->class_section_id);
        }

        $batches = $batchQuery->orderBy('oldest_entry')->get();
        $genders = $genderQuery->get();

        $batches = $batches->map(function ($batch) use ($genders, $exams, $classSections) {
            $key = [$batch->exam_id, $batch->class_section_id];

            $batch->exam_name = $exams[$batch->exam_id] ?? 'Unknown Exam';
            $batch->class_name = $classSections[$batch->class_section_id] ?? 'Unknown Class';
            $batch->girls = (int) ($genders->where('exam_id', $batch->exam_id)->where('class_section_id', $batch->class_section_id)->firstWhere('gender', 'female')->learners_count ?? 0);
            $batch->boys = (int) ($genders->where('exam_id', $batch->exam_id)->where('class_section_id', $batch->class_section_id)->firstWhere('gender', 'male')->learners_count ?? 0);

            return $batch;
        });

        $totalPending = (int) $batches->sum('pending_count');
        $totalLearners = (int) $batches->sum('learners_count');

        return view('marks_approval.index', compact(
            'exams', 'classSections', 'batches', 'totalPending', 'totalLearners'
        ));
    }

    /**
     * Drill-down for a single batch: every pending learner with their
     * demographic details and the exact subjects awaiting approval.
     */
    public function show(Request $request, int $examId, int $classSectionId)
    {
        $exam = Exam::findOrFail($examId);
        $classSection = ClassSection::with(['schoolClass', 'section'])->findOrFail($classSectionId);

        $this->authorizeClassSection($classSectionId);

        $pending = ExamResult::with(['student', 'subject', 'createdBy'])
            ->where('exam_id', $examId)
            ->where('class_section_id', $classSectionId)
            ->where('is_approved', false)
            ->orderBy('student_id')
            ->get();

        // Group per learner: name + demographics on top, subjects underneath.
        $learners = $pending->groupBy('student_id')->map(function ($rows) {
            $student = $rows->first()->student;

            return (object) [
                'student'   => $student,
                'entries'   => $rows,
                'girls_boys' => strtolower((string) ($student->gender ?? '')) === 'female' ? 'F' : 'M',
            ];
        })->sortBy(fn ($l) => $l->student->last_name ?? $l->student->full_name)->values();

        return view('marks_approval.show', compact('exam', 'classSection', 'learners'));
    }

    /**
     * Approve selected results — accepts either explicit result_ids or a
     * whole batch (exam_id + class_section_id, optionally narrowed by
     * student_ids). Always re-checks the acting user's class scope.
     */
    public function approve(Request $request)
    {
        $request->validate([
            'result_ids'       => 'nullable|array',
            'result_ids.*'     => 'integer',
            'exam_id'          => 'nullable|integer',
            'class_section_id' => 'nullable|integer',
            'student_ids'      => 'nullable|array',
        ]);

        $hasBatch = $request->filled(['exam_id', 'class_section_id']);
        $hasIds = $request->filled('result_ids');

        if (!$hasBatch && !$hasIds) {
            Flash::error('No results selected for approval.');

            return redirect()->back();
        }

        [$viewAll, $scopeIds] = $this->accessContext();

        // Match EITHER the explicit ids OR the whole/narrowed batch —
        // wrapped in a single closure so class scoping still applies to both.
        $query = ExamResult::where(function ($q) use ($hasIds, $hasBatch, $request) {
            if ($hasIds) {
                $q->whereIn('result_id', $request->result_ids);
            }

            if ($hasBatch) {
                $batch = fn ($qq) => $qq->where('exam_id', $request->exam_id)
                    ->where('class_section_id', $request->class_section_id)
                    ->when($request->filled('student_ids'), fn ($qq) => $qq->whereIn('student_id', $request->student_ids));

                $hasIds ? $q->orWhere($batch) : $q->where($batch);
            }
        })
        ->where('is_approved', false);

        if ($scopeIds !== null) {
            $query->whereIn('class_section_id', $scopeIds);
        }

        $approvedCount = $query->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        AuditTrail::log(
            'Exam Result',
            'APPROVE',
            null,
            null,
            [
                'results_approved' => $approvedCount,
                'exam_id'          => $request->exam_id,
                'class_section_id' => $request->class_section_id,
            ]
        );

        Flash::success($approvedCount . ' mark entries approved successfully.');

        return redirect()->route('marks-approval.index');
    }

    /**
     * @return array{0: bool, 1: \Illuminate\Support\Collection|null}
     *         [canViewEverything, allowedClassSectionIdsOrNull]
     */
    private function accessContext(): array
    {
        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all')
            || $user->hasPermission('academics.settings.manage');

        return [$viewAll, $viewAll ? null : $this->teacherScope->getClassSectionIds($user)];
    }

    private function authorizeClassSection(int $classSectionId): void
    {
        [$viewAll, $scopeIds] = $this->accessContext();

        if (!$viewAll && !($scopeIds && $scopeIds->contains($classSectionId))) {
            abort(403, 'You are not authorized to approve marks for this class.');
        }
    }
}
