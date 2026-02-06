<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Student;
use App\Models\ExamResult;
use App\Models\ClassSection;
use App\Models\ReportCardTemplate;
use Illuminate\Http\Request;

class ExamReportController extends Controller
{
    public function generate(Request $request)
    {
        $exams = Exam::pluck('name', 'exam_id');
        $classSections = ClassSection::with(['schoolClass', 'section'])->get()->mapWithKeys(function ($cs) {
            return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
        });
        $templates = ReportCardTemplate::where('status', true)->pluck('name', 'id');

        return view('exam_reports.generate', compact('exams', 'classSections', 'templates'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'exam_id' => 'required',
            'class_section_id' => 'required',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $classSection = ClassSection::with(['schoolClass', 'section'])->findOrFail($request->class_section_id);
        
        $students = Student::whereHas('studentClassEnrollments', function ($q) use ($request) {
            $q->where('class_section_id', $request->class_section_id)
              ->where('status', 'active');
        })->get();

        // In a real app, we would generate a PDF or a consolidated view.
        // For this demo, let's show a list of students with links to their individual report cards.
        return view('exam_reports.bulk_list', compact('exam', 'classSection', 'students'));
    }

    public function individual($exam_id, $student_id)
    {
        $exam = Exam::findOrFail($exam_id);
        $student = Student::findOrFail($student_id);
        
        $results = ExamResult::with(['subject', 'grade', 'classSection'])
            ->where('exam_id', $exam_id)
            ->where('student_id', $student_id)
            ->get();

        return view('exam_reports.templates.standard', compact('exam', 'student', 'results'));
    }
}
