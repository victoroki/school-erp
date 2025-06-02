<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateLeaveTypeRequest;
use App\Http\Requests\UpdateLeaveTypeRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\LeaveTypeRepository;
use Illuminate\Http\Request;
use Flash;

class LeaveTypeController extends AppBaseController
{
    /** @var LeaveTypeRepository $leaveTypeRepository*/
    private $leaveTypeRepository;

    public function __construct(LeaveTypeRepository $leaveTypeRepo)
    {
        $this->leaveTypeRepository = $leaveTypeRepo;
    }

    /**
     * Display a listing of the LeaveType.
     */
    public function index(Request $request)
    {
        $leaveTypes = $this->leaveTypeRepository->paginate(10);

        return view('leave_types.index')
            ->with('leaveTypes', $leaveTypes);
    }

    /**
     * Show the form for creating a new LeaveType.
     */
    public function create()
    {
        return view('leave_types.create');
    }

    /**
     * Store a newly created LeaveType in storage.
     */
    public function store(CreateLeaveTypeRequest $request)
    {
        $input = $request->all();

        $leaveType = $this->leaveTypeRepository->create($input);

        Flash::success('Leave Type saved successfully.');

        return redirect(route('leaveTypes.index'));
    }

    /**
     * Display the specified LeaveType.
     */
    public function show($id)
    {
        $leaveType = $this->leaveTypeRepository->find($id);

        if (empty($leaveType)) {
            Flash::error('Leave Type not found');

            return redirect(route('leaveTypes.index'));
        }

        return view('leave_types.show')->with('leaveType', $leaveType);
    }

    /**
     * Show the form for editing the specified LeaveType.
     */
    public function edit($id)
    {
        $leaveType = $this->leaveTypeRepository->find($id);

        if (empty($leaveType)) {
            Flash::error('Leave Type not found');

            return redirect(route('leaveTypes.index'));
        }

        return view('leave_types.edit')->with('leaveType', $leaveType);
    }

    /**
     * Update the specified LeaveType in storage.
     */
    public function update($id, UpdateLeaveTypeRequest $request)
    {
        $leaveType = $this->leaveTypeRepository->find($id);

        if (empty($leaveType)) {
            Flash::error('Leave Type not found');

            return redirect(route('leaveTypes.index'));
        }

        $leaveType = $this->leaveTypeRepository->update($request->all(), $id);

        Flash::success('Leave Type updated successfully.');

        return redirect(route('leaveTypes.index'));
    }

    /**
     * Remove the specified LeaveType from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $leaveType = $this->leaveTypeRepository->find($id);

        if (empty($leaveType)) {
            Flash::error('Leave Type not found');

            return redirect(route('leaveTypes.index'));
        }

        $this->leaveTypeRepository->delete($id);

        Flash::success('Leave Type deleted successfully.');

        return redirect(route('leaveTypes.index'));
    }
}
