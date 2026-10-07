<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The reported defect: GET /route-stops/create returned
 * "Route [route-stops.store] not defined" because the form action named the
 * resource `route-stops.store` while the resource is registered with
 * ->names('routeStops'). The route helper is resolved when the form renders, so
 * the create and edit screens 500'd.
 *
 * @see ViewRouteReferenceTest for the lint that now keeps this class of name
 *      drift out of the form actions of every view.
 */
class RouteStopFormRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_create_screen_renders(): void
    {
        $user = $this->admin();

        $this->actingAs($user)->get(route('routeStops.create'))
            ->assertOk()
            ->assertSee('action="'.route('routeStops.store').'"', false);
    }

    public function test_the_edit_screen_renders_and_targets_the_update_route(): void
    {
        $user = $this->admin();

        $stop = RouteStop::create([
            'route_id' => Route::create([
                'name' => 'Verification Route',
                'start_point' => 'School',
                'end_point' => 'Town',
            ])->route_id,
            'stop_name' => 'Verification Stop',
            'sequence' => 1,
        ]);

        $this->actingAs($user)->get(route('routeStops.edit', $stop->stop_id))
            ->assertOk()
            ->assertSee(route('routeStops.update', $stop->stop_id), false);
    }

    private function admin(): User
    {
        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $user = User::firstOrCreate(
            ['email' => 'route.stops.admin@example.test'],
            ['name' => 'Route Stops Admin', 'password' => bcrypt('secret')]
        );
        $user->roles()->syncWithoutDetaching(Role::where('role_name', 'Admin')->firstOrFail());

        return $user->load('roles.permissions');
    }
}
