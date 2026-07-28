<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Student;
use App\Models\ExamResult;
use App\Models\ClassSection;
use App\Services\TeacherScopeService;
use Illuminate\Http\Request;
use DB;

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
        return view('exam_analysis.performance', compact('exams'));
    }

    public function subject(Request $request)
    {
        $exams = Exam::pluck('name', 'exam_id');
        return view('exam_analysis.subject', compact('exams'));
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

            $rankings = ExamResult::select(
                'student_id',
                DB::raw('SUM(marks_obtained) as total_marks'),
                DB::raw('AVG(marks_obtained) as mean_score'),
                DB::raw('COUNT(subject_id) as subjects_count')
            )
            ->where('exam_id', $request->exam_id)
            ->where('class_section_id', $request->class_section_id)
            ->groupBy('student_id')
            ->orderBy('total_marks', 'desc')
            ->with('student')
            ->get();
        }

        return view('exam_analysis.rankings', compact('exams', 'classSections', 'rankings'));
    }
}
