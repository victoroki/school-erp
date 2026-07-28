<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Subject;
use App\Models\ExamResult;
use App\Models\ClassSection;
use App\Services\TeacherScopeService;
use Illuminate\Http\Request;

class MarkSheetController extends Controller
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
            $subjects = Subject::pluck('name', 'subject_id');
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

            $subjects = Subject::whereIn('subject_id', $subjectIds)->pluck('name', 'subject_id');
        }

        $results = [];

        if ($request->filled(['exam_id', 'class_section_id', 'subject_id'])) {
            if (!$viewAll && !$hasSettings) {
                $allowedIds = $this->teacherScope->getClassSectionIds($user);
                $allowedSubjectIds = $this->teacherScope->getSubjectIds($user);
                if (!$allowedIds->contains((int) $request->class_section_id) || !$allowedSubjectIds->contains((int) $request->subject_id)) {
                    abort(403, 'You are not authorized to view marksheet for this class or subject.');
                }
            }

            $results = ExamResult::with(['student', 'grade'])
                ->where('exam_id', $request->exam_id)
                ->where('class_section_id', $request->class_section_id)
                ->where('subject_id', $request->subject_id)
                ->get();
        }

        return view('mark_sheets.index', compact('exams', 'classSections', 'subjects', 'results'));
    }
}
