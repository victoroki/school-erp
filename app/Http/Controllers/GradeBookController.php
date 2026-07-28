<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Student;
use App\Models\Subject;
use App\Models\ExamResult;
use App\Models\ClassSection;
use App\Services\TeacherScopeService;
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
            $exams = Exam::pluck('name', 'exam_id');
            $classSections = ClassSection::with(['schoolClass', 'section'])->get()->mapWithKeys(function ($cs) {
                return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
            });
        } else {
            $classSectionIds = $this->teacherScope->getClassSectionIds($user);
            $subjectIds = $this->teacherScope->getSubjectIds($user);

            $exams = Exam::whereHas('examSchedules', function ($q) use ($classSectionIds) {
                $q->whereIn('class_section_id', $classSectionIds);
            })->orWhereDoesntHave('examSchedules')->pluck('name', 'exam_id');

            $classSections = ClassSection::with(['schoolClass', 'section'])
                ->whereIn('class_section_id', $classSectionIds)
                ->get()
                ->mapWithKeys(function ($cs) {
                    return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
                });
        }

        $students = [];
        $subjects = [];
        $results = [];

        if ($request->filled(['exam_id', 'class_section_id'])) {
            if (!$viewAll && !$hasSettings) {
                $allowedIds = $this->teacherScope->getClassSectionIds($user);
                if (!$allowedIds->contains((int) $request->class_section_id)) {
                    abort(403, 'You are not authorized to view grade book for this class.');
                }
            }

            $students = Student::whereHas('studentClassEnrollments', function ($q) use ($request) {
                $q->where('class_section_id', $request->class_section_id)
                  ->where('status', 'active');
            })->orderBy('last_name')->get();

            $subjectIdsWithResults = ExamResult::where('exam_id', $request->exam_id)
                ->where('class_section_id', $request->class_section_id)
                ->distinct()
                ->pluck('subject_id');
            
            $subjects = Subject::whereIn('subject_id', $subjectIdsWithResults)->get();

            $examResults = ExamResult::with('grade')
                ->where('exam_id', $request->exam_id)
                ->where('class_section_id', $request->class_section_id)
                ->get();

            foreach ($examResults as $res) {
                $results[$res->student_id][$res->subject_id] = [
                    'marks' => $res->marks_obtained,
                    'grade' => $res->grade->name ?? '-'
                ];
            }
        }

        return view('grade_book.index', compact('exams', 'classSections', 'students', 'subjects', 'results'));
    }
}
