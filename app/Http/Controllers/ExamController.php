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
use App\Models\AuditTrail;

class ExamController extends AppBaseController
{
    /** @var ExamRepository $examRepository*/
    private $examRepository;

    public function __construct(ExamRepository $examRepo)
    {
        $this->examRepository = $examRepo;
        $this->middleware('can:academics.settings.manage')->only(['index', 'show', 'create', 'store', 'edit', 'update', 'destroy']);
    }

    private function getDropdownData()
    {
        return [
            'examtypes' => ExamType::pluck('name', 'exam_type_id'),
            'academicYears' => AcademicYear::orderBy('start_date', 'desc')->pluck('name', 'academic_year_id')
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

        AuditTrail::log('Exam', 'CREATE', $exam->exam_id, null, $exam->toArray());

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
            'examSchedules.class'
        ])->find($id);

        if (empty($exam)) {
            Flash::error('Exam not found');
            return redirect(route('exams.index'));
        }

        // Statistics are computed on the PERCENTAGE each learner scored, not on
        // raw marks. Raw marks are out of whatever maximum the paper defines, so
        // averaging them is not a mean score and "marks >= 40" is not a 40% pass.
        //
        // exam_results has no max_marks column — the per-paper maximum lives on
        // exam_schedules, keyed by class and subject. Build the whole map once
        // rather than letting the percentage accessor fire a query per result
        // (249 results on the largest live exam).
        $scheduleBySubject = $exam->examSchedules->groupBy('subject_id');

        $maxMarksBySubject = $scheduleBySubject
            ->map(fn ($rows) => (float) $rows->min('max_marks'));

        // The paper's own pass mark lives on the schedule as a RAW mark, so it
        // has to be converted to a percentage to compare against a percentage.
        // Previously the controller hardcoded `marks_obtained >= 40`, which is
        // only a 40% pass when the paper happens to be out of 100.
        $passPercentBySubject = $scheduleBySubject->map(function ($rows) {
            $max = (float) $rows->min('max_marks');
            $pass = (float) $rows->min('passing_marks');

            if ($max <= 0) {
                return 40.0;
            }

            return $pass > 0 ? round(($pass / $max) * 100, 2) : 40.0;
        });

        $results = $exam->examResults()->get(['result_id', 'student_id', 'subject_id', 'marks_obtained']);

        $percentages = $results->map(function ($result) use ($maxMarksBySubject) {
            $max = (float) ($maxMarksBySubject[$result->subject_id] ?? 100);

            return $max > 0 ? round(((float) $result->marks_obtained / $max) * 100, 2) : 0.0;
        });

        $totalResults = $results->count();
        $averageScore = $percentages->isNotEmpty() ? round($percentages->avg(), 2) : 0;
        $highestScore = $percentages->isNotEmpty() ? round($percentages->max(), 1) : null;
        $lowestScore = $percentages->isNotEmpty() ? round($percentages->min(), 1) : null;

        $passPercentage = $percentages->isNotEmpty()
            ? round(
                $results->filter(function ($result, $index) use ($percentages, $passPercentBySubject) {
                    $threshold = (float) ($passPercentBySubject[$result->subject_id] ?? 40.0);

                    return $percentages[$index] >= $threshold;
                })->count() / $percentages->count() * 100,
                1
            )
            : 0;

        return view('exams.show', compact(
            'exam',
            'totalResults',
            'averageScore',
            'highestScore',
            'lowestScore',
            'passPercentage'
        ));
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

        $oldData = $exam->toArray();
        $exam = $this->examRepository->update($request->all(), $id);

        AuditTrail::log('Exam', 'UPDATE', $exam->exam_id, $oldData, $exam->toArray());

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

        $oldData = $exam->toArray();
        $this->examRepository->delete($id);

        AuditTrail::log('Exam', 'DELETE', $id, $oldData, null);

        Flash::success('Exam deleted successfully.');

        return redirect(route('exams.index'));
    }
}
