<?php

namespace Tests\Feature;

use App\Models\LeaveType;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeaveTypesIsPaidDisplayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }
    }

    private function admin(): User
    {
        $role = Role::where('role_name', 'Admin')->firstOrFail();
        $user = User::firstOrCreate(
            ['email' => 'paid-display-admin@example.test'],
            ['name' => 'Paid Display Admin', 'password' => bcrypt('secret')]
        );
        $user->roles()->syncWithoutDetaching($role);

        return $user->load('roles.permissions');
    }

    /** @test */
    public function index_shows_yes_no_badges_instead_of_raw_boolean(): void
    {
        LeaveType::create(['name' => 'Annual Leave', 'days_allowed' => 21, 'is_paid' => true]);
        LeaveType::create(['name' => 'Unpaid Leave', 'days_allowed' => 5, 'is_paid' => false]);

        $response = $this->actingAs($this->admin())->get(route('leaveTypes.index'));

        $response->assertOk();
        $response->assertSee('Yes');
        $response->assertSee('No');
        // No raw 1/0 leaking into the Is Paid column (days_allowed are 21/5,
        // so a bare "1" can only come from an unfixed boolean render).
        $response->assertDontSee('<td>1</td>', false);
    }

    /** @test */
    public function show_page_shows_yes_badge_for_paid_type(): void
    {
        $type = LeaveType::create(['name' => 'Paid Sick Leave', 'days_allowed' => 7, 'is_paid' => true]);

        $response = $this->actingAs($this->admin())->get(route('leaveTypes.show', $type->leave_type_id));

        $response->assertOk();
        $response->assertSee('Yes');
        $response->assertDontSee('<td>1</td>', false);
    }

    /** @test */
    public function show_page_shows_no_badge_for_unpaid_type(): void
    {
        $type = LeaveType::create(['name' => 'Compassionate Leave', 'days_allowed' => 3, 'is_paid' => false]);

        $response = $this->actingAs($this->admin())->get(route('leaveTypes.show', $type->leave_type_id));

        $response->assertOk();
        $response->assertSee('No');
    }
}
