<?php

namespace Tests\Feature\Hostel;

use App\Models\AcademicYear;
use App\Models\Hostel;
use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Bed allocation behaviour: occupancy counters, room status, capacity limits,
 * bed numbering and the atomicity of transfer/bulk/checkout.
 */
class HostelAllocationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Hostel $boysHostel;

    private Hostel $girlsHostel;

    private AcademicYear $year;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->boysHostel = Hostel::create([
            'name' => 'Simba Boys Dormitory',
            'type' => 'boys',
            'address' => 'Main Campus',
            'capacity' => 120,
        ]);

        $this->girlsHostel = Hostel::create([
            'name' => 'Chui Girls Hostel',
            'type' => 'girls',
            'address' => 'East Wing',
            'capacity' => 40,
        ]);

        $this->year = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-11-20',
            'is_current' => true,
        ]);
    }

    private function room(Hostel $hostel, array $attributes = []): HostelRoom
    {
        return HostelRoom::create(array_merge([
            'hostel_id' => $hostel->hostel_id,
            'room_number' => 'A-' . HostelRoom::count() + 1,
            'room_type' => 'double',
            'capacity' => 2,
            'floor' => 'Ground',
            'status' => HostelRoom::STATUS_AVAILABLE,
        ], $attributes));
    }

    private function student(string $gender = 'male'): Student
    {
        return Student::factory()->create(['gender' => $gender]);
    }

    private function allocationPayload(array $overrides = []): array
    {
        $room = $this->room($this->boysHostel);
        $student = $this->student('male');

        return array_merge([
            'student_id' => $student->student_id,
            'hostel_id' => $this->boysHostel->hostel_id,
            'room_id' => $room->room_id,
            'allocation_date' => '2026-01-10',
            'academic_year_id' => $this->year->academic_year_id,
            'status' => 'active',
        ], $overrides);
    }

    // ---------------------------------------------------------------- routing

    public function test_bulk_form_route_is_not_swallowed_by_the_resource_show_route(): void
    {
        $response = $this->actingAs($this->admin)->get('/hostel-allocations/bulk');

        $response->assertStatus(200);
        $response->assertSee('Bulk Bed Allocation');
    }

    public function test_bulk_form_is_ordered_before_the_resource_route(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn ($route) => $route->uri() === 'hostel-allocations/bulk' && in_array('GET', $route->methods(), true));

        $this->assertCount(1, $routes);
        $this->assertSame('hostel-allocations.bulk-form', $routes->first()->getName());
    }

    // ------------------------------------------------------------ single bed

    public function test_allocating_a_bed_increments_occupancy_and_keeps_status_available(): void
    {
        $room = $this->room($this->boysHostel, ['capacity' => 3]);
        $student = $this->student('male');

        $this->actingAs($this->admin)->post(route('hostel-allocations.store'), [
            'student_id' => $student->student_id,
            'hostel_id' => $this->boysHostel->hostel_id,
            'room_id' => $room->room_id,
            'allocation_date' => '2026-01-10',
            'academic_year_id' => $this->year->academic_year_id,
            'status' => 'active',
        ])->assertRedirect(route('hostel-allocations.index'));

        $this->assertDatabaseHas('hostel_allocations', [
            'student_id' => $student->student_id,
            'room_id' => $room->room_id,
            'status' => 'active',
        ]);

        // A room with free beds is "available" — never the invalid "partial".
        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $room->room_id,
            'occupied' => 1,
            'status' => HostelRoom::STATUS_AVAILABLE,
        ]);
    }

    public function test_filling_the_last_bed_marks_the_room_full(): void
    {
        $room = $this->room($this->boysHostel, ['capacity' => 1]);

        $this->actingAs($this->admin)->post(route('hostel-allocations.store'), $this->allocationPayload([
            'room_id' => $room->room_id,
            'bed_number' => null,
        ]));

        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $room->room_id,
            'occupied' => 1,
            'status' => HostelRoom::STATUS_FULL,
        ]);
    }

    public function test_allocating_into_a_full_room_is_rejected_and_changes_nothing(): void
    {
        $room = $this->room($this->boysHostel, ['capacity' => 1]);
        $room->forceFill(['occupied' => 1, 'status' => HostelRoom::STATUS_FULL])->save();

        $student = $this->student('male');

        $this->actingAs($this->admin)
            ->from(route('hostel-allocations.create'))
            ->post(route('hostel-allocations.store'), [
                'student_id' => $student->student_id,
                'hostel_id' => $this->boysHostel->hostel_id,
                'room_id' => $room->room_id,
                'allocation_date' => '2026-01-10',
                'academic_year_id' => $this->year->academic_year_id,
            ])
            ->assertRedirect(route('hostel-allocations.create'));

        $this->assertDatabaseMissing('hostel_allocations', ['student_id' => $student->student_id]);
        $this->assertDatabaseHas('hostel_rooms', ['room_id' => $room->room_id, 'occupied' => 1]);
    }

    public function test_gender_mismatch_with_the_hostel_type_is_rejected(): void
    {
        $room = $this->room($this->boysHostel);
        $girl = $this->student('female');

        $this->actingAs($this->admin)
            ->from(route('hostel-allocations.create'))
            ->post(route('hostel-allocations.store'), [
                'student_id' => $girl->student_id,
                'hostel_id' => $this->boysHostel->hostel_id,
                'room_id' => $room->room_id,
                'allocation_date' => '2026-01-10',
                'academic_year_id' => $this->year->academic_year_id,
            ])
            ->assertRedirect(route('hostel-allocations.create'));

        $this->assertDatabaseMissing('hostel_allocations', ['student_id' => $girl->student_id]);
    }

    public function test_a_room_cannot_be_paired_with_another_hostel(): void
    {
        $room = $this->room($this->boysHostel);

        $this->actingAs($this->admin)
            ->from(route('hostel-allocations.create'))
            ->post(route('hostel-allocations.store'), [
                'student_id' => $this->student('male')->student_id,
                'hostel_id' => $this->girlsHostel->hostel_id,
                'room_id' => $room->room_id,
                'allocation_date' => '2026-01-10',
                'academic_year_id' => $this->year->academic_year_id,
            ])
            ->assertRedirect(route('hostel-allocations.create'));

        $this->assertDatabaseCount('hostel_allocations', 0);
    }

    public function test_a_student_cannot_hold_two_active_beds(): void
    {
        $student = $this->student('male');
        $firstRoom = $this->room($this->boysHostel);

        $this->actingAs($this->admin)->post(route('hostel-allocations.store'), $this->allocationPayload([
            'student_id' => $student->student_id,
            'room_id' => $firstRoom->room_id,
        ]))->assertRedirect(route('hostel-allocations.index'));

        $secondRoom = $this->room($this->boysHostel);

        $this->actingAs($this->admin)
            ->from(route('hostel-allocations.create'))
            ->post(route('hostel-allocations.store'), $this->allocationPayload([
                'student_id' => $student->student_id,
                'room_id' => $secondRoom->room_id,
            ]))
            ->assertRedirect(route('hostel-allocations.create'));

        $this->assertDatabaseCount('hostel_allocations', 1);
    }

    public function test_bed_numbers_are_assigned_sequentially_and_never_collide(): void
    {
        $room = $this->room($this->boysHostel, ['capacity' => 3]);
        $beds = [];

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($this->admin)->post(route('hostel-allocations.store'), $this->allocationPayload([
                'room_id' => $room->room_id,
                'bed_number' => null,
            ]));
        }

        $beds = HostelAllocation::where('room_id', $room->room_id)
            ->where('status', 'active')
            ->orderBy('bed_number')
            ->pluck('bed_number')
            ->all();

        $this->assertSame([1, 2, 3], $beds);
        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $room->room_id,
            'occupied' => 3,
            'status' => HostelRoom::STATUS_FULL,
        ]);
    }

    public function test_a_requested_bed_number_is_honoured_when_free(): void
    {
        $room = $this->room($this->boysHostel, ['capacity' => 4]);

        $this->actingAs($this->admin)->post(route('hostel-allocations.store'), $this->allocationPayload([
            'room_id' => $room->room_id,
            'bed_number' => 4,
        ]));

        $this->assertDatabaseHas('hostel_allocations', [
            'room_id' => $room->room_id,
            'bed_number' => 4,
            'status' => 'active',
        ]);
    }

    public function test_an_out_of_range_bed_number_is_rejected(): void
    {
        $room = $this->room($this->boysHostel, ['capacity' => 2]);

        $this->actingAs($this->admin)
            ->from(route('hostel-allocations.create'))
            ->post(route('hostel-allocations.store'), $this->allocationPayload([
                'room_id' => $room->room_id,
                'bed_number' => 9,
            ]))
            ->assertRedirect(route('hostel-allocations.create'));

        $this->assertDatabaseCount('hostel_allocations', 0);
    }

    // ------------------------------------------------------------------ bulk

    public function test_bulk_allocation_fills_a_room_in_one_go(): void
    {
        $room = $this->room($this->boysHostel, ['capacity' => 3]);
        $students = Student::factory()->count(3)->create(['gender' => 'male']);

        $this->actingAs($this->admin)->post(route('hostel-allocations.bulk-store'), [
            'student_ids' => $students->pluck('student_id')->all(),
            'hostel_id' => $this->boysHostel->hostel_id,
            'room_id' => $room->room_id,
            'allocation_date' => '2026-01-10',
            'academic_year_id' => $this->year->academic_year_id,
        ])->assertRedirect(route('hostel-allocations.index'));

        $this->assertDatabaseCount('hostel_allocations', 3);
        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $room->room_id,
            'occupied' => 3,
            'status' => HostelRoom::STATUS_FULL,
        ]);
    }

    public function test_bulk_allocation_is_all_or_nothing(): void
    {
        $room = $this->room($this->boysHostel, ['capacity' => 2]);
        $students = Student::factory()->count(4)->create(['gender' => 'male']);

        $this->actingAs($this->admin)
            ->from(route('hostel-allocations.bulk-form'))
            ->post(route('hostel-allocations.bulk-store'), [
                'student_ids' => $students->pluck('student_id')->all(),
                'hostel_id' => $this->boysHostel->hostel_id,
                'room_id' => $room->room_id,
                'allocation_date' => '2026-01-10',
            ])
            ->assertRedirect(route('hostel-allocations.bulk-form'));

        // Not one student is placed, and the room is untouched.
        $this->assertDatabaseCount('hostel_allocations', 0);
        $this->assertDatabaseHas('hostel_rooms', ['room_id' => $room->room_id, 'occupied' => 0]);
    }

    public function test_bulk_allocation_rejects_a_student_who_already_has_a_bed(): void
    {
        $room = $this->room($this->boysHostel, ['capacity' => 4]);
        $otherRoom = $this->room($this->boysHostel, ['capacity' => 4]);

        $alreadyPlaced = $this->student('male');
        $this->actingAs($this->admin)->post(route('hostel-allocations.store'), $this->allocationPayload([
            'student_id' => $alreadyPlaced->student_id,
            'room_id' => $otherRoom->room_id,
        ]));

        $fresh = $this->student('male');

        $this->actingAs($this->admin)
            ->from(route('hostel-allocations.bulk-form'))
            ->post(route('hostel-allocations.bulk-store'), [
                'student_ids' => [$alreadyPlaced->student_id, $fresh->student_id],
                'hostel_id' => $this->boysHostel->hostel_id,
                'room_id' => $room->room_id,
                'allocation_date' => '2026-01-10',
            ])
            ->assertRedirect(route('hostel-allocations.bulk-form'));

        $this->assertDatabaseCount('hostel_allocations', 1);
        $this->assertDatabaseHas('hostel_rooms', ['room_id' => $room->room_id, 'occupied' => 0]);
    }

    // -------------------------------------------------------------- transfer

    public function test_transfer_moves_the_bed_and_keeps_both_rooms_consistent(): void
    {
        $oldRoom = $this->room($this->boysHostel, ['capacity' => 2]);
        $newRoom = $this->room($this->boysHostel, ['capacity' => 2]);
        $student = $this->student('male');

        $this->actingAs($this->admin)->post(route('hostel-allocations.store'), $this->allocationPayload([
            'student_id' => $student->student_id,
            'room_id' => $oldRoom->room_id,
        ]));

        $allocation = HostelAllocation::where('student_id', $student->student_id)
            ->where('status', 'active')
            ->firstOrFail();

        $this->actingAs($this->admin)->post(route('hostel-allocations.transfer-store', $allocation->allocation_id), [
            'room_id' => $newRoom->room_id,
            'transfer_reason' => 'Room maintenance',
        ])->assertRedirect(route('hostel-allocations.index'));

        // Old bed released, new bed taken, and no invalid status written.
        $this->assertDatabaseHas('hostel_allocations', [
            'allocation_id' => $allocation->allocation_id,
            'status' => 'vacated',
        ]);
        $this->assertDatabaseHas('hostel_allocations', [
            'student_id' => $student->student_id,
            'room_id' => $newRoom->room_id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $oldRoom->room_id,
            'occupied' => 0,
            'status' => HostelRoom::STATUS_AVAILABLE,
        ]);
        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $newRoom->room_id,
            'occupied' => 1,
            'status' => HostelRoom::STATUS_AVAILABLE,
        ]);
    }

    public function test_transfer_into_a_full_room_changes_nothing(): void
    {
        $oldRoom = $this->room($this->boysHostel, ['capacity' => 2]);
        $fullRoom = $this->room($this->boysHostel, ['capacity' => 1]);
        $student = $this->student('male');

        $this->actingAs($this->admin)->post(route('hostel-allocations.store'), $this->allocationPayload([
            'student_id' => $student->student_id,
            'room_id' => $oldRoom->room_id,
        ]));

        $occupant = $this->student('male');
        $this->actingAs($this->admin)->post(route('hostel-allocations.store'), $this->allocationPayload([
            'student_id' => $occupant->student_id,
            'room_id' => $fullRoom->room_id,
        ]));

        $allocation = HostelAllocation::where('student_id', $student->student_id)
            ->where('status', 'active')
            ->firstOrFail();

        $this->actingAs($this->admin)
            ->from(route('hostel-allocations.transfer-form', $allocation->allocation_id))
            ->post(route('hostel-allocations.transfer-store', $allocation->allocation_id), [
                'room_id' => $fullRoom->room_id,
            ])
            ->assertRedirect(route('hostel-allocations.transfer-form', $allocation->allocation_id));

        // The student still holds the original bed; nothing was half-applied.
        $this->assertDatabaseHas('hostel_allocations', [
            'allocation_id' => $allocation->allocation_id,
            'room_id' => $oldRoom->room_id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('hostel_rooms', ['room_id' => $oldRoom->room_id, 'occupied' => 1]);
        $this->assertDatabaseHas('hostel_rooms', ['room_id' => $fullRoom->room_id, 'occupied' => 1]);
    }

    public function test_transfer_to_the_same_room_is_rejected(): void
    {
        $room = $this->room($this->boysHostel);
        $student = $this->student('male');

        $this->actingAs($this->admin)->post(route('hostel-allocations.store'), $this->allocationPayload([
            'student_id' => $student->student_id,
            'room_id' => $room->room_id,
        ]));

        $allocation = HostelAllocation::where('student_id', $student->student_id)->firstOrFail();

        $this->actingAs($this->admin)
            ->from(route('hostel-allocations.transfer-form', $allocation->allocation_id))
            ->post(route('hostel-allocations.transfer-store', $allocation->allocation_id), [
                'room_id' => $room->room_id,
            ])
            ->assertRedirect(route('hostel-allocations.transfer-form', $allocation->allocation_id));

        $this->assertDatabaseHas('hostel_allocations', [
            'allocation_id' => $allocation->allocation_id,
            'status' => 'active',
        ]);
    }

    public function test_only_active_allocations_can_be_transferred(): void
    {
        $room = $this->room($this->boysHostel);
        $student = $this->student('male');

        $this->actingAs($this->admin)->post(route('hostel-allocations.store'), $this->allocationPayload([
            'student_id' => $student->student_id,
            'room_id' => $room->room_id,
        ]));

        $allocation = HostelAllocation::where('student_id', $student->student_id)->firstOrFail();

        // An active bed offers the transfer form...
        $this->actingAs($this->admin)
            ->get(route('hostel-allocations.transfer-form', $allocation->allocation_id))
            ->assertStatus(200);

        // ...but a vacated one is turned away.
        $allocation->update(['status' => 'vacated', 'vacating_date' => now()->toDateString()]);

        $this->actingAs($this->admin)
            ->get(route('hostel-allocations.transfer-form', $allocation->allocation_id))
            ->assertRedirect(route('hostel-allocations.index'));
    }

    // ----------------------------------------------------- checkout & delete

    public function test_checkout_frees_the_bed_and_reopens_the_room(): void
    {
        $room = $this->room($this->boysHostel, ['capacity' => 1]);
        $student = $this->student('male');

        $this->actingAs($this->admin)->post(route('hostel-allocations.store'), $this->allocationPayload([
            'student_id' => $student->student_id,
            'room_id' => $room->room_id,
        ]));

        $allocation = HostelAllocation::where('student_id', $student->student_id)->firstOrFail();

        $this->actingAs($this->admin)->post(route('hostel-allocations.checkout', $allocation->allocation_id), [
            'checkout_notes' => 'Keys returned',
        ])->assertRedirect();

        $this->assertDatabaseHas('hostel_allocations', [
            'allocation_id' => $allocation->allocation_id,
            'status' => 'vacated',
            'checkout_notes' => 'Keys returned',
        ]);
        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $room->room_id,
            'occupied' => 0,
            'status' => HostelRoom::STATUS_AVAILABLE,
        ]);
    }

    public function test_checking_out_twice_does_not_double_free_the_bed(): void
    {
        $room = $this->room($this->boysHostel, ['capacity' => 1]);
        $student = $this->student('male');

        $this->actingAs($this->admin)->post(route('hostel-allocations.store'), $this->allocationPayload([
            'student_id' => $student->student_id,
            'room_id' => $room->room_id,
        ]));

        $allocation = HostelAllocation::where('student_id', $student->student_id)->firstOrFail();

        $this->actingAs($this->admin)->post(route('hostel-allocations.checkout', $allocation->allocation_id));
        $this->actingAs($this->admin)
            ->from(route('hostel-allocations.index'))
            ->post(route('hostel-allocations.checkout', $allocation->allocation_id))
            ->assertRedirect(route('hostel-allocations.index'));

        $this->assertDatabaseHas('hostel_rooms', ['room_id' => $room->room_id, 'occupied' => 0]);
    }

    public function test_deleting_an_active_allocation_frees_the_bed(): void
    {
        $room = $this->room($this->boysHostel, ['capacity' => 2]);
        $student = $this->student('male');

        $this->actingAs($this->admin)->post(route('hostel-allocations.store'), $this->allocationPayload([
            'student_id' => $student->student_id,
            'room_id' => $room->room_id,
        ]));

        $allocation = HostelAllocation::where('student_id', $student->student_id)->firstOrFail();

        $this->actingAs($this->admin)
            ->delete(route('hostel-allocations.destroy', $allocation->allocation_id))
            ->assertRedirect(route('hostel-allocations.index'));

        $this->assertDatabaseMissing('hostel_allocations', ['allocation_id' => $allocation->allocation_id]);
        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $room->room_id,
            'occupied' => 0,
            'status' => HostelRoom::STATUS_AVAILABLE,
        ]);
    }

    public function test_under_maintenance_rooms_cannot_be_allocated(): void
    {
        $room = $this->room($this->boysHostel, ['status' => HostelRoom::STATUS_UNDER_MAINTENANCE]);
        $student = $this->student('male');

        $this->actingAs($this->admin)
            ->from(route('hostel-allocations.create'))
            ->post(route('hostel-allocations.store'), $this->allocationPayload([
                'student_id' => $student->student_id,
                'room_id' => $room->room_id,
            ]))
            ->assertRedirect(route('hostel-allocations.create'));

        $this->assertDatabaseCount('hostel_allocations', 0);
    }
}
