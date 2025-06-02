<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExamScheduleRequest;
use App\Http\Requests\UpdateExamScheduleRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\ExamScheduleRepository;
use Illuminate\Http\Request;
use Flash;

class ExamScheduleController extends AppBaseController
{
    /** @var ExamScheduleRepository $examScheduleRepository*/
    private $examScheduleRepository;

    public function __construct(ExamScheduleRepository $examScheduleRepo)
    {
        $this->examScheduleRepository = $examScheduleRepo;
    }

    /**
     * Display a listing of the ExamSchedule.
     */
    public function index(Request $request)
    {
        $examSchedules = $this->examScheduleRepository->paginate(10);

        return view('exam_schedules.index')
            ->with('examSchedules', $examSchedules);
    }

    /**
     * Show the form for creating a new ExamSchedule.
     */
    public function create()
    {
        return view('exam_schedules.create');
    }

    /**
     * Store a newly created ExamSchedule in storage.
     */
    public function store(CreateExamScheduleRequest $request)
    {
        $input = $request->all();

        $examSchedule = $this->examScheduleRepository->create($input);

        Flash::success('Exam Schedule saved successfully.');

        return redirect(route('examSchedules.index'));
    }

    /**
     * Display the specified ExamSchedule.
     */
    public function show($id)
    {
        $examSchedule = $this->examScheduleRepository->find($id);

        if (empty($examSchedule)) {
            Flash::error('Exam Schedule not found');

            return redirect(route('examSchedules.index'));
        }

        return view('exam_schedules.show')->with('examSchedule', $examSchedule);
    }

    /**
     * Show the form for editing the specified ExamSchedule.
     */
    public function edit($id)
    {
        $examSchedule = $this->examScheduleRepository->find($id);

        if (empty($examSchedule)) {
            Flash::error('Exam Schedule not found');

            return redirect(route('examSchedules.index'));
        }

        return view('exam_schedules.edit')->with('examSchedule', $examSchedule);
    }

    /**
     * Update the specified ExamSchedule in storage.
     */
    public function update($id, UpdateExamScheduleRequest $request)
    {
        $examSchedule = $this->examScheduleRepository->find($id);

        if (empty($examSchedule)) {
            Flash::error('Exam Schedule not found');

            return redirect(route('examSchedules.index'));
        }

        $examSchedule = $this->examScheduleRepository->update($request->all(), $id);

        Flash::success('Exam Schedule updated successfully.');

        return redirect(route('examSchedules.index'));
    }

    /**
     * Remove the specified ExamSchedule from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $examSchedule = $this->examScheduleRepository->find($id);

        if (empty($examSchedule)) {
            Flash::error('Exam Schedule not found');

            return redirect(route('examSchedules.index'));
        }

        $this->examScheduleRepository->delete($id);

        Flash::success('Exam Schedule deleted successfully.');

        return redirect(route('examSchedules.index'));
    }
}
