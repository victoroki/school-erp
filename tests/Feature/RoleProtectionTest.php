<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->superAdmin = User::factory()->create(['email' => 'protection-sa@test.local']);
        $this->superAdmin->roles()->sync([
            Role::where('role_name', 'Super Admin')->firstOrFail()->role_id,
        ]);

        $managerRole = Role::create([
            'role_name' => 'Users Manager',
            'description' => 'Manager for protection tests',
        ]);
        $managerRole->permissions()->sync(
            Permission::whereIn('permission_name', ['users.view', 'users.manage'])->pluck('permission_id')
        );

        $this->manager = User::factory()->create(['email' => 'protection-manager@test.local']);
        $this->manager->roles()->sync([$managerRole->role_id]);
        $this->manager->load('roles');
    }

    public function test_super_admin_role_is_protected_after_seeding(): void
    {
        $role = Role::where('role_name', 'Super Admin')->firstOrFail();

        $this->assertTrue($role->is_protected);
        $this->assertFalse($role->is_hidden);
    }

    public function test_manager_cannot_update_or_delete_protected_role(): void
    {
        $protected = Role::where('role_name', 'Super Admin')->firstOrFail();
        $custom = Role::create(['role_name' => 'Temp Role', 'description' => 'deletable']);

        $this->actingAs($this->manager);

        $this->put(route('roles.update', $protected->role_id), ['role_name' => 'Hacked', 'description' => 'x'])
            ->assertForbidden();
        $this->delete(route('roles.destroy', $protected->role_id))
            ->assertForbidden();

        $this->put(route('roles.update', $custom->role_id), ['role_name' => 'Temp Role Renamed', 'description' => 'ok'])
            ->assertRedirect(route('roles.index'));
    }

    public function test_manager_cannot_update_delete_or_reset_password_of_protected_user(): void
    {
        $protectedUser = User::factory()->create(['email' => 'protected@test.local', 'is_protected' => true]);
        $normalUser = User::factory()->create(['email' => 'normal@test.local']);

        $this->actingAs($this->manager);

        $this->put(route('users.update', $protectedUser->id), ['name' => 'X', 'email' => 'protected@test.local'])
            ->assertForbidden();
        $this->patch(route('users.reset-password', $protectedUser->id), [
            'password' => 'newsecret123',
            'password_confirmation' => 'newsecret123',
        ])->assertForbidden();
        $this->delete(route('users.destroy', $protectedUser->id))
            ->assertForbidden();

        $this->put(route('users.update', $normalUser->id), ['name' => 'Normal Updated', 'email' => 'normal@test.local'])
            ->assertRedirect(route('users.index'));
    }

    public function test_manager_cannot_delete_self(): void
    {
        $this->actingAs($this->manager)
            ->delete(route('users.destroy', $this->manager->id))
            ->assertForbidden();
    }

    public function test_hidden_role_and_user_hidden_from_non_bypass_listings(): void
    {
        $hiddenUser = User::factory()->create(['email' => 'hidden@test.local', 'is_hidden' => true]);
        Role::create(['role_name' => 'Secret Role', 'description' => 'hidden', 'is_hidden' => true]);

        $this->actingAs($this->manager)
            ->get(route('users.index'))
            ->assertOk()
            ->assertDontSee('hidden@test.local');
        $this->actingAs($this->manager)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertDontSee('Secret Role');

        // Hidden accounts are invisible to ALL school staff — including Super
        // Admins. This keeps the seeded platform-owner account off their radar.
        $this->actingAs($this->superAdmin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertDontSee('hidden@test.local');
        $this->actingAs($this->superAdmin)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Secret Role');

        $this->assertNotNull($hiddenUser->id);
    }

    public function test_manager_cannot_grant_protected_role_when_creating_user(): void
    {
        $superAdminRole = Role::where('role_name', 'Super Admin')->firstOrFail();
        $teacherRole = Role::where('role_name', 'Teacher')->firstOrFail();

        $this->actingAs($this->manager)
            ->post(route('users.store'), [
                'name' => 'New Staff',
                'email' => 'newstaff@test.local',
                'password' => 'secretpass123',
                'password_confirmation' => 'secretpass123',
                'roles' => [$superAdminRole->role_id, $teacherRole->role_id],
            ])
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'newstaff@test.local')->firstOrFail();
        $this->assertTrue($user->hasRole('Teacher'));
        $this->assertFalse($user->hasRole('Super Admin'));
    }

    public function test_manager_cannot_strip_protected_role_when_editing_user(): void
    {
        $superAdminRole = Role::where('role_name', 'Super Admin')->firstOrFail();

        $target = User::factory()->create(['email' => 'target@test.local']);
        $target->roles()->sync([$superAdminRole->role_id]);

        $this->actingAs($this->manager)
            ->put(route('users.update', $target->id), [
                'name' => 'Target',
                'email' => 'target@test.local',
            ])
            ->assertRedirect(route('users.index'));

        $target->refresh();
        $this->assertTrue($target->hasRole('Super Admin'));
    }

    public function test_bypass_user_can_manage_protected_rows(): void
    {
        $protectedRole = Role::where('role_name', 'Super Admin')->firstOrFail();
        $deletableRole = Role::create(['role_name' => 'Unused Role', 'description' => 'no users']);

        $this->actingAs($this->superAdmin);

        $this->put(route('roles.update', $protectedRole->role_id), ['role_name' => 'Super Admin', 'description' => 'updated by bypass'])
            ->assertRedirect(route('roles.index'));

        $this->delete(route('roles.destroy', $deletableRole->role_id))
            ->assertRedirect(route('roles.index'));

        $this->assertNull(Role::find($deletableRole->role_id));
    }

    public function test_rbac_seeder_does_not_grant_owner_to_school_users(): void
    {
        // The Owner role belongs exclusively to the SaaS provider. Seeding
        // RBAC alone must never hand it to any school account (the setup
        // flow grants Super Admin instead).
        \App\Models\UserRole::query()->delete();
        User::query()->delete();

        $first = User::factory()->create(['email' => 'first-admin@test.local']);
        $this->seed(RbacSeeder::class);

        $this->assertFalse($first->hasRole('Owner'));
        $this->assertSame(0, \App\Models\UserRole::where('role_id', Role::where('role_name', 'Owner')->value('role_id'))->count());
    }

    public function test_owner_seeder_creates_env_configured_platform_account(): void
    {
        config(['saas.owner' => [
            'email'    => 'platform-owner@test.local',
            'password' => 'OwnerSecret123',
            'name'     => 'SaaS Provider',
        ]]);

        $this->seed(\Database\Seeders\OwnerSeeder::class);

        $owner = User::where('email', 'platform-owner@test.local')->firstOrFail();
        $this->assertTrue($owner->hasRole('Owner'));
        $this->assertTrue($owner->is_hidden);
        $this->assertTrue($owner->is_protected);
        $this->assertTrue($owner->isOwner());
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('OwnerSecret123', $owner->password));

        // Re-running converges (idempotent) and keeps the existing password
        // even when SAAS_OWNER_PASSWORD is later removed from the environment.
        config(['saas.owner.password' => '']);
        $this->seed(\Database\Seeders\OwnerSeeder::class);

        $owner->refresh();
        $this->assertTrue($owner->hasRole('Owner'));
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('OwnerSecret123', $owner->password));
    }

    public function test_super_admin_cannot_touch_the_platform_owner(): void
    {
        config(['saas.owner' => [
            'email'    => 'platform-owner@test.local',
            'password' => 'OwnerSecret123',
            'name'     => 'SaaS Provider',
        ]]);
        $this->seed(\Database\Seeders\OwnerSeeder::class);

        $owner = User::where('email', 'platform-owner@test.local')->firstOrFail();
        $ownerRole = Role::where('role_name', 'Owner')->firstOrFail();

        // Locked out of the Administration module entirely.
        $this->actingAs($this->superAdmin)->get('/audit-trail')->assertForbidden();
        $this->actingAs($this->superAdmin)->get('/modules')->assertForbidden();

        // Cannot edit, delete or reset the password of the owner account.
        $this->put(route('users.update', $owner->id), ['name' => 'Hijacked', 'email' => $owner->email])
            ->assertForbidden();
        $this->patch(route('users.reset-password', $owner->id), [
            'password' => 'hacked-pass-123',
            'password_confirmation' => 'hacked-pass-123',
        ])->assertForbidden();
        $this->delete(route('users.destroy', $owner->id))->assertForbidden();

        // Cannot rename, delete or grant the Owner role.
        $this->put(route('roles.update', $ownerRole->role_id), ['role_name' => 'Owned', 'description' => 'x'])
            ->assertForbidden();
        $this->delete(route('roles.destroy', $ownerRole->role_id))->assertForbidden();

        $target = User::factory()->create(['email' => 'grant-target@test.local']);
        $this->post(route('users.store'), [
            'name' => 'Grant Target',
            'email' => 'grant-target2@test.local',
            'password' => 'secretpass123',
            'password_confirmation' => 'secretpass123',
            'roles' => [$ownerRole->role_id],
        ])->assertRedirect(route('users.index'));

        $created = User::where('email', 'grant-target2@test.local')->firstOrFail();
        $this->assertFalse($created->hasRole('Owner'));

        // And cannot strip the role from an existing owner either.
        $this->put(route('users.update', $owner->id), ['name' => $owner->name, 'email' => $owner->email])
            ->assertForbidden();

        $owner->refresh();
        $this->assertTrue($owner->hasRole('Owner'));
        $this->assertNotNull(Role::find($ownerRole->role_id));
    }

    public function test_owner_has_all_access_and_is_hidden(): void
    {
        $ownerRole = Role::where('role_name', 'Owner')->firstOrFail();
        $this->assertTrue($ownerRole->is_protected);
        $this->assertTrue($ownerRole->is_hidden);

        $owner = User::factory()->create(['email' => 'owner@test.local']);
        $owner->roles()->sync([$ownerRole->role_id]);
        $owner->load('roles');

        // Owner reaches the audit trail and module screens.
        $this->actingAs($owner)->get('/audit-trail')->assertOk();
        $this->actingAs($owner)->get('/modules')->assertOk();

        // Owner is hidden from all role listings (exists but not displayed).
        $this->actingAs($this->manager)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertDontSee('Owner');
        $this->actingAs($this->superAdmin)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertDontSee('Owner');

        // Owner can toggle a core module (B6 decision: privileged users toggle everything).
        $this->seed(\Database\Seeders\ModuleSeeder::class);
        $this->actingAs($owner)
            ->post('/modules/dashboard/toggle', ['is_active' => 0])
            ->assertRedirect();
        $this->assertFalse(app(\App\Services\ModuleManager::class)->isActive('dashboard'));
        $this->actingAs($owner)
            ->post('/modules/dashboard/toggle', ['is_active' => 1])
            ->assertRedirect();
        $this->assertTrue(app(\App\Services\ModuleManager::class)->isActive('dashboard'));
    }
}
