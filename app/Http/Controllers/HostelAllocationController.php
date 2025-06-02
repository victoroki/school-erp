<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHostelAllocationRequest;
use App\Http\Requests\UpdateHostelAllocationRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\HostelAllocationRepository;
use Illuminate\Http\Request;
use Flash;

class HostelAllocationController extends AppBaseController
{
    /** @var HostelAllocationRepository $hostelAllocationRepository*/
    private $hostelAllocationRepository;

    public function __construct(HostelAllocationRepository $hostelAllocationRepo)
    {
        $this->hostelAllocationRepository = $hostelAllocationRepo;
    }

    /**
     * Display a listing of the HostelAllocation.
     */
    public function index(Request $request)
    {
        $hostelAllocations = $this->hostelAllocationRepository->paginate(10);

        return view('hostel_allocations.index')
            ->with('hostelAllocations', $hostelAllocations);
    }

    /**
     * Show the form for creating a new HostelAllocation.
     */
    public function create()
    {
        return view('hostel_allocations.create');
    }

    /**
     * Store a newly created HostelAllocation in storage.
     */
    public function store(CreateHostelAllocationRequest $request)
    {
        $input = $request->all();

        $hostelAllocation = $this->hostelAllocationRepository->create($input);

        Flash::success('Hostel Allocation saved successfully.');

        return redirect(route('hostelAllocations.index'));
    }

    /**
     * Display the specified HostelAllocation.
     */
    public function show($id)
    {
        $hostelAllocation = $this->hostelAllocationRepository->find($id);

        if (empty($hostelAllocation)) {
            Flash::error('Hostel Allocation not found');

            return redirect(route('hostelAllocations.index'));
        }

        return view('hostel_allocations.show')->with('hostelAllocation', $hostelAllocation);
    }

    /**
     * Show the form for editing the specified HostelAllocation.
     */
    public function edit($id)
    {
        $hostelAllocation = $this->hostelAllocationRepository->find($id);

        if (empty($hostelAllocation)) {
            Flash::error('Hostel Allocation not found');

            return redirect(route('hostelAllocations.index'));
        }

        return view('hostel_allocations.edit')->with('hostelAllocation', $hostelAllocation);
    }

    /**
     * Update the specified HostelAllocation in storage.
     */
    public function update($id, UpdateHostelAllocationRequest $request)
    {
        $hostelAllocation = $this->hostelAllocationRepository->find($id);

        if (empty($hostelAllocation)) {
            Flash::error('Hostel Allocation not found');

            return redirect(route('hostelAllocations.index'));
        }

        $hostelAllocation = $this->hostelAllocationRepository->update($request->all(), $id);

        Flash::success('Hostel Allocation updated successfully.');

        return redirect(route('hostelAllocations.index'));
    }

    /**
     * Remove the specified HostelAllocation from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $hostelAllocation = $this->hostelAllocationRepository->find($id);

        if (empty($hostelAllocation)) {
            Flash::error('Hostel Allocation not found');

            return redirect(route('hostelAllocations.index'));
        }

        $this->hostelAllocationRepository->delete($id);

        Flash::success('Hostel Allocation deleted successfully.');

        return redirect(route('hostelAllocations.index'));
    }
}
