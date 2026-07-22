<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Subject;
use App\Models\ExamResult;
use App\Models\ClassSection;
use Illuminate\Http\Request;

class MarkSheetController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:exams.view');
    }

    public function index(Request $request)
    {
        $exams = Exam::pluck('name', 'exam_id');
        $classSections = ClassSection::with(['schoolClass', 'section'])->get()->mapWithKeys(function ($cs) {
            return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
        });
        $subjects = Subject::pluck('name', 'subject_id');

        $results = [];

        if ($request->filled(['exam_id', 'class_section_id', 'subject_id'])) {
            $results = ExamResult::with(['student', 'grade'])
                ->where('exam_id', $request->exam_id)
                ->where('class_section_id', $request->class_section_id)
                ->where('subject_id', $request->subject_id)
                ->get();
        }

        return view('mark_sheets.index', compact('exams', 'classSections', 'subjects', 'results'));
    }
}
