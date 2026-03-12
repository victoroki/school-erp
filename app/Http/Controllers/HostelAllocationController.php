<?php

namespace App\Http\Controllers;

use Flash;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Repositories\HostelAllocationRepository;
use App\Http\Requests\CreateHostelAllocationRequest;
use App\Http\Requests\UpdateHostelAllocationRequest;
use App\Models\AcademicYear;
use App\Models\Hostel;
use App\Models\HostelRoom;
use App\Models\HostelAllocation;

class HostelAllocationController extends AppBaseController
{
    /** @var HostelAllocationRepository $hostelAllocationRepository*/
    private $hostelAllocationRepository;

    public function __construct(HostelAllocationRepository $hostelAllocationRepo)
    {
        $this->hostelAllocationRepository = $hostelAllocationRepo;
    }

        private function getDropdownData()
    {
        return [
            'students' => Student::selectRaw("student_id, CONCAT(first_name, ' ', last_name, ' (', admission_no, ')') as full_name")
                ->pluck('full_name', 'student_id')
                ->toArray(),
            'hostels' => Hostel::pluck('name', 'hostel_id')->toArray(),
            'rooms' => HostelRoom::with('hostel')->where('status', '!=', 'full')
                ->where('status', '!=', 'under_maintenance')
                ->get()
                ->mapWithKeys(function ($room) {
                    return [$room->room_id => $room->room_number . " (" . ($room->hostel->name ?? 'N/A') . " - " . ($room->capacity - $room->occupied) . " beds left)"];
                })->toArray(),
            'academicYears' => AcademicYear::pluck('name', 'academic_year_id')->toArray()
        ];
    }

    /**
     * Display a listing of the HostelAllocation.
     */
    public function index(Request $request)
    {
        $query = \App\Models\HostelAllocation::with(['student', 'room', 'hostel', 'academicYear']);

        if ($request->has('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $hostelAllocations = $query->latest()->paginate(10);
        $hostels = Hostel::pluck('name', 'hostel_id')->toArray();

        return view('hostel_allocations.index', compact('hostelAllocations', 'hostels'));
    }

    /**
     * Show the form for creating a new HostelAllocation.
     */
    public function create()
    {
        $data = $this->getDropdownData();
        return view('hostel_allocations.create')->with($data);
    }

    /**
     * Store a newly created HostelAllocation in storage.
     */
    public function store(CreateHostelAllocationRequest $request)
    {
        $input = $request->all();

        // 1. Check student gender vs hostel type
        $student = Student::find($input['student_id']);
        $hostel = Hostel::find($input['hostel_id']);
        $room = HostelRoom::find($input['room_id']);

        if (!$student || !$hostel || !$room) {
            Flash::error('Invalid student, hostel, or room.');
            return redirect()->back()->withInput();
        }

        if ($hostel->type !== 'co-ed' && strtolower($student->gender) !== (strtolower($hostel->type) === 'boys' ? 'male' : 'female')) {
            Flash::error("Gender mismatch: This hostel is for " . $hostel->type . " but the student is " . $student->gender);
            return redirect()->back()->withInput();
        }

        // 2. Check room capacity
        if ($room->occupied >= $room->capacity) {
            Flash::error('Room is already full.');
            return redirect()->back()->withInput();
        }

        // 3. Create allocation
        $hostelAllocation = $this->hostelAllocationRepository->create($input);

        // 4. Update room occupancy
        $room->increment('occupied');
        if ($room->occupied >= $room->capacity) {
            $room->update(['status' => 'full']);
        } else if ($room->occupied > 0) {
            $room->update(['status' => 'partial']);
        }

        Flash::success('Hostel Allocation saved successfully.');

        return redirect(route('hostel-allocations.index'));
    }

    /**
     * Display the specified HostelAllocation.
     */
    public function show($id)
    {
        $hostelAllocation = HostelAllocation::with(['student', 'room', 'hostel', 'academicYear'])->find($id);

        if (empty($hostelAllocation)) {
            Flash::error('Hostel Allocation not found');
            return redirect(route('hostel-allocations.index'));
        }

        return view('hostel_allocations.show')->with('hostelAllocation', $hostelAllocation);
    }

    /**
     * Show the form for editing the specified HostelAllocation.
     */
    public function edit($id)
    {
        $data = $this->getDropdownData();
        $hostelAllocation = $this->hostelAllocationRepository->find($id);

        if (empty($hostelAllocation)) {
            Flash::error('Hostel Allocation not found');
            return redirect(route('hostel-allocations.index'));
        }

        return view('hostel_allocations.edit')->with(array_merge($data, ['hostelAllocation' => $hostelAllocation]));
    }

    /**
     * Update the specified HostelAllocation in storage.
     */
    public function update($id, UpdateHostelAllocationRequest $request)
    {
        $hostelAllocation = $this->hostelAllocationRepository->find($id);

        if (empty($hostelAllocation)) {
            Flash::error('Hostel Allocation not found');
            return redirect(route('hostel-allocations.index'));
        }

        $hostelAllocation = $this->hostelAllocationRepository->update($request->all(), $id);

        Flash::success('Hostel Allocation updated successfully.');

        return redirect(route('hostel-allocations.index'));
    }

    /**
     * Remove the specified HostelAllocation from storage.
     */
    public function destroy($id)
    {
        $hostelAllocation = $this->hostelAllocationRepository->find($id);

        if (empty($hostelAllocation)) {
            Flash::error('Hostel Allocation not found');
            return redirect(route('hostel-allocations.index'));
        }

        // Decrement room occupancy if active
        if ($hostelAllocation->status === 'active') {
            $room = $hostelAllocation->room;
            if ($room) {
                $room->decrement('occupied');
                if ($room->occupied == 0) {
                    $room->update(['status' => 'available']);
                } else {
                    $room->update(['status' => 'partial']);
                }
            }
        }

        $this->hostelAllocationRepository->delete($id);

        Flash::success('Hostel Allocation deleted successfully.');

        return redirect(route('hostel-allocations.index'));
    }

    /**
     * Checkout a student
     */
    public function checkout(Request $request, $id)
    {
        $hostelAllocation = HostelAllocation::find($id);
        if (!$hostelAllocation) {
            Flash::error('Allocation not found');
            return redirect()->back();
        }

        $hostelAllocation->update([
            'status' => 'vacated',
            'vacating_date' => now(),
            'checkout_notes' => $request->checkout_notes
        ]);

        $room = $hostelAllocation->room;
        if ($room) {
            $room->decrement('occupied');
            if ($room->occupied == 0) {
                $room->update(['status' => 'available']);
            } else {
                $room->update(['status' => 'partial']);
            }
        }

        Flash::success('Student checked out successfully.');
        return redirect()->back();
    }

    /**
     * Bulk allocation form
     */
    public function bulkForm()
    {
        $data = $this->getDropdownData();
        return view('hostel_allocations.bulk')->with($data);
    }

    /**
     * Bulk allocation store
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'hostel_id' => 'required',
            'room_id' => 'required',
            'allocation_date' => 'required|date'
        ]);

        $room = HostelRoom::find($request->room_id);
        $count = count($request->student_ids);

        if ($room->getAvailableBeds() < $count) {
            Flash::error("Not enough beds available in room " . $room->room_number . ". Available: " . $room->getAvailableBeds());
            return redirect()->back()->withInput();
        }

        foreach ($request->student_ids as $student_id) {
            HostelAllocation::create([
                'student_id' => $student_id,
                'hostel_id' => $request->hostel_id,
                'room_id' => $request->room_id,
                'allocation_date' => $request->allocation_date,
                'academic_year_id' => $request->academic_year_id,
                'status' => 'active'
            ]);
            $room->increment('occupied');
        }

        if ($room->occupied >= $room->capacity) {
            $room->update(['status' => 'full']);
        } else {
            $room->update(['status' => 'partial']);
        }

        Flash::success("$count students allocated successfully.");
        return redirect(route('hostel-allocations.index'));
    }

    /**
     * Transfer form
     */
    public function transferForm($id)
    {
        $hostelAllocation = HostelAllocation::with(['student', 'room', 'hostel'])->find($id);
        $data = $this->getDropdownData();
        return view('hostel_allocations.transfer', compact('hostelAllocation'))->with($data);
    }

    /**
     * Transfer store
     */
    public function transferStore(Request $request, $id)
    {
        $oldAllocation = HostelAllocation::find($id);
        $newRoom = HostelRoom::find($request->room_id);

        if ($newRoom->getAvailableBeds() < 1) {
            Flash::error('Target room is full.');
            return redirect()->back();
        }

        // Vacate old room
        $oldRoom = $oldAllocation->room;
        $oldRoom->decrement('occupied');
        if ($oldRoom->occupied == 0) {
            $oldRoom->update(['status' => 'available']);
        } else {
            $oldRoom->update(['status' => 'partial']);
        }

        $oldAllocation->update([
            'status' => 'vacated',
            'vacating_date' => now(),
            'checkout_notes' => 'Transferred to Room ' . $newRoom->room_number
        ]);

        // Create new allocation
        HostelAllocation::create([
            'student_id' => $oldAllocation->student_id,
            'hostel_id' => $newRoom->hostel_id,
            'room_id' => $newRoom->room_id,
            'allocation_date' => now(),
            'academic_year_id' => $oldAllocation->academic_year_id,
            'status' => 'active'
        ]);

        $newRoom->increment('occupied');
        if ($newRoom->occupied >= $newRoom->capacity) {
            $newRoom->update(['status' => 'full']);
        } else {
            $newRoom->update(['status' => 'partial']);
        }

        Flash::success('Student transferred successfully.');
        return redirect(route('hostel-allocations.index'));
    }
}
