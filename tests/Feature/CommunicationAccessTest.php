<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Communication screen authorization.
 *
 * The controller constructor guarded only(['index', 'show', 'sentMessages']) —
 * none of which exist on the controller. can:communication.view therefore
 * protected nothing, and message history, individual message detail (recipient
 * names and phone numbers), template bodies and recipient counts were readable
 * by any authenticated user, including Teachers.
 */
class CommunicationAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['email' => strtolower($role) . '-' . uniqid() . '@test.local']);
        $user->roles()->sync(Role::where('role_name', $role)->pluck('role_id'));

        return $user->load('roles.permissions');
    }

    public function test_teacher_cannot_read_the_message_history(): void
    {
        $this->actingAs($this->userWithRole('Teacher'))
            ->get('/communication/history')
            ->assertForbidden();
    }

    public function test_teacher_cannot_read_an_individual_message(): void
    {
        $this->actingAs($this->userWithRole('Teacher'))
            ->get('/communication/history/1')
            ->assertForbidden();
    }

    public function test_teacher_cannot_read_template_payloads(): void
    {
        $this->actingAs($this->userWithRole('Teacher'))
            ->get('/communication/api/template/SMS/1')
            ->assertForbidden();
    }

    public function test_teacher_cannot_read_recipient_counts(): void
    {
        $this->actingAs($this->userWithRole('Teacher'))
            ->get('/communication/api/recipients/count?recipient_group=All%20Students')
            ->assertForbidden();
    }

    public function test_teacher_cannot_open_the_compose_screen_or_send(): void
    {
        $teacher = $this->userWithRole('Teacher');

        $this->actingAs($teacher)->get('/communication/compose')->assertForbidden();

        $this->actingAs($teacher)
            ->post('/communication/send', [
                'message_type' => 'SMS',
                'content' => 'Should not be sent',
                'recipient_group' => 'All Students',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('sent_messages', 0);
    }

    public function test_admin_with_communication_view_can_read_history(): void
    {
        $this->actingAs($this->userWithRole('Admin'))
            ->get('/communication/history')
            ->assertOk();
    }

    public function test_admin_with_communication_manage_can_open_compose(): void
    {
        $this->actingAs($this->userWithRole('Admin'))
            ->get('/communication/compose')
            ->assertOk();
    }

    public function test_module_toggle_is_restricted_to_the_owner(): void
    {
        $module = Module::create([
            'key' => 'test-module',
            'name' => 'Test Module',
            'is_core' => false,
            'is_active' => false,
            'order' => 99,
        ]);

        // Module binds on its `key`, not its id.
        $url = "/modules/{$module->key}/toggle";

        // Admin holds every ordinary permission but is not the platform Owner.
        $this->actingAs($this->userWithRole('Admin'))
            ->post($url, ['is_active' => 1])
            ->assertForbidden();

        $this->actingAs($this->userWithRole('Teacher'))
            ->post($url, ['is_active' => 1])
            ->assertForbidden();

        $this->assertFalse($module->fresh()->is_active);
    }

    public function test_owner_can_toggle_a_module(): void
    {
        $module = Module::create([
            'key' => 'owner-toggle-module',
            'name' => 'Owner Toggle Module',
            'is_core' => false,
            'is_active' => false,
            'order' => 98,
        ]);

        $this->actingAs($this->userWithRole('Owner'))
            ->post("/modules/{$module->key}/toggle", ['is_active' => 1])
            ->assertRedirect();

        $this->assertTrue((bool) $module->fresh()->is_active);
    }

    public function test_module_pages_are_owner_only(): void
    {
        $this->actingAs($this->userWithRole('Admin'))->get('/modules')->assertForbidden();
        $this->actingAs($this->userWithRole('Teacher'))->get('/modules')->assertForbidden();
    }
}
