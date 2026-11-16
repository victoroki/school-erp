<?php

namespace App\Http\Controllers;

use App\Exceptions\HostelAllocationException;
use App\Http\Requests\CreateHostelAllocationRequest;
use App\Http\Requests\UpdateHostelAllocationRequest;
use App\Models\AcademicYear;
use App\Models\AuditTrail;
use App\Models\Hostel;
use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Repositories\HostelAllocationRepository;
use App\Services\HostelAllocationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Flash;
use Illuminate\Http\Request;

/**
 * Single, bulk, transfer and checkout bed allocation.
 *
 * Every write is delegated to HostelAllocationService so occupancy, room status
 * and allocation rows stay consistent and a failure cannot half-apply.
 */
class HostelAllocationController extends AppBaseController
{
    /** @var HostelAllocationRepository $hostelAllocationRepository*/
    private $hostelAllocationRepository;

    public function __construct(
        HostelAllocationRepository $hostelAllocationRepo,
        private HostelAllocationService $allocationService
    ) {
        $this->hostelAllocationRepository = $hostelAllocationRepo;
        $this->middleware('can:hostel.view')->only(['index', 'show', 'export']);
        $this->middleware('can:hostel.manage')->only([
            'create', 'store', 'edit', 'update', 'destroy',
            'bulkForm', 'bulkStore', 'transferForm', 'transferStore', 'checkout',
        ]);
    }

    private function getDropdownData(?int $includeRoomId = null): array
    {
        return [
            // `dropdown_name` alias: `full_name` would be shadowed by the
            // Student fullName accessor and resolve to empty strings.
            'students' => Student::selectRaw("student_id, CONCAT(first_name, ' ', last_name, ' (', admission_no, ')') as dropdown_name")
                ->orderBy('first_name')
                ->pluck('dropdown_name', 'student_id')
                ->toArray(),
            'hostels' => Hostel::pluck('name', 'hostel_id')->toArray(),
            'rooms' => $this->allocationService->roomOptions(null, $includeRoomId),
            // Lets the room <select> filter itself by hostel without AJAX.
            'roomHostels' => HostelRoom::pluck('hostel_id', 'room_id')->toArray(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->pluck('name', 'academic_year_id')->toArray(),
        ];
    }

    /**
     * Display a listing of the HostelAllocation.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'hostel_id', 'room_id', 'status', 'academic_year_id', 'class_id', 'section_id', 'search',
        ]);

        $hostelAllocations = HostelAllocation::filter($filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('hostel_allocations.index', [
            'hostelAllocations' => $hostelAllocations,
            'hostels' => Hostel::pluck('name', 'hostel_id')->toArray(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->pluck('name', 'academic_year_id')->toArray(),
            'classes' => SchoolClass::orderBy('numeric_value')->pluck('name', 'class_id')->toArray(),
            'sections' => Section::orderBy('name')->pluck('name', 'section_id')->toArray(),
            'sectionClasses' => Section::pluck('class_id', 'section_id')->toArray(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new HostelAllocation.
     *
     * Preselects a room/student when arriving from the room list ("Quick
     * Allocate") or an existing allocation.
     */
    public function create(Request $request)
    {
        $preselected = array_filter([
            'room_id' => $request->integer('room_id') ?: null,
            'hostel_id' => $request->integer('hostel_id') ?: null,
            'student_id' => $request->integer('student_id') ?: null,
        ]);

        $data = $this->getDropdownData();

        // A room picked from the room list implies its hostel.
        if (!isset($preselected['hostel_id']) && isset($preselected['room_id'])) {
            $preselected['hostel_id'] = $data['roomHostels'][$preselected['room_id']] ?? null;
        }

        return view('hostel_allocations.create', array_merge($data, [
            'preselected' => $preselected,
            'allocation' => null,
        ]));
    }

    /**
     * Store a newly created HostelAllocation in storage.
     */
    public function store(CreateHostelAllocationRequest $request)
    {
        try {
            $hostelAllocation = $this->allocationService->allocate($request->validated());
        } catch (HostelAllocationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back()->withInput();
        }

        AuditTrail::log('Hostel Allocation', 'CREATE', $hostelAllocation->allocation_id, null, $hostelAllocation->toArray());

        Flash::success(sprintf(
            '%s has been allocated bed %s in room %s.',
            $hostelAllocation->student?->full_name ?? 'Student',
            $hostelAllocation->bed_number ?? '?',
            $hostelAllocation->room?->room_number ?? '?'
        ));

        return redirect(route('hostel-allocations.index'));
    }

    /**
     * Display the specified HostelAllocation.
     */
    public function show($id)
    {
        $hostelAllocation = HostelAllocation::withDisplayContext()->find($id);

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
        $hostelAllocation = HostelAllocation::withDisplayContext()->find($id);

        if (empty($hostelAllocation)) {
            Flash::error('Hostel Allocation not found');
            return redirect(route('hostel-allocations.index'));
        }

        // The current room is always offered, so an allocation in a room that
        // is now full or under maintenance can still be edited and released.
        return view('hostel_allocations.edit')
            ->with($this->getDropdownData((int) $hostelAllocation->room_id))
            ->with('hostelAllocation', $hostelAllocation)
            ->with('allocation', $hostelAllocation)
            ->with('preselected', [
                'room_id' => $hostelAllocation->room_id,
                'hostel_id' => $hostelAllocation->hostel_id,
                'student_id' => $hostelAllocation->student_id,
            ]);
    }

    /**
     * Update the specified HostelAllocation in storage.
     */
    public function update($id, UpdateHostelAllocationRequest $request)
    {
        $hostelAllocation = HostelAllocation::find($id);

        if (empty($hostelAllocation)) {
            Flash::error('Hostel Allocation not found');
            return redirect(route('hostel-allocations.index'));
        }

        $oldData = $hostelAllocation->toArray();

        try {
            $hostelAllocation = $this->allocationService->updateAllocation($hostelAllocation, $request->validated());
        } catch (HostelAllocationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back()->withInput();
        }

        AuditTrail::log('Hostel Allocation', 'UPDATE', $hostelAllocation->allocation_id, $oldData, $hostelAllocation->toArray());

        Flash::success('Hostel Allocation updated successfully.');

        return redirect(route('hostel-allocations.index'));
    }

    /**
     * Remove the specified HostelAllocation from storage.
     */
    public function destroy($id)
    {
        $hostelAllocation = HostelAllocation::find($id);

        if (empty($hostelAllocation)) {
            Flash::error('Hostel Allocation not found');
            return redirect(route('hostel-allocations.index'));
        }

        // Frees the bed and re-syncs the room inside the same transaction.
        $this->allocationService->deleteAllocation($hostelAllocation);

        Flash::success('Hostel Allocation deleted successfully.');

        return redirect(route('hostel-allocations.index'));
    }

    /**
     * Checkout a student
     */
    public function checkout(Request $request, $id)
    {
        $request->validate([
            'checkout_notes' => 'nullable|string|max:1000',
        ]);

        $hostelAllocation = HostelAllocation::find($id);

        if (!$hostelAllocation) {
            Flash::error('Hostel Allocation not found');
            return redirect(route('hostel-allocations.index'));
        }

        try {
            $this->allocationService->checkout($hostelAllocation, $request->input('checkout_notes'));
        } catch (HostelAllocationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        AuditTrail::log('Hostel Allocation', 'CHECKOUT', $hostelAllocation->allocation_id, ['status' => 'active'], $hostelAllocation->refresh()->toArray());

        Flash::success(sprintf(
            '%s has been checked out. The bed is now free.',
            $hostelAllocation->student?->full_name ?? 'Student'
        ));

        return redirect()->back();
    }

    /**
     * Bulk allocation form
     */
    public function bulkForm()
    {
        $data = $this->getDropdownData();
        $data['allocation'] = null;

        return view('hostel_allocations.bulk')->with($data);
    }

    /**
     * Bulk allocation store
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:students,student_id',
            'hostel_id' => 'required|exists:hostels,hostel_id',
            'room_id' => 'required|exists:hostel_rooms,room_id',
            'allocation_date' => 'required|date',
            'academic_year_id' => 'nullable|exists:academic_years,academic_year_id',
        ]);

        try {
            $allocations = $this->allocationService->bulkAllocate($validated['student_ids'], $validated);
        } catch (HostelAllocationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back()->withInput();
        }

        $room = HostelRoom::find($validated['room_id']);

        AuditTrail::log('Hostel Allocation', 'BULK CREATE', $room?->room_id, null, [
            'hostel_id' => $validated['hostel_id'],
            'room_id' => $validated['room_id'],
            'student_ids' => $validated['student_ids'],
            'count' => count($allocations),
        ]);

        Flash::success(sprintf(
            '%d student%s allocated to room %s.',
            count($allocations),
            count($allocations) === 1 ? '' : 's',
            $room?->room_number ?? ''
        ));

        return redirect(route('hostel-allocations.index'));
    }

    /**
     * Transfer form
     */
    public function transferForm($id)
    {
        $hostelAllocation = HostelAllocation::with(['student', 'room', 'hostel'])->find($id);

        if (empty($hostelAllocation)) {
            Flash::error('Hostel Allocation not found');
            return redirect(route('hostel-allocations.index'));
        }

        if ($hostelAllocation->status !== 'active') {
            Flash::error('Only an active allocation can be transferred.');
            return redirect(route('hostel-allocations.index'));
        }

        $data = $this->getDropdownData();
        $data['rooms'] = $this->allocationService->roomOptions((int) $hostelAllocation->room_id);

        return view('hostel_allocations.transfer', array_merge($data, [
            'hostelAllocation' => $hostelAllocation,
        ]));
    }

    /**
     * Transfer store
     */
    public function transferStore(Request $request, $id)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:hostel_rooms,room_id',
            'transfer_reason' => 'nullable|string|max:255',
        ]);

        $hostelAllocation = HostelAllocation::find($id);

        if (!$hostelAllocation) {
            Flash::error('Hostel Allocation not found');
            return redirect(route('hostel-allocations.index'));
        }

        $oldRoomNumber = $hostelAllocation->room?->room_number ?? '?';

        try {
            $newAllocation = $this->allocationService->transfer($hostelAllocation, $validated);
        } catch (HostelAllocationException $e) {
            Flash::error($e->getMessage());
            return redirect()->back()->withInput();
        }

        AuditTrail::log('Hostel Allocation', 'TRANSFER', $hostelAllocation->allocation_id, [
            'room_id' => $hostelAllocation->room_id,
            'room_number' => $oldRoomNumber,
        ], [
            'student_id' => $newAllocation->student_id,
            'new_room_id' => $newAllocation->room_id,
            'new_allocation_id' => $newAllocation->allocation_id,
        ]);

        Flash::success(sprintf(
            'Student transferred from room %s to room %s.',
            $oldRoomNumber,
            $newAllocation->room?->room_number ?? '?'
        ));

        return redirect(route('hostel-allocations.index'));
    }

    /**
     * Export the filtered allocation list to PDF.
     */
    public function export(Request $request)
    {
        $filters = $request->only([
            'hostel_id', 'room_id', 'status', 'academic_year_id', 'class_id', 'section_id', 'search',
        ]);

        $allocations = HostelAllocation::filter($filters)
            ->orderBy('hostel_id')
            ->orderByDesc('allocation_date')
            ->get();

        $pdf = Pdf::loadView('hostel_allocations.exports.pdf', [
            'allocations' => $allocations,
            'scopeLabel' => $this->scopeLabel($filters),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('hostel-allocations-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Human readable description of the filters applied to a list/export.
     */
    private function scopeLabel(array $filters): string
    {
        $parts = [];

        if (!empty($filters['hostel_id'])) {
            $parts[] = Hostel::where('hostel_id', $filters['hostel_id'])->value('name') ?? 'Hostel #' . $filters['hostel_id'];
        }

        if (!empty($filters['room_id'])) {
            $parts[] = 'Room ' . (HostelRoom::where('room_id', $filters['room_id'])->value('room_number') ?? $filters['room_id']);
        }

        if (!empty($filters['status'])) {
            $parts[] = ucfirst($filters['status']) . ' allocations';
        }

        if (!empty($filters['academic_year_id'])) {
            $parts[] = AcademicYear::where('academic_year_id', $filters['academic_year_id'])->value('name');
        }

        if (!empty($filters['class_id'])) {
            $parts[] = \App\Models\SchoolClass::where('class_id', $filters['class_id'])->value('name');
        }

        if (!empty($filters['section_id'])) {
            $parts[] = \App\Models\Section::where('section_id', $filters['section_id'])->value('name');
        }

        if (!empty($filters['search'])) {
            $parts[] = 'search "' . $filters['search'] . '"';
        }

        return $parts ? implode(' · ', array_filter($parts)) : 'All allocations';
    }
}
