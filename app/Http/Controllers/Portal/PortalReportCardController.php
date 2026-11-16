<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;
use App\Models\Exam;
use App\Models\Student;
use App\Services\PortalScopeService;
use Illuminate\Support\Facades\Auth;

class PortalReportCardController extends Controller
{
    /**
     * List exams for the authenticated user's child(ren).
     * Shows exams that have results for the student.
     */
    public function index()
    {
        $student = $this->resolveStudent(Auth::user());

        if (! $student) {
            // This branch used to render the view with only `exams` and
            // `message`, but the view reads `$student->full_name` in its own
            // header, so every account with no linked learner got a
            // "Undefined variable $student" 500 instead of an answer. An account
            // with no learner attached to it has nothing to read here.
            abort(403, 'No student is linked to this account.');
        }

        // Get exams that have results for this student
        $examIds = ExamResult::where('student_id', $student->student_id)
            ->pluck('exam_id')
            ->unique();

        $exams = Exam::with(['examType', 'academicYear'])
            ->whereIn('exam_id', $examIds)
            ->latest('start_date')
            ->get();

        return view('portal.report-cards', compact('exams', 'student'));
    }

    /**
     * Show the report card for a specific exam.
     *
     * The exam comes from the URL, but the learner never does: it is resolved
     * from the authenticated user, and the results query is filtered by that
     * learner. Swapping the exam id can therefore only ever 404 — it cannot make
     * one learner's results appear under another's session.
     */
    public function show(Exam $exam)
    {
        $student = $this->resolveStudent(Auth::user());

        if (! $student) {
            abort(403, 'No student is linked to this account.');
        }

        $results = ExamResult::with(['subject', 'grade', 'classSection', 'exam'])
            ->where('student_id', $student->student_id)
            ->where('exam_id', $exam->exam_id)
            ->get();

        if ($results->isEmpty()) {
            abort(404, 'No results found for this exam.');
        }

        $totalMarks   = $results->sum('marks_obtained');
        $count        = $results->count();
        $average      = $count > 0 ? round($totalMarks / $count, 2) : 0;

        $highestTotal = ExamResult::where('exam_id', $exam->exam_id)
            ->selectRaw('student_id, SUM(marks_obtained) as total')
            ->groupBy('student_id')
            ->orderByDesc('total')
            ->pluck('total')
            ->first() ?? 0;

        $classPosition = ExamResult::where('exam_id', $exam->exam_id)
            ->selectRaw('student_id, SUM(marks_obtained) as total')
            ->groupBy('student_id')
            ->havingRaw('SUM(marks_obtained) > ?', [$totalMarks])
            ->count() + 1;

        $totalStudents = ExamResult::where('exam_id', $exam->exam_id)
            ->distinct('student_id')
            ->count('student_id');

        $overview = [
            'total_marks'     => $totalMarks,
            'average'         => $average,
            'highest_total'   => $highestTotal,
            'class_position'  => $classPosition,
            'total_students'  => $totalStudents,
        ];

        return view('portal.report-card-detail', compact('exam', 'student', 'results', 'overview'));
    }

    /**
     * Resolve the learner this portal user is allowed to see.
     *
     * Ownership comes from PortalScopeService — the single place the
     * parent/student rules live — rather than from a second implementation here.
     *
     * A student account resolves to itself only. A parent account resolves to one
     * of its own linked children, and to nothing when it has none. Deliberately
     * NOT the first student in the school: `visibleStudentIds()` returns every
     * active learner for Owner/Super Admin/Admin/Accountant, so using it here
     * would hand a staff account a pupil's report card through the parent portal.
     */
    protected function resolveStudent($user): ?Student
    {
        if ($student = PortalScopeService::selfStudent($user)) {
            return $student;
        }

        $ownChildren = app(PortalScopeService::class)->childrenFor($user);

        if ($ownChildren->isEmpty()) {
            return null;
        }

        return Student::find($ownChildren->first()['student_id']);
    }
}
