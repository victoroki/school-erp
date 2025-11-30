<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransportAssignmentRequest;
use App\Http\Requests\UpdateTransportAssignmentRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\TransportAssignmentRepository;
use App\Models\Route;
use App\Models\Vehicle;
use App\Models\Staff;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Arr;

class TransportAssignmentController extends AppBaseController
{
    /** @var TransportAssignmentRepository $transportAssignmentRepository*/
    private $transportAssignmentRepository;

    public function __construct(TransportAssignmentRepository $transportAssignmentRepo)
    {
        $this->transportAssignmentRepository = $transportAssignmentRepo;
    }

    private function getDropdownData()
    {
        return [
            'routes' => Route::pluck('name', 'route_id'),
            'vehicles' => Vehicle::pluck('vehicle_number', 'vehicle_id'),
            'drivers' => Staff::where('staff_type', 'driver')->pluck('first_name', 'staff_id'),
            'assistants' => Staff::where('staff_type', 'assistant')->pluck('first_name', 'staff_id')
        ];
    }

    /**
     * Display a listing of the TransportAssignment.
     */
    public function index(Request $request)
    {
        $transportAssignments = $this->transportAssignmentRepository->paginate(10);

        return view('transport_assignments.index')
            ->with('transportAssignments', $transportAssignments);
    }

    /**
     * Show the form for creating a new TransportAssignment.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        return view('transport_assignments.create', $dropdownData);
    }

    /**
     * Store a newly created TransportAssignment in storage.
     */
    public function store(CreateTransportAssignmentRequest $request)
    {
        $input = $request->all();

        $transportAssignment = $this->transportAssignmentRepository->create($input);

        Flash::success('Transport Assignment saved successfully.');

        return redirect(route('transport-assignments.index'));
    }

    /**
     * Display the specified TransportAssignment.
     */
    public function show($id)
    {
        $transportAssignment = $this->transportAssignmentRepository->find($id);

        if (empty($transportAssignment)) {
            Flash::error('Transport Assignment not found');

            return redirect(route('transport-assignments.index'));
        }

        return view('transport_assignments.show')->with('transportAssignment', $transportAssignment);
    }

    /**
     * Show the form for editing the specified TransportAssignment.
     */
    public function edit($id)
    {
        $transportAssignment = $this->transportAssignmentRepository->find($id);

        if (empty($transportAssignment)) {
            Flash::error('Transport Assignment not found');

            return redirect(route('transport-assignments.index'));
        }

        $dropdownData = $this->getDropdownData();
        return view('transport_assignments.edit', array_merge(['transportAssignment' => $transportAssignment], $dropdownData));
    }

    /**
     * Update the specified TransportAssignment in storage.
     */
    public function update($id, UpdateTransportAssignmentRequest $request)
    {
        $transportAssignment = $this->transportAssignmentRepository->find($id);

        if (empty($transportAssignment)) {
            Flash::error('Transport Assignment not found');

            return redirect(route('transport-assignments.index'));
        }

        $transportAssignment = $this->transportAssignmentRepository->update($request->all(), $id);

        Flash::success('Transport Assignment updated successfully.');

        return redirect(route('transport-assignments.index'));
    }

    /**
     * Remove the specified TransportAssignment from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $transportAssignment = $this->transportAssignmentRepository->find($id);

        if (empty($transportAssignment)) {
            Flash::error('Transport Assignment not found');

            return redirect(route('transport-assignments.index'));
        }

        $this->transportAssignmentRepository->delete($id);

        Flash::success('Transport Assignment deleted successfully.');

        return redirect(route('transport-assignments.index'));
    }
}