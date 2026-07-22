<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ExamResult;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalReportCardController extends Controller
{
    /**
     * List exams for the authenticated user's child(ren).
     * Shows exams that have results for the student.
     */
    public function index()
    {
        $user    = Auth::user();
        $student = $this->resolveStudent($user);

        if (!$student) {
            return view('portal.report-cards', ['exams' => collect(), 'message' => 'No student profile found.']);
        }

        // Get exams that have results for this student
        $examIds = ExamResult::where('student_id', $student->student_id)
            ->pluck('exam_id')
            ->unique();

        $exams = Exam::with(['examType', 'academicYear', 'termModel'])
            ->whereIn('exam_id', $examIds)
            ->latest('start_date')
            ->get();

        return view('portal.report-cards', compact('exams', 'student'));
    }

    /**
     * Show the report card for a specific exam.
     */
    public function show(Exam $exam)
    {
        $user    = Auth::user();
        $student = $this->resolveStudent($user);

        if (!$student) {
            abort(404, 'Student profile not found.');
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
     * Resolve the student record for the authenticated user.
     */
    protected function resolveStudent($user): ?Student
    {
        if ($user->user_type === 'student' && $user->student) {
            return $user->student;
        }

        if ($user->user_type === 'parent' && $user->parent) {
            return $user->parent->students->first();
        }

        return null;
    }
}
