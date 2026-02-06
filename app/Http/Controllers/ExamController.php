<?php

namespace App\Http\Controllers;

use Flash;
use App\Models\ExamType;
use Illuminate\Http\Request;
use App\Repositories\ExamRepository;
use App\Http\Requests\CreateExamRequest;
use App\Http\Requests\UpdateExamRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\AcademicYear;

class ExamController extends AppBaseController
{
    /** @var ExamRepository $examRepository*/
    private $examRepository;

    public function __construct(ExamRepository $examRepo)
    {
        $this->examRepository = $examRepo;
    }

    private function getDropdownData()
    {
        return [
            'examtypes' => ExamType::pluck('name', 'exam_type_id'),
            'academicYears' => AcademicYear::where('status', 1)->pluck('name', 'academic_year_id')
        ];
    }
    /**
     * Display a listing of the Exam.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Exam::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('exam_type_id')) {
            $query->where('exam_type_id', $request->exam_type_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $exams = $query->with(['examType', 'academicYear'])
            ->orderBy('start_date', 'desc')
            ->paginate(15)
            ->appends($request->all());

        $examTypes = ExamType::pluck('name', 'exam_type_id');
        $academicYears = AcademicYear::pluck('name', 'academic_year_id');

        return view('exams.index', compact('exams', 'examTypes', 'academicYears'));
    }

    /**
     * Show the form for creating a new Exam.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        return view('exams.create', $dropdownData);
    }

    /**
     * Store a newly created Exam in storage.
     */
    public function store(CreateExamRequest $request)
    {
        $input = $request->all();

        $exam = $this->examRepository->create($input);

        Flash::success('Exam saved successfully.');

        return redirect(route('exams.index'));
    }

    /**
     * Display the specified Exam.
     */
    public function show($id)
    {
        $exam = \App\Models\Exam::with([
            'examType', 
            'academicYear', 
            'examSchedules.subject', 
            'examSchedules.classSection.schoolClass'
        ])->find($id);

        if (empty($exam)) {
            Flash::error('Exam not found');
            return redirect(route('exams.index'));
        }

        // Basic Stats
        $totalResults = $exam->examResults()->count();
        $averageScore = $exam->examResults()->avg('marks_obtained') ?: 0;
        
        // Pass rate (Assuming 40% as pass mark if not defined)
        $passCount = $exam->examResults()->where('marks_obtained', '>=', 40)->count();
        $passPercentage = $totalResults > 0 ? round(($passCount / $totalResults) * 100, 1) : 0;

        return view('exams.show', compact('exam', 'totalResults', 'averageScore', 'passPercentage'));
    }

    /**
     * Show the form for editing the specified Exam.
     */
    public function edit($id)
    {
        $exam = $this->examRepository->find($id);
        $dropdownData = $this->getDropdownData();

        if (empty($exam)) {
            Flash::error('Exam not found');

            return redirect(route('exams.index'));
        }

        return view('exams.edit', array_merge(['exam' => $exam], $dropdownData));
    }

    /**
     * Update the specified Exam in storage.
     */
    public function update($id, UpdateExamRequest $request)
    {
        $exam = $this->examRepository->find($id);

        if (empty($exam)) {
            Flash::error('Exam not found');

            return redirect(route('exams.index'));
        }

        $exam = $this->examRepository->update($request->all(), $id);

        Flash::success('Exam updated successfully.');

        return redirect(route('exams.index'));
    }

    /**
     * Remove the specified Exam from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $exam = $this->examRepository->find($id);

        if (empty($exam)) {
            Flash::error('Exam not found');

            return redirect(route('exams.index'));
        }

        $this->examRepository->delete($id);

        Flash::success('Exam deleted successfully.');

        return redirect(route('exams.index'));
    }
}
