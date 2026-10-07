<?php

namespace App\Services;

use App\Exceptions\HostelAllocationException;
use App\Models\AuditTrail;
use App\Models\Hostel;
use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for hostel bed occupancy.
 *
 * Every allocation write (single, bulk, transfer, checkout, delete) goes through
 * this service so that:
 *  - multi-step writes run inside one transaction (no half-applied transfers),
 *  - rooms are locked before the capacity test (no over-allocation on races),
 *  - `hostel_rooms.occupied` is recalculated from the active allocation rows
 *    instead of being incremented/decremented by one (no drift),
 *  - `hostel_rooms.status` is derived from occupancy and only ever uses the
 *    three values the enum allows: available | full | under_maintenance,
 *  - the room actually belongs to the chosen hostel and the student's gender
 *    matches the hostel type.
 */
class HostelAllocationService
{
    public const STATUS_AVAILABLE = HostelRoom::STATUS_AVAILABLE;
    public const STATUS_FULL = HostelRoom::STATUS_FULL;
    public const STATUS_UNDER_MAINTENANCE = HostelRoom::STATUS_UNDER_MAINTENANCE;

    /** The only values hostel_rooms.status accepts. */
    public const ROOM_STATUSES = HostelRoom::STATUSES;

    /**
     * Derive a room status from its occupancy.
     *
     * A room under maintenance keeps that state regardless of its beds;
     * otherwise it is full once every bed is taken and available while beds
     * remain. There is deliberately no "partial" value: a room with free beds is
     * available, and the bed counts carry the "partially occupied" detail.
     */
    public function deriveRoomStatus(int $occupied, int $capacity, ?string $currentStatus = null): string
    {
        if ($currentStatus === self::STATUS_UNDER_MAINTENANCE) {
            return self::STATUS_UNDER_MAINTENANCE;
        }

        return $capacity > 0 && $occupied >= $capacity
            ? self::STATUS_FULL
            : self::STATUS_AVAILABLE;
    }

    /**
     * Rooms that can still take a student (not in maintenance, free beds).
     */
    public function allocatableRoomsQuery(): Builder
    {
        return HostelRoom::with('hostel')
            ->where('status', '!=', self::STATUS_UNDER_MAINTENANCE)
            ->whereColumn('occupied', '<', 'capacity')
            ->orderBy('hostel_id')
            ->orderBy('room_number');
    }

    /**
     * Dropdown options for rooms, labelled with the beds still free.
     *
     * @param  int|null  $excludeRoomId  rooms never offered (e.g. the source
     *                                   room of a transfer)
     * @param  int|null  $includeRoomId  room that must stay listed even when it
     *                                   is not allocatable, so editing an
     *                                   allocation in a full or under-maintenance
     *                                   room does not silently drop it
     * @return array<int,string>
     */
    public function roomOptions(?int $excludeRoomId = null, ?int $includeRoomId = null): array
    {
        $query = $this->allocatableRoomsQuery();

        if ($excludeRoomId !== null) {
            $query->where('room_id', '!=', $excludeRoomId);
        }

        $rooms = $query->get();

        if ($includeRoomId !== null) {
            $room = HostelRoom::with('hostel')->find($includeRoomId);

            if ($room && !$rooms->contains('room_id', $room->room_id)) {
                $rooms->push($room);
                $rooms = $rooms->sortBy([['hostel_id', 'asc'], ['room_number', 'asc']])->values();
            }
        }

        return $rooms
            ->mapWithKeys(function (HostelRoom $room) {
                $hostelName = $room->hostel->name ?? 'N/A';
                $free = $room->getAvailableBeds();

                $note = $room->status === self::STATUS_UNDER_MAINTENANCE
                    ? 'under maintenance'
                    : sprintf('%d bed%s left', $free, $free === 1 ? '' : 's');

                return [$room->room_id => sprintf(
                    '%s - %s (%s) - %s',
                    $hostelName,
                    $room->room_number,
                    ucfirst((string) $room->room_type),
                    $note
                )];
            })
            ->all();
    }

    /**
     * Recalculate a room's occupancy and status from its active allocations.
     *
     * Call inside the same transaction that changed the allocations; pass a
     * locked instance when the room is being allocated to.
     */
    public function syncRoom(HostelRoom $room): HostelRoom
    {
        $occupied = HostelAllocation::where('room_id', $room->room_id)
            ->where('status', 'active')
            ->count();

        $status = $this->deriveRoomStatus($occupied, (int) $room->capacity, $room->status);

        if ((int) $room->occupied !== $occupied || $room->status !== $status) {
            $room->forceFill(['occupied' => $occupied, 'status' => $status])->save();
        }

        return $room;
    }

    /**
     * Allocate one bed to a student.
     *
     * @param array{student_id:int,room_id:int,hostel_id?:int,allocation_date?:string,academic_year_id?:int|null,status?:string} $data
     */
    public function allocate(array $data): HostelAllocation
    {
        return DB::transaction(function () use ($data) {
            $student = $this->lockStudent((int) $data['student_id']);
            $room = $this->lockRoom((int) $data['room_id']);
            $hostel = $this->lockHostel((int) $room->hostel_id);

            $this->assertRoomAssignable($room, isset($data['hostel_id']) ? (int) $data['hostel_id'] : null);
            $this->assertGenderAllowed($student, $hostel);
            $this->assertNoActiveAllocation($student->student_id);

            $status = $data['status'] ?? 'active';

            $allocation = HostelAllocation::create([
                'student_id' => $student->student_id,
                'hostel_id' => $hostel->hostel_id,
                'room_id' => $room->room_id,
                // Only an active allocation holds a bed. A pending one has not
                // moved in yet, so giving it a number would imply a reservation
                // the occupancy counts do not honour.
                'bed_number' => $status === 'active'
                    ? $this->resolveBedNumber($room, $data['bed_number'] ?? null)
                    : null,
                'allocation_date' => $data['allocation_date'] ?? now()->toDateString(),
                'academic_year_id' => $data['academic_year_id'] ?? null,
                'status' => $status,
            ]);

            $this->syncRoom($room);

            return $allocation;
        });
    }

    /**
     * Allocate several students to the same room, all or nothing.
     *
     * @param  array<int>  $studentIds
     * @param  array{room_id:int,hostel_id?:int,allocation_date?:string,academic_year_id?:int|null}  $data
     * @return array<int,\App\Models\HostelAllocation>
     */
    public function bulkAllocate(array $studentIds, array $data): array
    {
        $studentIds = array_values(array_unique(array_map('intval', $studentIds)));

        if ($studentIds === []) {
            throw new HostelAllocationException('Select at least one student to allocate.');
        }

        return DB::transaction(function () use ($studentIds, $data) {
            // Lock the students (ascending, so concurrent batches queue instead
            // of deadlocking) and the room in the same order as a single
            // allocation: students first, then room, then hostel.
            sort($studentIds);

            $students = $this->lockStudents($studentIds);

            $room = $this->lockRoom((int) $data['room_id']);
            $hostel = $this->lockHostel((int) $room->hostel_id);

            $this->assertRoomAssignable($room, isset($data['hostel_id']) ? (int) $data['hostel_id'] : null);

            $freeBeds = $room->getAvailableBeds();
            if ($freeBeds < count($studentIds)) {
                throw new HostelAllocationException(sprintf(
                    'Room %s only has %d bed%s free but %d student%s were selected.',
                    $room->room_number,
                    $freeBeds,
                    $freeBeds === 1 ? '' : 's',
                    count($studentIds),
                    count($studentIds) === 1 ? ' was' : 's were'
                ));
            }

            foreach ($studentIds as $studentId) {
                $this->assertGenderAllowed($students[$studentId], $hostel);
            }

            $conflicts = $this->studentsWithActiveAllocations($studentIds);
            if ($conflicts !== '') {
                throw new HostelAllocationException(
                    'These students already have an active bed: ' . $conflicts . '. Remove them from the selection.'
                );
            }

            $allocations = [];
            foreach ($studentIds as $index => $studentId) {
                $allocations[] = HostelAllocation::create([
                    'student_id' => $studentId,
                    'hostel_id' => $hostel->hostel_id,
                    'room_id' => $room->room_id,
                    // A single requested bed only makes sense for the first
                    // student; the rest take the next free beds.
                    'bed_number' => $this->resolveBedNumber($room, $index === 0 ? ($data['bed_number'] ?? null) : null),
                    'allocation_date' => $data['allocation_date'] ?? now()->toDateString(),
                    'academic_year_id' => $data['academic_year_id'] ?? null,
                    'status' => 'active',
                ]);
            }

            $this->syncRoom($room);

            return $allocations;
        });
    }

    /**
     * Move an active allocation to another room.
     *
     * The old bed is released and the new bed taken in one transaction, so a
     * failure can never leave a student with two beds or none.
     *
     * @param  array{room_id:int,transfer_reason?:string|null}  $data
     */
    public function transfer(HostelAllocation $allocation, array $data): HostelAllocation
    {
        return DB::transaction(function () use ($allocation, $data) {
            $allocation = HostelAllocation::where('allocation_id', $allocation->allocation_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($allocation->status !== 'active') {
                throw new HostelAllocationException('Only an active allocation can be transferred.');
            }

            $oldRoomId = (int) $allocation->room_id;
            $newRoomId = (int) $data['room_id'];

            if ($oldRoomId === $newRoomId) {
                throw new HostelAllocationException('The student is already allocated to that room.');
            }

            $student = $this->lockStudent((int) $allocation->student_id);

            // Lock both rooms in ascending id order so two simultaneous
            // transfers can never deadlock against each other.
            $rooms = [];
            foreach ([$oldRoomId, $newRoomId] as $roomId) {
                if (!isset($rooms[$roomId])) {
                    $rooms[$roomId] = $this->lockRoom($roomId);
                }
            }
            ksort($rooms);

            $oldRoom = $rooms[$oldRoomId];
            $newRoom = $rooms[$newRoomId];

            $this->assertRoomAssignable($newRoom, null);
            // A transfer can move a student into a different hostel, so the
            // gender rule has to be checked against the *new* hostel too.
            $this->assertGenderAllowed($student, $this->lockHostel((int) $newRoom->hostel_id));

            $reason = trim((string) ($data['transfer_reason'] ?? ''));

            $allocation->update([
                'status' => 'vacated',
                'vacating_date' => now()->toDateString(),
                'checkout_notes' => $reason !== ''
                    ? 'Transferred to room ' . $newRoom->room_number . ': ' . $reason
                    : 'Transferred to room ' . $newRoom->room_number,
            ]);

            $newAllocation = HostelAllocation::create([
                'student_id' => $allocation->student_id,
                'hostel_id' => $newRoom->hostel_id,
                'room_id' => $newRoom->room_id,
                'bed_number' => $this->nextFreeBedNumber($newRoom),
                'allocation_date' => now()->toDateString(),
                'academic_year_id' => $allocation->academic_year_id,
                'status' => 'active',
            ]);

            $this->syncRoom($oldRoom);
            $this->syncRoom($newRoom);

            return $newAllocation;
        });
    }

    /**
     * Check a student out of their bed, freeing it for someone else.
     */
    public function checkout(HostelAllocation $allocation, ?string $notes = null, ?string $vacatingDate = null): HostelAllocation
    {
        return DB::transaction(function () use ($allocation, $notes, $vacatingDate) {
            $allocation = HostelAllocation::where('allocation_id', $allocation->allocation_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($allocation->status !== 'active') {
                throw new HostelAllocationException('This student has already been checked out.');
            }

            $room = $this->lockRoom((int) $allocation->room_id);

            $allocation->update([
                'status' => 'vacated',
                'vacating_date' => $vacatingDate ?: now()->toDateString(),
                'checkout_notes' => $notes !== null && trim($notes) !== '' ? $notes : $allocation->checkout_notes,
            ]);

            $this->syncRoom($room);

            return $allocation;
        });
    }

    /**
     * Update an allocation that already exists.
     *
     * Re-validates the room and gender, and re-syncs both the old and the new
     * room so occupancy never drifts when an allocation is edited.
     */
    public function updateAllocation(HostelAllocation $allocation, array $data): HostelAllocation
    {
        return DB::transaction(function () use ($allocation, $data) {
            $allocation = HostelAllocation::where('allocation_id', $allocation->allocation_id)
                ->lockForUpdate()
                ->firstOrFail();

            $oldRoomId = (int) $allocation->room_id;
            $newRoomId = (int) ($data['room_id'] ?? $oldRoomId);
            $wasActive = $allocation->status === 'active';
            $willBeActive = ($data['status'] ?? $allocation->status) === 'active';

            $student = $this->lockStudent((int) $allocation->student_id);

            // Lock rooms in a stable order to avoid deadlocks.
            $roomIds = array_values(array_unique([$oldRoomId, $newRoomId]));
            sort($roomIds);
            $rooms = [];
            foreach ($roomIds as $roomId) {
                $rooms[$roomId] = $this->lockRoom($roomId);
            }

            $newRoom = $rooms[$newRoomId];

            if ($willBeActive) {
                // When the bed stays in the same room, the allocation's own
                // occupancy is excluded from the capacity test: keeping a
                // student where they are must not look like over-filling a
                // room that is now full *because of them*.
                $alreadyCounted = $newRoomId === $oldRoomId && $wasActive ? 1 : 0;

                if ($newRoom->getAvailableBeds() + $alreadyCounted < 1) {
                    throw new HostelAllocationException(sprintf(
                        'Room %s is full.',
                        $newRoom->room_number
                    ));
                }

                $this->assertRoomAssignable(
                    $newRoom,
                    isset($data['hostel_id']) ? (int) $data['hostel_id'] : null,
                    $alreadyCounted
                );
                $this->assertGenderAllowed($student, $this->lockHostel((int) $newRoom->hostel_id));
                $this->assertNoActiveAllocation($student->student_id, $allocation->allocation_id);
            }

            $attributes = [
                'room_id' => $newRoomId,
                'hostel_id' => $newRoom->hostel_id,
                'status' => $data['status'] ?? $allocation->status,
            ];

            foreach (['allocation_date', 'vacating_date', 'academic_year_id', 'checkout_notes'] as $field) {
                if (array_key_exists($field, $data)) {
                    $attributes[$field] = $data[$field];
                }
            }

            // The bed is re-derived whenever it is active, so a manual value
            // can never point at a bed someone else is already in. Moving away
            // from "active" releases the bed rather than leaving a stale number
            // that the occupancy count no longer honours.
            $attributes['bed_number'] = $willBeActive
                ? $this->resolveBedNumber(
                    $newRoom,
                    $data['bed_number'] ?? $allocation->bed_number,
                    $allocation->allocation_id
                )
                : null;

            $allocation->update($attributes);

            foreach ($rooms as $room) {
                $this->syncRoom($room);
            }

            if ($wasActive && !$willBeActive) {
                $this->syncRoom($rooms[$oldRoomId]);
            }

            return $allocation->refresh();
        });
    }

    /**
     * Delete an allocation record, releasing its bed first if it was active.
     */
    public function deleteAllocation(HostelAllocation $allocation): void
    {
        DB::transaction(function () use ($allocation) {
            $allocation = HostelAllocation::where('allocation_id', $allocation->allocation_id)
                ->lockForUpdate()
                ->firstOrFail();

            $roomId = (int) $allocation->room_id;
            $snapshot = $allocation->toArray();

            $allocation->delete();

            $this->syncRoom($this->lockRoom($roomId));

            AuditTrail::log('Hostel Allocation', 'DELETE', $allocation->allocation_id, $snapshot, null);
        });
    }

    /**
     * The lowest bed number not taken by an active allocation in the room.
     */
    public function nextFreeBedNumber(HostelRoom $room, ?int $exceptAllocationId = null): ?int
    {
        $taken = $this->takenBedNumbers($room, $exceptAllocationId);

        for ($bed = 1; $bed <= (int) $room->capacity; $bed++) {
            if (!in_array($bed, $taken, true)) {
                return $bed;
            }
        }

        return null;
    }

    /**
     * Honour an explicitly requested bed when it is valid and free, otherwise
     * fall back to the next free bed.
     */
    private function resolveBedNumber(HostelRoom $room, $requested, ?int $exceptAllocationId = null): ?int
    {
        if ($requested === null || $requested === '') {
            $next = $this->nextFreeBedNumber($room, $exceptAllocationId);

            // A free bed is guaranteed by assertRoomAssignable(), so reaching
            // null here means the stored occupied/bed data has drifted out of
            // step with the allocation rows. Fail loudly instead of writing a
            // bed-less "active" record that would corrupt the room again.
            if ($next === null) {
                throw new HostelAllocationException(sprintf(
                    'Room %s has beds numbered 1 to %d but every number is taken. Re-save the room to resync it.',
                    $room->room_number,
                    (int) $room->capacity
                ));
            }

            return $next;
        }

        $requested = (int) $requested;

        if ($requested < 1 || $requested > (int) $room->capacity) {
            throw new HostelAllocationException(sprintf(
                'Room %s has beds numbered 1 to %d.',
                $room->room_number,
                (int) $room->capacity
            ));
        }

        if (in_array($requested, $this->takenBedNumbers($room, $exceptAllocationId), true)) {
            throw new HostelAllocationException(sprintf(
                'Bed %d in room %s is already taken.',
                $requested,
                $room->room_number
            ));
        }

        return $requested;
    }

    /**
     * Bed numbers held by active allocations in the room.
     *
     * @return array<int>
     */
    private function takenBedNumbers(HostelRoom $room, ?int $exceptAllocationId = null): array
    {
        $query = HostelAllocation::where('room_id', $room->room_id)
            ->where('status', 'active')
            ->whereNotNull('bed_number');

        if ($exceptAllocationId !== null) {
            $query->where('allocation_id', '!=', $exceptAllocationId);
        }

        return $query->pluck('bed_number')
            ->map(fn ($bed) => (int) $bed)
            ->all();
    }

    private function lockStudent(int $studentId): Student
    {
        $student = Student::where('student_id', $studentId)->lockForUpdate()->first();

        if (!$student) {
            throw new HostelAllocationException('The selected student no longer exists.');
        }

        return $student;
    }

    /**
     * Lock several students at once, keyed by id, in ascending order.
     *
     * @param  array<int>  $studentIds
     * @return \Illuminate\Support\Collection<int,Student>
     */
    private function lockStudents(array $studentIds): Collection
    {
        $students = Student::whereIn('student_id', $studentIds)
            ->orderBy('student_id')
            ->lockForUpdate()
            ->get()
            ->keyBy('student_id');

        if ($students->count() !== count($studentIds)) {
            throw new HostelAllocationException('One or more selected students no longer exist.');
        }

        return $students;
    }

    private function lockRoom(int $roomId): HostelRoom
    {
        $room = HostelRoom::where('room_id', $roomId)->lockForUpdate()->first();

        if (!$room) {
            throw new HostelAllocationException('The selected room no longer exists.');
        }

        return $room;
    }

    private function lockHostel(int $hostelId): Hostel
    {
        $hostel = Hostel::where('hostel_id', $hostelId)->lockForUpdate()->first();

        if (!$hostel) {
            throw new HostelAllocationException('The selected hostel no longer exists.');
        }

        return $hostel;
    }

    /**
     * @param int $occupiedByCaller 1 when the caller already holds a bed in this
     *                              room, so its own occupancy is not a conflict.
     */
    private function assertRoomAssignable(HostelRoom $room, ?int $requestedHostelId, int $occupiedByCaller = 0): void
    {
        if ($room->status === self::STATUS_UNDER_MAINTENANCE) {
            throw new HostelAllocationException(sprintf(
                'Room %s is under maintenance and cannot take new allocations.',
                $room->room_number
            ));
        }

        if ($requestedHostelId !== null && (int) $requestedHostelId !== (int) $room->hostel_id) {
            throw new HostelAllocationException(sprintf(
                'Room %s does not belong to the selected hostel.',
                $room->room_number
            ));
        }

        if ($room->getAvailableBeds() + $occupiedByCaller < 1) {
            throw new HostelAllocationException(sprintf('Room %s is full.', $room->room_number));
        }
    }

    private function assertGenderAllowed(Student $student, Hostel $hostel): void
    {
        $expected = match ($hostel->type) {
            'boys' => 'male',
            'girls' => 'female',
            default => null,
        };

        if ($expected !== null && strtolower((string) $student->gender) !== $expected) {
            throw new HostelAllocationException(sprintf(
                '%s %s is %s but %s is a %s hostel.',
                $hostel->type === 'boys' ? 'Boys' : 'Girls',
                trim($student->first_name . ' ' . $student->last_name),
                $student->gender ?: 'of unknown gender',
                $hostel->name,
                $hostel->type
            ));
        }
    }

    private function assertNoActiveAllocation(int $studentId, ?int $exceptAllocationId = null): void
    {
        $query = HostelAllocation::where('student_id', $studentId)->where('status', 'active');

        if ($exceptAllocationId !== null) {
            $query->where('allocation_id', '!=', $exceptAllocationId);
        }

        $existing = $query->with('room')->first();

        if ($existing) {
            throw new HostelAllocationException(sprintf(
                'This student already holds bed %s in room %s.',
                $existing->bed_number ?? '?',
                $existing->room->room_number ?? '?'
            ));
        }
    }

    /**
     * @param  array<int>  $studentIds
     * @return string comma separated list of student names
     */
    private function studentsWithActiveAllocations(array $studentIds): string
    {
        return HostelAllocation::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->with('student')
            ->get()
            ->map(fn (HostelAllocation $allocation) => trim(
                ($allocation->student->first_name ?? '') . ' ' . ($allocation->student->last_name ?? '')
            ))
            ->filter()
            ->unique()
            ->implode(', ');
    }
}
