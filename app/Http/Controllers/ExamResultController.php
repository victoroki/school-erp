<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExamResultRequest;
use App\Http\Requests\UpdateExamResultRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Exam;
use App\Models\Student;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\GradingScale;
use App\Repositories\ExamResultRepository;
use Illuminate\Http\Request;
use Flash;
use Auth;
use DB;

class ExamResultController extends AppBaseController
{
    public function bulk(Request $request)
    {
        $exams = Exam::pluck('name', 'exam_id');
        $classSections = ClassSection::with(['schoolClass', 'section'])->get()->mapWithKeys(function ($cs) {
            return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
        });
        $subjects = Subject::pluck('name', 'subject_id');

        $students = [];
        $existingResults = [];

        if ($request->filled(['exam_id', 'class_section_id', 'subject_id'])) {
            $students = Student::whereHas('studentClassEnrollments', function ($q) use ($request) {
                $q->where('class_section_id', $request->class_section_id)
                  ->where('status', 'active');
            })->orderBy('last_name')->get();

            $existingResults = ExamResult::where('exam_id', $request->exam_id)
                ->where('class_section_id', $request->class_section_id)
                ->where('subject_id', $request->subject_id)
                ->pluck('marks_obtained', 'student_id')
                ->toArray();
        }

        return view('exam_results.bulk', compact('exams', 'classSections', 'subjects', 'students', 'existingResults'));
    }

    public function postBulk(Request $request)
    {
        $request->validate([
            'exam_id' => 'required',
            'class_section_id' => 'required',
            'subject_id' => 'required',
            'marks' => 'required|array'
        ]);

        $exam_id = $request->exam_id;
        $class_section_id = $request->class_section_id;
        $subject_id = $request->subject_id;
        $marks = $request->marks; // array student_id => marks

        DB::transaction(function () use ($exam_id, $class_section_id, $subject_id, $marks) {
            foreach ($marks as $student_id => $mark) {
                if ($mark !== null && $mark !== '') {
                    ExamResult::updateOrCreate(
                        [
                            'exam_id' => $exam_id,
                            'student_id' => $student_id,
                            'class_section_id' => $class_section_id,
                            'subject_id' => $subject_id
                        ],
                        [
                            'marks_obtained' => $mark,
                            'created_by' => Auth::id()
                        ]
                    );
                }
            }
        });

        Flash::success('Marks updated successfully.');
        return redirect()->back()->withInput();
    }
    /** @var ExamResultRepository $examResultRepository*/
    private $examResultRepository;

    public function __construct(ExamResultRepository $examResultRepo)
    {
        $this->examResultRepository = $examResultRepo;
    }

    private function getDropdownData()
    {
        // Get students with full name and ID
        $students = Student::all()->mapWithKeys(function ($student) {
            return [$student->student_id => $student->first_name . ' ' . $student->last_name . ' (' . $student->admission_no . ')'];
        });

        // Get class sections with class name and section name
        $classSections = ClassSection::with(['class', 'section'])->get()->mapWithKeys(function ($cs) {
            $name = ($cs->class->name ?? 'N/A') . ' - ' . ($cs->section->name ?? 'N/A');
            return [$cs->class_section_id => $name];
        });

        return [
            'exams' => Exam::pluck('name', 'exam_id'),
            'students' => $students,
            'classSections' => $classSections,
            'subjects' => Subject::pluck('name', 'subject_id'),
        ];
    }

    /**
     * Display a listing of the ExamResult.
     */
    public function index(Request $request)
    {
        $examResults = $this->examResultRepository->paginate(10);

        return view('exam_results.index')
            ->with('examResults', $examResults);
    }

    /**
     * Show the form for creating a new ExamResult.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        
        return view('exam_results.create', $dropdownData);
    }

    /**
     * Store a newly created ExamResult in storage.
     */
    public function store(CreateExamResultRequest $request)
    {
        $input = $request->all();
        $input['created_by'] = Auth::id();

        $this->examResultRepository->create($input);

        Flash::success('Exam Result saved successfully.');

        return redirect(route('exam-results.index'));
    }

    /**
     * Display the specified ExamResult.
     */
    public function show($id)
    {
        $examResult = $this->examResultRepository->find($id);

        if (empty($examResult)) {
            Flash::error('Exam Result not found');
            return redirect(route('exam-results.index'));
        }

        return view('exam_results.show')->with('examResult', $examResult);
    }

    /**
     * Show the form for editing the specified ExamResult.
     */
    public function edit($id)
    {
        $examResult = $this->examResultRepository->find($id);

        if (empty($examResult)) {
            Flash::error('Exam Result not found');
            return redirect(route('exam-results.index'));
        }

        $dropdownData = $this->getDropdownData();

        return view('exam_results.edit', array_merge(['examResult' => $examResult], $dropdownData));
    }

    /**
     * Update the specified ExamResult in storage.
     */
    public function update($id, UpdateExamResultRequest $request)
    {
        $examResult = $this->examResultRepository->find($id);

        if (empty($examResult)) {
            Flash::error('Exam Result not found');
            return redirect(route('exam-results.index'));
        }

        $this->examResultRepository->update($request->all(), $id);

        Flash::success('Exam Result updated successfully.');

        return redirect(route('exam-results.index'));
    }

    /**
     * Remove the specified ExamResult from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $examResult = $this->examResultRepository->find($id);

        if (empty($examResult)) {
            Flash::error('Exam Result not found');
            return redirect(route('exam-results.index'));
        }

        $this->examResultRepository->delete($id);

        Flash::success('Exam Result deleted successfully.');

        return redirect(route('exam-results.index'));
    }
}