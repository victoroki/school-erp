<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExamTypeRequest;
use App\Http\Requests\UpdateExamTypeRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\ExamTypeRepository;
use Illuminate\Http\Request;
use Flash;

class ExamTypeController extends AppBaseController
{
    /** @var ExamTypeRepository $examTypeRepository*/
    private $examTypeRepository;

    public function __construct(ExamTypeRepository $examTypeRepo)
    {
        $this->examTypeRepository = $examTypeRepo;
    }

    /**
     * Display a listing of the ExamType.
     */
    public function index(Request $request)
    {
        $examTypes = $this->examTypeRepository->paginate(10);

        return view('exam_types.index')
            ->with('examTypes', $examTypes);
    }

    /**
     * Show the form for creating a new ExamType.
     */
    public function create()
    {
        return view('exam_types.create');
    }

    /**
     * Store a newly created ExamType in storage.
     */
    public function store(CreateExamTypeRequest $request)
    {
        $input = $request->all();

        $examType = $this->examTypeRepository->create($input);

        Flash::success('Exam Type saved successfully.');

        return redirect(route('examTypes.index'));
    }

    /**
     * Display the specified ExamType.
     */
    public function show($id)
    {
        $examType = $this->examTypeRepository->find($id);

        if (empty($examType)) {
            Flash::error('Exam Type not found');

            return redirect(route('examTypes.index'));
        }

        return view('exam_types.show')->with('examType', $examType);
    }

    /**
     * Show the form for editing the specified ExamType.
     */
    public function edit($id)
    {
        $examType = $this->examTypeRepository->find($id);

        if (empty($examType)) {
            Flash::error('Exam Type not found');

            return redirect(route('examTypes.index'));
        }

        return view('exam_types.edit')->with('examType', $examType);
    }

    /**
     * Update the specified ExamType in storage.
     */
    public function update($id, UpdateExamTypeRequest $request)
    {
        $examType = $this->examTypeRepository->find($id);

        if (empty($examType)) {
            Flash::error('Exam Type not found');

            return redirect(route('examTypes.index'));
        }

        $examType = $this->examTypeRepository->update($request->all(), $id);

        Flash::success('Exam Type updated successfully.');

        return redirect(route('examTypes.index'));
    }

    /**
     * Remove the specified ExamType from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $examType = $this->examTypeRepository->find($id);

        if (empty($examType)) {
            Flash::error('Exam Type not found');

            return redirect(route('examTypes.index'));
        }

        $this->examTypeRepository->delete($id);

        Flash::success('Exam Type deleted successfully.');

        return redirect(route('examTypes.index'));
    }
}
