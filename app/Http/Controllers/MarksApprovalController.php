<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ClassSection;
use Illuminate\Http\Request;
use Flash;
use Auth;

class MarksApprovalController extends Controller
{
    public function index(Request $request)
    {
        $exams = Exam::pluck('name', 'exam_id');
        $classSections = ClassSection::with(['schoolClass', 'section'])->get()->mapWithKeys(function ($cs) {
            return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
        });

        $query = ExamResult::with(['student', 'subject', 'exam', 'classSection'])
            ->where('is_approved', false);

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

        ExamResult::whereIn('result_id', $resultIds)->update([
            'is_approved' => true,
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        Flash::success(count($resultIds) . ' marks approved successfully.');
        return redirect()->back();
    }
}
