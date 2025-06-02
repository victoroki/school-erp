<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExamResultRequest;
use App\Http\Requests\UpdateExamResultRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\ExamResultRepository;
use Illuminate\Http\Request;
use Flash;

class ExamResultController extends AppBaseController
{
    /** @var ExamResultRepository $examResultRepository*/
    private $examResultRepository;

    public function __construct(ExamResultRepository $examResultRepo)
    {
        $this->examResultRepository = $examResultRepo;
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
        return view('exam_results.create');
    }

    /**
     * Store a newly created ExamResult in storage.
     */
    public function store(CreateExamResultRequest $request)
    {
        $input = $request->all();

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

        return view('exam_results.edit')->with('examResult', $examResult);
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

        $examResult = $this->examResultRepository->update($request->all(), $id);

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
}
