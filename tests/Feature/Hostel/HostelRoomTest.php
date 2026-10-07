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
 * Room CRUD: capacity guards, the under-maintenance toggle, occupancy that is
 * derived rather than typed in, and the delete guard.
 */
class HostelRoomTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Hostel $hostel;

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

        $this->hostel = Hostel::create([
            'name' => 'Simba Boys Dormitory',
            'type' => 'boys',
            'address' => 'Main Campus',
            'capacity' => 60,
        ]);

        $this->year = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-11-20',
            'is_current' => true,
        ]);
    }

    private function room(array $attributes = []): HostelRoom
    {
        return HostelRoom::create(array_merge([
            'hostel_id' => $this->hostel->hostel_id,
            'room_number' => 'B-' . (HostelRoom::count() + 1),
            'room_type' => 'double',
            'capacity' => 2,
            'floor' => 'First',
            'status' => HostelRoom::STATUS_AVAILABLE,
        ], $attributes));
    }

    private function occupy(HostelRoom $room, int $count = 1): void
    {
        for ($i = 0; $i < $count; $i++) {
            $student = Student::factory()->create(['gender' => 'male']);

            $this->actingAs($this->admin)->post(route('hostel-allocations.store'), [
                'student_id' => $student->student_id,
                'hostel_id' => $this->hostel->hostel_id,
                'room_id' => $room->room_id,
                'allocation_date' => '2026-01-10',
                'academic_year_id' => $this->year->academic_year_id,
                'status' => 'active',
            ]);
        }
    }

    public function test_a_new_room_starts_empty_and_available(): void
    {
        $this->actingAs($this->admin)->post(route('hostel-rooms.store'), [
            'hostel_id' => $this->hostel->hostel_id,
            'room_number' => 'C-1',
            'room_type' => 'single',
            'capacity' => 1,
            'floor' => 'Ground',
            'status' => HostelRoom::STATUS_AVAILABLE,
        ])->assertRedirect(route('hostel-rooms.index'));

        $this->assertDatabaseHas('hostel_rooms', [
            'room_number' => 'C-1',
            'occupied' => 0,
            'status' => HostelRoom::STATUS_AVAILABLE,
        ]);
    }

    public function test_a_new_room_cannot_claim_to_be_full(): void
    {
        $this->actingAs($this->admin)->post(route('hostel-rooms.store'), [
            'hostel_id' => $this->hostel->hostel_id,
            'room_number' => 'C-2',
            'room_type' => 'single',
            'capacity' => 4,
            'floor' => 'Ground',
            'status' => HostelRoom::STATUS_FULL,
        ]);

        // "full" follows from the occupancy, which is zero on a new room.
        $this->assertDatabaseHas('hostel_rooms', [
            'room_number' => 'C-2',
            'occupied' => 0,
            'status' => HostelRoom::STATUS_AVAILABLE,
        ]);
    }

    public function test_occupied_cannot_be_typed_in_by_the_user(): void
    {
        $room = $this->room();

        $this->actingAs($this->admin)->put(route('hostel-rooms.update', $room->room_id), [
            'hostel_id' => $this->hostel->hostel_id,
            'room_number' => $room->room_number,
            'room_type' => 'double',
            'capacity' => 4,
            'floor' => 'First',
            'status' => HostelRoom::STATUS_AVAILABLE,
            'occupied' => 3,
        ])->assertRedirect(route('hostel-rooms.index'));

        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $room->room_id,
            'occupied' => 0,
        ]);
    }

    public function test_a_room_can_be_put_under_maintenance_and_reopened(): void
    {
        $room = $this->room();

        $this->actingAs($this->admin)->put(route('hostel-rooms.update', $room->room_id), [
            'hostel_id' => $this->hostel->hostel_id,
            'room_number' => $room->room_number,
            'room_type' => 'double',
            'capacity' => 2,
            'floor' => 'First',
            'status' => HostelRoom::STATUS_UNDER_MAINTENANCE,
        ])->assertRedirect(route('hostel-rooms.index'));

        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $room->room_id,
            'status' => HostelRoom::STATUS_UNDER_MAINTENANCE,
        ]);

        // Maintenance is reversible: choosing "available" must clear it.
        $this->actingAs($this->admin)->put(route('hostel-rooms.update', $room->room_id), [
            'hostel_id' => $this->hostel->hostel_id,
            'room_number' => $room->room_number,
            'room_type' => 'double',
            'capacity' => 2,
            'floor' => 'First',
            'status' => HostelRoom::STATUS_AVAILABLE,
        ])->assertRedirect(route('hostel-rooms.index'));

        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $room->room_id,
            'status' => HostelRoom::STATUS_AVAILABLE,
        ]);
    }

    public function test_capacity_cannot_be_shrunk_below_the_students_already_in_the_room(): void
    {
        $room = $this->room(['capacity' => 3]);
        $this->occupy($room, 2);

        $this->assertDatabaseHas('hostel_rooms', ['room_id' => $room->room_id, 'occupied' => 2]);

        $this->actingAs($this->admin)
            ->from(route('hostel-rooms.edit', $room->room_id))
            ->put(route('hostel-rooms.update', $room->room_id), [
                'hostel_id' => $this->hostel->hostel_id,
                'room_number' => $room->room_number,
                'room_type' => 'double',
                'capacity' => 1,
                'floor' => 'First',
                'status' => HostelRoom::STATUS_AVAILABLE,
            ])
            ->assertRedirect(route('hostel-rooms.edit', $room->room_id));

        // The rejected write left the room exactly as it was.
        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $room->room_id,
            'capacity' => 3,
            'occupied' => 2,
        ]);
    }

    public function test_growing_a_room_below_full_marks_it_full_again(): void
    {
        $room = $this->room(['capacity' => 4]);
        $this->occupy($room, 3);

        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $room->room_id,
            'occupied' => 3,
            'status' => HostelRoom::STATUS_AVAILABLE,
        ]);

        $this->actingAs($this->admin)->put(route('hostel-rooms.update', $room->room_id), [
            'hostel_id' => $this->hostel->hostel_id,
            'room_number' => $room->room_number,
            'room_type' => 'double',
            'capacity' => 3,
            'floor' => 'First',
            'status' => HostelRoom::STATUS_AVAILABLE,
        ])->assertRedirect(route('hostel-rooms.index'));

        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $room->room_id,
            'occupied' => 3,
            'status' => HostelRoom::STATUS_FULL,
        ]);
    }

    public function test_a_room_holding_students_cannot_be_deleted(): void
    {
        $room = $this->room();
        $this->occupy($room);

        $this->actingAs($this->admin)
            ->from(route('hostel-rooms.index'))
            ->delete(route('hostel-rooms.destroy', $room->room_id))
            ->assertRedirect(route('hostel-rooms.index'));

        $this->assertDatabaseHas('hostel_rooms', ['room_id' => $room->room_id]);
    }

    public function test_an_empty_room_can_be_deleted(): void
    {
        $room = $this->room();

        $this->actingAs($this->admin)
            ->delete(route('hostel-rooms.destroy', $room->room_id))
            ->assertRedirect(route('hostel-rooms.index'));

        $this->assertDatabaseMissing('hostel_rooms', ['room_id' => $room->room_id]);
    }

    public function test_the_room_list_can_be_filtered(): void
    {
        $full = $this->room(['capacity' => 1, 'room_number' => 'FULL-1']);
        $this->occupy($full);

        $this->room(['room_number' => 'FREE-1']);

        $this->actingAs($this->admin)
            ->get(route('hostel-rooms.index', ['status' => HostelRoom::STATUS_FULL]))
            ->assertStatus(200)
            ->assertSee('FULL-1')
            ->assertDontSee('FREE-1');

        $this->actingAs($this->admin)
            ->get(route('hostel-rooms.index', ['status' => HostelRoom::STATUS_AVAILABLE]))
            ->assertStatus(200)
            ->assertSee('FREE-1')
            ->assertDontSee('FULL-1');
    }

    public function test_the_invalid_partial_status_is_rejected_outright(): void
    {
        $room = $this->room();

        $this->actingAs($this->admin)
            ->from(route('hostel-rooms.edit', $room->room_id))
            ->put(route('hostel-rooms.update', $room->room_id), [
                'hostel_id' => $this->hostel->hostel_id,
                'room_number' => $room->room_number,
                'room_type' => 'double',
                'capacity' => 2,
                'floor' => 'First',
                'status' => 'partial',
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $room->room_id,
            'status' => HostelRoom::STATUS_AVAILABLE,
        ]);
    }

    public function test_the_model_rejects_partial_status(): void
    {
        $room = $this->room();

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $room->forceFill(['status' => 'partial'])->save();
    }

    /**
     * The original production failure: a write of "partial" reached MySQL and
     * came back as "Data truncated for column 'status'". The model has to stop
     * it before the database ever sees the value, whatever the write path.
     */
    public function test_no_write_path_can_store_a_status_outside_the_enum(): void
    {
        $room = $this->room();

        try {
            $room->forceFill(['status' => 'partial'])->save();
            $this->fail('forceFill of an invalid status should not be storable.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }

        $this->assertDatabaseHas('hostel_rooms', [
            'room_id' => $room->room_id,
            'status' => HostelRoom::STATUS_AVAILABLE,
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        HostelRoom::create([
            'hostel_id' => $this->hostel->hostel_id,
            'room_number' => 'C-99',
            'room_type' => 'single',
            'capacity' => 1,
            'status' => 'partial',
        ]);
    }
}
