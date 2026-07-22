<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Student;
use App\Models\Subject;
use App\Models\ExamResult;
use App\Models\ClassSection;
use Illuminate\Http\Request;

class GradeBookController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:academics.view');
    }

    public function index(Request $request)
    {
        $exams = Exam::pluck('name', 'exam_id');
        $classSections = ClassSection::with(['schoolClass', 'section'])->get()->mapWithKeys(function ($cs) {
            return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
        });

        $students = [];
        $subjects = [];
        $results = [];

        if ($request->filled(['exam_id', 'class_section_id'])) {
            // Get students in this class
            $students = Student::whereHas('studentClassEnrollments', function ($q) use ($request) {
                $q->where('class_section_id', $request->class_section_id)
                  ->where('status', 'active');
            })->orderBy('last_name')->get();

            // Get subjects assigned to this class OR subjects that have results for this exam/class
            // For simplicity, let's get all subjects first, or just those with results
            $subjectIdsWithResults = ExamResult::where('exam_id', $request->exam_id)
                ->where('class_section_id', $request->class_section_id)
                ->distinct()
                ->pluck('subject_id');
            
            $subjects = Subject::whereIn('subject_id', $subjectIdsWithResults)->get();

            // Fetch all results for this matrix
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
