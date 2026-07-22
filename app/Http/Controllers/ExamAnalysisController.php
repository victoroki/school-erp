<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Student;
use App\Models\ExamResult;
use App\Models\ClassSection;
use Illuminate\Http\Request;
use DB;

class ExamAnalysisController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:exams.view');
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
        $classSections = ClassSection::with(['schoolClass', 'section'])->get()->mapWithKeys(function ($cs) {
            return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
        });

        $rankings = [];

        if ($request->filled(['exam_id', 'class_section_id'])) {
            // Calculate rankings based on total marks for the exam and class
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
