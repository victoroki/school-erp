<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExamResultRequest;
use App\Http\Requests\UpdateExamResultRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Exam;
use App\Models\Student;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\GradingScale;
use App\Models\Staff;
use App\Models\User;
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

    private function getDropdownData(){
        return [
            'exams' => Exam::selectRaw("exam_id, CONCAT(name, ' - ', exam_type_id) as display_name")
                ->pluck('display_name', 'id')
                ->toArray(),
            'students' => Student::selectRaw("student_id, CONCAT(first_name, ' ', last_name, ' (', student_id, ')') as full_name")
                ->pluck('full_name', 'id')
                ->toArray(),
            'classSections' => ClassSection::with(['class', 'section', 'academicYear'])
                ->get()
                ->pluck('display_name', 'id')
                ->toArray(),
            'subjects' => Subject::pluck('name', 'subject_id')
                ->toArray(),
            'grades' => GradingScale::orderBy('min_percentage', 'asc')
                ->selectRaw("grade_id, CONCAT(name, ' (', min_percentage, '-', max_percentage, ')') as grade_display")
                ->pluck('grade_display', 'id')
                ->toArray(),
            'teachers' => Staff::where('staff_type', 'teacher')
                ->selectRaw("staff_id, CONCAT(first_name, ' ', last_name) as full_name")
                ->pluck('full_name', 'id')
                ->toArray()
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
        
        // Auto-assign the logged-in user as creator
        $input['created_by'] = Auth::id();
        
        // Auto-calculate grade based on marks if not provided
        if (empty($input['grade_id']) && !empty($input['marks_obtained'])) {
            $grade = Grade::where('min_marks', '<=', $input['marks_obtained'])
                         ->where('max_marks', '>=', $input['marks_obtained'])
                         ->first();
            if ($grade) {
                $input['grade_id'] = $grade->id;
            }
        }

        $examResult = $this->examResultRepository->create($input);

        Flash::success('Exam Result saved successfully.');

        return redirect(route('examResults.index'));
    }

    /**
     * Display the specified ExamResult.
     */
    public function show($id)
    {
        $examResult = $this->examResultRepository->find($id);

        if (empty($examResult)) {
            Flash::error('Exam Result not found');

            return redirect(route('examResults.index'));
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

            return redirect(route('examResults.index'));
        }

        $dropdownData = $this->getDropdownData();

        return view('exam_results.edit')
            ->with('examResult', $examResult)
            ->with($dropdownData);
    }

    /**
     * Update the specified ExamResult in storage.
     */
    public function update($id, UpdateExamResultRequest $request)
    {
        $examResult = $this->examResultRepository->find($id);

        if (empty($examResult)) {
            Flash::error('Exam Result not found');

            return redirect(route('examResults.index'));
        }

        $input = $request->all();
        
        // Auto-calculate grade based on marks if not provided
        if (empty($input['grade_id']) && !empty($input['marks_obtained'])) {
            $grade = Grade::where('min_marks', '<=', $input['marks_obtained'])
                         ->where('max_marks', '>=', $input['marks_obtained'])
                         ->first();
            if ($grade) {
                $input['grade_id'] = $grade->id;
            }
        }

        $examResult = $this->examResultRepository->update($input, $id);

        Flash::success('Exam Result updated successfully.');

        return redirect(route('examResults.index'));
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

            return redirect(route('examResults.index'));
        }

        $this->examResultRepository->delete($id);

        Flash::success('Exam Result deleted successfully.');

        return redirect(route('examResults.index'));
    }

    /**
     * Get subjects by class section (AJAX endpoint)
     */
    public function getSubjectsByClassSection($classSectionId)
    {
        $subjects = Subject::whereHas('classSections', function($query) use ($classSectionId) {
            $query->where('class_section_id', $classSectionId);
        })->where('status', 'active')->get();

        return response()->json($subjects);
    }

    /**
     * Get students by class section (AJAX endpoint)
     */
    public function getStudentsByClassSection($classSectionId)
    {
        $students = Student::whereHas('enrollments', function($query) use ($classSectionId) {
            $query->where('class_section_id', $classSectionId)
                  ->where('status', 'active');
        })->get();

        return response()->json($students);
    }
}