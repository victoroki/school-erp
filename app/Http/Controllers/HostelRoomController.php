<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHostelRoomRequest;
use App\Http\Requests\UpdateHostelRoomRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Hostel;
use App\Repositories\HostelRoomRepository;
use Illuminate\Http\Request;
use Flash;

class HostelRoomController extends AppBaseController
{
    /** @var HostelRoomRepository $hostelRoomRepository*/
    private $hostelRoomRepository;

    public function __construct(HostelRoomRepository $hostelRoomRepo)
    {
        $this->hostelRoomRepository = $hostelRoomRepo;
    }
    private function getDropdownData(){
        return[
            'hostels' => Hostel::pluck('name', 'hostel_id')
        ];
    }

    /**
     * Display a listing of the HostelRoom.
     */
    public function index(Request $request)
    {
        $query = $this->hostelRoomRepository->allQuery()->with('hostel');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        $hostelRooms = $query->paginate(10);
        $hostels = Hostel::pluck('name', 'hostel_id')->toArray();

        return view('hostel_rooms.index', compact('hostelRooms', 'hostels'));
    }

    /**
     * Show the form for creating a new HostelRoom.
     */
    public function create()
    {
        $data = $this->getDropdownData();
        return view('hostel_rooms.create')->with($data);
    }

    /**
     * Store a newly created HostelRoom in storage.
     */
    public function store(CreateHostelRoomRequest $request)
    {
        $input = $request->all();
        
        // Initial status based on occupancy
        if ($input['status'] !== 'under_maintenance') {
            $occupied = $input['occupied'] ?? 0;
            $capacity = $input['capacity'];
            if ($occupied >= $capacity) $input['status'] = 'full';
            else if ($occupied > 0) $input['status'] = 'partial';
            else $input['status'] = 'available';
        }

        $hostelRoom = $this->hostelRoomRepository->create($input);

        Flash::success('Hostel Room saved successfully.');

        return redirect(route('hostel-rooms.index'));
    }

    /**
     * Display the specified HostelRoom.
     */
    public function show($id)
    {
        $hostelRoom = \App\Models\HostelRoom::with(['hostel', 'hostelAllocations.student'])->find($id);

        if (empty($hostelRoom)) {
            Flash::error('Hostel Room not found');
            return redirect(route('hostel-rooms.index'));
        }

        return view('hostel_rooms.show', compact('hostelRoom'));
    }

    /**
     * Show the form for editing the specified HostelRoom.
     */
    public function edit($id)
    {
        $hostelRoom = $this->hostelRoomRepository->find($id);
        $data = $this->getDropdownData();

        if (empty($hostelRoom)) {
            Flash::error('Hostel Room not found');
            return redirect(route('hostel-rooms.index'));
        }

        return view('hostel_rooms.edit', compact('hostelRoom'))->with($data);
    }

    /**
     * Update the specified HostelRoom in storage.
     */
    public function update($id, UpdateHostelRoomRequest $request)
    {
        $hostelRoom = $this->hostelRoomRepository->find($id);

        if (empty($hostelRoom)) {
            Flash::error('Hostel Room not found');
            return redirect(route('hostel-rooms.index'));
        }

        $input = $request->all();
        
        // Auto-update status based on occupancy change, unless maintenance is forced
        if ($input['status'] !== 'under_maintenance') {
            $occupied = $input['occupied'] ?? $hostelRoom->occupied;
            $capacity = $input['capacity'] ?? $hostelRoom->capacity;
            if ($occupied >= $capacity) $input['status'] = 'full';
            else if ($occupied > 0) $input['status'] = 'partial';
            else $input['status'] = 'available';
        }

        $hostelRoom = $this->hostelRoomRepository->update($input, $id);

        Flash::success('Hostel Room updated successfully.');

        return redirect(route('hostel-rooms.index'));
    }

    /**
     * Remove the specified HostelRoom from storage.
     */
    public function destroy($id)
    {
        $hostelRoom = $this->hostelRoomRepository->find($id);

        if (empty($hostelRoom)) {
            Flash::error('Hostel Room not found');
            return redirect(route('hostel-rooms.index'));
        }

        // Check if room has active allocations
        if ($hostelRoom->hostelAllocations()->where('status', 'active')->count() > 0) {
            Flash::error('Cannot delete room with active student allocations. Please vacate or transfer students first.');
            return redirect()->back();
        }

        $this->hostelRoomRepository->delete($id);

        Flash::success('Hostel Room deleted successfully.');

        return redirect(route('hostel-rooms.index'));
    }
}
