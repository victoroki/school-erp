<?php

namespace App\Http\Controllers;

use App\Exceptions\HostelAllocationException;
use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateHostelRoomRequest;
use App\Http\Requests\UpdateHostelRoomRequest;
use App\Models\AuditTrail;
use App\Models\Hostel;
use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use App\Repositories\HostelRoomRepository;
use App\Services\HostelAllocationService;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Hostel room CRUD.
 *
 * `occupied` and the derived part of `status` are owned by
 * HostelAllocationService: this controller never writes them from form input,
 * it only records the manual "under maintenance" state.
 */
class HostelRoomController extends AppBaseController
{
    /** @var HostelRoomRepository $hostelRoomRepository*/
    private $hostelRoomRepository;

    public function __construct(
        HostelRoomRepository $hostelRoomRepo,
        private HostelAllocationService $allocationService
    ) {
        $this->hostelRoomRepository = $hostelRoomRepo;
        $this->middleware('can:hostel.view')->only(['index', 'show']);
        $this->middleware('can:hostel.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    private function getDropdownData(): array
    {
        return [
            'hostels' => Hostel::pluck('name', 'hostel_id')->toArray(),
        ];
    }

    /**
     * Display a listing of the HostelRoom.
     */
    public function index(Request $request)
    {
        $query = $this->hostelRoomRepository->allQuery()->with('hostel');

        if ($request->filled('status')) {
            $request->validate([
                'status' => Rule::in(HostelRoom::STATUSES),
            ]);
            $query->where('status', $request->status);
        }

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        if ($request->filled('has_beds')) {
            $request->boolean('has_beds')
                ? $query->whereColumn('occupied', '<', 'capacity')
                : $query->whereColumn('occupied', '>=', 'capacity');
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('room_number', 'like', $term)->orWhere('floor', 'like', $term);
            });
        }

        $hostelRooms = $query->orderBy('hostel_id')->orderBy('room_number')->paginate(15)->withQueryString();
        $hostels = Hostel::pluck('name', 'hostel_id')->toArray();

        return view('hostel_rooms.index', compact('hostelRooms', 'hostels'));
    }

    /**
     * Show the form for creating a new HostelRoom.
     */
    public function create()
    {
        return view('hostel_rooms.create')->with($this->getDropdownData());
    }

    /**
     * Store a newly created HostelRoom in storage.
     */
    public function store(CreateHostelRoomRequest $request)
    {
        $input = $request->validated();

        // Occupancy starts empty; a brand new room can only be available or
        // under maintenance.
        $input['status'] = ($input['status'] ?? HostelRoom::STATUS_AVAILABLE) === HostelRoom::STATUS_UNDER_MAINTENANCE
            ? HostelRoom::STATUS_UNDER_MAINTENANCE
            : HostelRoom::STATUS_AVAILABLE;

        $hostelRoom = $this->hostelRoomRepository->create($input);
        $hostelRoom = $this->allocationService->syncRoom($hostelRoom);

        AuditTrail::log('Hostel Room', 'CREATE', $hostelRoom->room_id, null, $hostelRoom->toArray());

        Flash::success('Hostel Room saved successfully.');

        return redirect(route('hostel-rooms.index'));
    }

    /**
     * Display the specified HostelRoom.
     */
    public function show($id)
    {
        $hostelRoom = HostelRoom::with(['hostel', 'hostelAllocations' => fn ($q) => $q->withDisplayContext()])
            ->find($id);

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

        if (empty($hostelRoom)) {
            Flash::error('Hostel Room not found');
            return redirect(route('hostel-rooms.index'));
        }

        return view('hostel_rooms.edit', compact('hostelRoom'))->with($this->getDropdownData());
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

        $input = $request->validated();

        try {
            $hostelRoom = DB::transaction(function () use ($hostelRoom, $input, $id) {
                // Lock the row so a concurrent allocation cannot slip a student
                // in between the capacity check and the write.
                $room = HostelRoom::where('room_id', $id)->lockForUpdate()->firstOrFail();

                $activeAllocations = HostelAllocation::where('room_id', $room->room_id)
                    ->where('status', 'active')
                    ->count();

                // Shrinking a room below the students it already holds would
                // push occupancy past capacity, so it is rejected with a clear
                // message rather than silently over-filling the room.
                if ((int) $input['capacity'] < $activeAllocations) {
                    throw new HostelAllocationException(sprintf(
                        'Room %s already holds %d student%s. Its capacity cannot be set below %d — move students to another room first.',
                        $room->room_number,
                        $activeAllocations,
                        $activeAllocations === 1 ? '' : 's',
                        $activeAllocations
                    ));
                }

                // Only the maintenance state is user controlled, and an explicit
                // "available" is allowed to clear it; full/available are then
                // re-derived from the bed occupancy below.
                $requestedStatus = $input['status'] ?? null;

                if ($requestedStatus === HostelRoom::STATUS_UNDER_MAINTENANCE) {
                    $input['status'] = HostelRoom::STATUS_UNDER_MAINTENANCE;
                } elseif ($requestedStatus === HostelRoom::STATUS_AVAILABLE) {
                    $input['status'] = HostelRoom::STATUS_AVAILABLE;
                } else {
                    $input['status'] = $room->status;
                }

                $oldData = $room->toArray();
                $updated = $this->hostelRoomRepository->update($input, $id);

                // Re-derive occupancy/status in case the capacity changed.
                $updated = $this->allocationService->syncRoom($updated->refresh());

                return [$updated, $oldData];
            });
        } catch (HostelAllocationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back()->withInput();
        }

        [$hostelRoom, $oldData] = $hostelRoom;

        AuditTrail::log('Hostel Room', 'UPDATE', $hostelRoom->room_id, $oldData, $hostelRoom->toArray());

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

        $oldData = $hostelRoom->toArray();
        $this->hostelRoomRepository->delete($id);

        AuditTrail::log('Hostel Room', 'DELETE', $id, $oldData, null);

        Flash::success('Hostel Room deleted successfully.');

        return redirect(route('hostel-rooms.index'));
    }
}
