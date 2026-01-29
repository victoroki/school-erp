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

class ExamResultController extends AppBaseController
{
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