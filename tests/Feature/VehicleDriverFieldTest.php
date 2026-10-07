<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The vehicle driver is typed in as free text.
 *
 * The form used to offer a dropdown of staff records with staff_type = 'driver',
 * but that value does not exist in the staff_type list, so the select rendered
 * empty and no driver could ever be recorded.
 */
class VehicleDriverFieldTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'vehicle_number' => 'KDJ 210B',
            'vehicle_type' => 'Bus',
            'model' => 'Quantum',
            'make' => 'Toyota',
            'year' => 2019,
            'seating_capacity' => 33,
            'status' => 'active',
        ], $overrides);
    }

    public function test_the_create_form_offers_a_free_text_driver_field_not_a_dropdown(): void
    {
        $response = $this->actingAs($this->admin)->get(route('vehicles.create'));

        $response->assertStatus(200);
        $response->assertSee('name="driver_name"', false);
        $response->assertSee('Enter driver name');

        // The old staff-backed select is gone.
        $response->assertDontSee('name="driver_id"', false);
    }

    public function test_a_typed_driver_name_is_saved(): void
    {
        $this->actingAs($this->admin)->post(route('vehicles.store'), $this->payload([
            'driver_name' => 'John Otieno',
        ]))->assertRedirect(route('vehicles.index'));

        $vehicle = Vehicle::where('vehicle_number', 'KDJ 210B')->firstOrFail();

        $this->assertSame('John Otieno', $vehicle->driver_name);
        $this->assertSame('John Otieno', $vehicle->driver_display);
    }

    public function test_the_driver_is_optional(): void
    {
        $this->actingAs($this->admin)->post(route('vehicles.store'), $this->payload([
            'vehicle_number' => 'KDJ 210C',
        ]))->assertRedirect(route('vehicles.index'));

        $vehicle = Vehicle::where('vehicle_number', 'KDJ 210C')->firstOrFail();

        $this->assertNull($vehicle->driver_name);
        $this->assertSame('Not Assigned', $vehicle->driver_display);
    }

    public function test_an_over_long_driver_name_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->from(route('vehicles.create'))
            ->post(route('vehicles.store'), $this->payload([
                'driver_name' => str_repeat('a', 101),
            ]))
            ->assertSessionHasErrors('driver_name');

        $this->assertDatabaseMissing('vehicles', ['vehicle_number' => 'KDJ 210B']);
    }

    public function test_the_driver_name_can_be_changed_and_cleared(): void
    {
        $this->actingAs($this->admin)->post(route('vehicles.store'), $this->payload([
            'driver_name' => 'John Otieno',
        ]));

        $vehicle = Vehicle::where('vehicle_number', 'KDJ 210B')->firstOrFail();

        $this->actingAs($this->admin)->put(route('vehicles.update', $vehicle->vehicle_id), $this->payload([
            'driver_name' => 'Mary Achieng',
        ]))->assertRedirect(route('vehicles.index'));

        $this->assertSame('Mary Achieng', $vehicle->fresh()->driver_display);

        $this->actingAs($this->admin)->put(route('vehicles.update', $vehicle->vehicle_id), $this->payload([
            'driver_name' => '',
        ]))->assertRedirect(route('vehicles.index'));

        $this->assertSame('Not Assigned', $vehicle->fresh()->driver_display);
    }

    public function test_the_show_page_displays_the_typed_driver(): void
    {
        $this->actingAs($this->admin)->post(route('vehicles.store'), $this->payload([
            'driver_name' => 'John Otieno',
        ]));

        $vehicle = Vehicle::where('vehicle_number', 'KDJ 210B')->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('vehicles.show', $vehicle->vehicle_id))
            ->assertStatus(200)
            ->assertSee('John Otieno');
    }
}
