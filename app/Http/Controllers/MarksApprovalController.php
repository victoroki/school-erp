<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ClassSection;
use App\Services\TeacherScopeService;
use Illuminate\Http\Request;
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

    public function index(Request $request)
    {
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

        $exams = Exam::pluck('name', 'exam_id');

        $query = ExamResult::with(['student', 'subject', 'exam', 'classSection'])
            ->where('is_approved', false);

        if (!$viewAll && !$hasSettings) {
            $classSectionIds = $this->teacherScope->getClassSectionIds($user);
            $query->whereIn('class_section_id', $classSectionIds);
        }

        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->filled('class_section_id')) {
            $query->where('class_section_id', $request->class_section_id);
        }

        $pendingResults = $query->paginate(20);

        return view('marks_approval.index', compact('exams', 'classSections', 'pendingResults'));
    }

    public function approve(Request $request)
    {
        $resultIds = $request->input('result_ids', []);
        
        if (empty($resultIds)) {
            Flash::error('No results selected for approval.');
            return redirect()->back();
        }

        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        $query = ExamResult::whereIn('result_id', $resultIds);

        if (!$viewAll && !$hasSettings) {
            $classSectionIds = $this->teacherScope->getClassSectionIds($user);
            $query->whereIn('class_section_id', $classSectionIds);
        }

        $query->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        Flash::success(count($resultIds) . ' marks approved successfully.');
        return redirect()->back();
    }
}
