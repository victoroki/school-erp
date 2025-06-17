<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateJobPositionRequest;
use App\Http\Requests\UpdateJobPositionRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Department;
use App\Repositories\JobPositionRepository;
use Illuminate\Http\Request;
use Flash;

class JobPositionController extends AppBaseController
{
    /** @var JobPositionRepository $jobPositionRepository*/
    private $jobPositionRepository;

    public function __construct(JobPositionRepository $jobPositionRepo)
    {
        $this->jobPositionRepository = $jobPositionRepo;
    }

    /**
     * Display a listing of the JobPosition.
     */
    public function index(Request $request)
    {
        $jobPositions = $this->jobPositionRepository->paginate(10);

        return view('job_positions.index')
            ->with('jobPositions', $jobPositions);
    }

    private function getdropdownData(){
        return[
            'department' => Department::pluck('name', 'department_id')
        ];
    }

    /**
     * Show the form for creating a new JobPosition.
     */
    public function create()
    {
        $dropdownData = $this->getdropdownData();
        return view('job_positions.create', $dropdownData);
    }

    /**
     * Store a newly created JobPosition in storage.
     */
    public function store(CreateJobPositionRequest $request)
    {
        $input = $request->all();

        $jobPosition = $this->jobPositionRepository->create($input);

        Flash::success('Job Position saved successfully.');

        return redirect(route('jobPositions.index'));
    }

    /**
     * Display the specified JobPosition.
     */
    public function show($id)
    {
        $jobPosition = $this->jobPositionRepository->find($id);

        if (empty($jobPosition)) {
            Flash::error('Job Position not found');

            return redirect(route('jobPositions.index'));
        }

        return view('job_positions.show')->with('jobPosition', $jobPosition);
    }

    /**
     * Show the form for editing the specified JobPosition.
     */
    public function edit($id)
    {
        $jobPosition = $this->jobPositionRepository->find($id);
        $dropdownData = $this->getdropdownData();
        if (empty($jobPosition)) {
            Flash::error('Job Position not found');

            return redirect(route('jobPositions.index'));
        }

        return view('job_positions.edit', array_merge(
            [
                'jobPosition', $jobPosition,
                $dropdownData
            ]
        ));
    }

    /**
     * Update the specified JobPosition in storage.
     */
    public function update($id, UpdateJobPositionRequest $request)
    {
        $jobPosition = $this->jobPositionRepository->find($id);

        if (empty($jobPosition)) {
            Flash::error('Job Position not found');

            return redirect(route('jobPositions.index'));
        }

        $jobPosition = $this->jobPositionRepository->update($request->all(), $id);

        Flash::success('Job Position updated successfully.');

        return redirect(route('jobPositions.index'));
    }

    /**
     * Remove the specified JobPosition from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $jobPosition = $this->jobPositionRepository->find($id);

        if (empty($jobPosition)) {
            Flash::error('Job Position not found');

            return redirect(route('jobPositions.index'));
        }

        $this->jobPositionRepository->delete($id);

        Flash::success('Job Position deleted successfully.');

        return redirect(route('jobPositions.index'));
    }
}
