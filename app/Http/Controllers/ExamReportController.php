<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Student;
use App\Models\ExamResult;
use App\Models\ClassSection;
use App\Models\ReportCardTemplate;
use App\Services\TeacherScopeService;
use Illuminate\Http\Request;

class ExamReportController extends Controller
{
    private TeacherScopeService $teacherScope;

    public function __construct(TeacherScopeService $teacherScope)
    {
        $this->teacherScope = $teacherScope;
        $this->middleware('can:exams.results.view-own')->only(['generate', 'individual']);
        $this->middleware('can:exams.report-cards.export')->only(['bulk']);
    }

    public function generate(Request $request)
    {
        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        $exams = Exam::pluck('name', 'exam_id');

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

        $templates = ReportCardTemplate::where('status', true)->pluck('name', 'id');

        return view('exam_reports.generate', compact('exams', 'classSections', 'templates'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'exam_id' => 'required',
            'class_section_id' => 'required',
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
        
        $students = Student::whereHas('studentClassEnrollments', function ($q) use ($request) {
            $q->where('class_section_id', $request->class_section_id)
              ->where('status', 'active');
        })->get();

        return view('exam_reports.bulk_list', compact('exam', 'classSection', 'students'));
    }

    public function individual($exam_id, $student_id)
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
        
        $results = ExamResult::with(['subject', 'grade', 'classSection'])
            ->where('exam_id', $exam_id)
            ->where('student_id', $student_id)
            ->get();

        return view('exam_reports.templates.standard', compact('exam', 'student', 'results'));
    }
}
