<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the two RBAC edit data-loss defects found in the view audit:
 *
 *  1. The role/user edit forms compared Eloquent *models* against scalar ids
 *     (Collection::contains($id)), which is always false — so no checkbox was
 *     ever pre-ticked and a save posted an empty selection, wiping the row's
 *     permissions/roles.
 *  2. A request that carried no permission/role payload at all was treated as
 *     "remove everything".
 *
 * These tests submit exactly what the rendered form would submit, so they fail
 * if either regression returns.
 */
class RbacEditPreservationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->superAdmin = User::factory()->create(['email' => 'rbac-edit-sa@test.local']);
        $this->superAdmin->roles()->sync([
            Role::where('role_name', 'Super Admin')->firstOrFail()->role_id,
        ]);
        $this->superAdmin->load('roles');
    }

    /**
     * Read back the ids the rendered form marked as checked — i.e. exactly what
     * a browser submits when the user opens the page and saves without editing.
     *
     * @return list<int>
     */
    private function checkedIds(string $html, string $fieldName): array
    {
        preg_match_all('/<input[^>]*name="' . preg_quote($fieldName, '/') . '"[^>]*>/', $html, $matches);

        return collect($matches[0])
            ->filter(fn (string $tag) => str_contains($tag, 'checked'))
            ->map(function (string $tag) {
                preg_match('/value="(\d+)"/', $tag, $value);

                return (int) ($value[1] ?? 0);
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Permission ids held by a role, read through the relation collection.
     *
     * @return list<int>
     */
    private function permissionIdsOf(Role $role): array
    {
        return $role->permissions->pluck('permission_id')->map(fn ($id) => (int) $id)->values()->all();
    }

    /**
     * Role ids held by a user, read through the relation collection.
     *
     * @return list<int>
     */
    private function roleIdsOf(User $user): array
    {
        return $user->roles->pluck('role_id')->map(fn ($id) => (int) $id)->values()->all();
    }

    private function makeRoleWithPermissions(string $name, array $permissionNames): Role
    {
        $role = Role::create(['role_name' => $name, 'description' => 'fixture']);
        $role->permissions()->sync(
            Permission::whereIn('permission_name', $permissionNames)->pluck('permission_id')->all()
        );

        return $role;
    }

    public function test_role_edit_form_preticks_and_saving_unchanged_preserves_permissions(): void
    {
        $role = $this->makeRoleWithPermissions('Preserve Me', ['students.view', 'students.manage', 'fees.view']);
        $expected = $this->permissionIdsOf($role);

        $this->assertNotEmpty($expected, 'Fixture must assign permissions, otherwise this test proves nothing.');

        $response = $this->actingAs($this->superAdmin)
            ->get(route('roles.edit', $role->role_id))
            ->assertOk();

        $checked = $this->checkedIds($response->getContent(), 'permissions[]');

        $this->assertNotEmpty($checked, 'The role edit form ticked nothing at all.');

        $this->assertEqualsCanonicalizing(
            $expected,
            $checked,
            'The role edit form must pre-tick every permission the role already holds.'
        );

        // Save exactly what the browser rendered.
        $this->actingAs($this->superAdmin)
            ->put(route('roles.update', $role->role_id), [
                'role_name' => $role->role_name,
                'description' => 'renamed only',
                'permissions_submitted' => 1,
                'permissions' => $checked,
            ])
            ->assertRedirect(route('roles.index'));

        $this->assertEqualsCanonicalizing(
            $expected,
            $this->permissionIdsOf($role->fresh()),
            'Saving an untouched role edit form must not change its permissions.'
        );
        $this->assertSame('renamed only', $role->fresh()->description);
    }

    public function test_role_form_can_intentionally_clear_every_permission(): void
    {
        $role = $this->makeRoleWithPermissions('Clear Me', ['students.view', 'fees.view']);

        $this->actingAs($this->superAdmin)
            ->put(route('roles.update', $role->role_id), [
                'role_name' => $role->role_name,
                'description' => $role->description,
                'permissions_submitted' => 1,
                // no permissions[] => the user unticked everything
            ])
            ->assertRedirect(route('roles.index'));

        $this->assertSame(0, $role->fresh()->permissions()->count());
    }

    public function test_role_update_without_any_permission_payload_leaves_permissions_untouched(): void
    {
        $role = $this->makeRoleWithPermissions('Untouched', ['students.view', 'fees.view']);
        $expected = $this->permissionIdsOf($role);

        $this->assertNotEmpty($expected);

        // A caller that never carried the checkbox field (partial/API update).
        $this->actingAs($this->superAdmin)
            ->put(route('roles.update', $role->role_id), [
                'role_name' => 'Untouched Renamed',
                'description' => $role->description,
            ])
            ->assertRedirect(route('roles.index'));

        $this->assertEqualsCanonicalizing($expected, $this->permissionIdsOf($role->fresh()));
    }

    public function test_user_edit_form_preticks_and_saving_unchanged_preserves_roles(): void
    {
        $roleIds = Role::whereIn('role_name', ['Teacher', 'Accountant'])->pluck('role_id')->all();

        $target = User::factory()->create(['email' => 'preserve-roles@test.local']);
        $target->roles()->sync($roleIds);

        $expected = $this->roleIdsOf($target->fresh());
        $this->assertNotEmpty($expected, 'Fixture must assign roles, otherwise this test proves nothing.');

        $response = $this->actingAs($this->superAdmin)
            ->get(route('users.edit', $target->id))
            ->assertOk();

        $checked = $this->checkedIds($response->getContent(), 'roles[]');

        $this->assertNotEmpty($checked, 'The user edit form ticked nothing at all.');

        $this->assertEqualsCanonicalizing(
            $expected,
            $checked,
            'The user edit form must pre-tick every role the user already holds.'
        );

        $this->actingAs($this->superAdmin)
            ->put(route('users.update', $target->id), [
                'name' => 'Renamed Only',
                'email' => 'preserve-roles@test.local',
                'roles_submitted' => 1,
                'roles' => $checked,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertEqualsCanonicalizing(
            $expected,
            $this->roleIdsOf($target->fresh()),
            'Saving an untouched user edit form must not strip roles.'
        );
    }

    public function test_user_update_without_any_role_payload_does_not_strip_roles(): void
    {
        $roleIds = Role::whereIn('role_name', ['Teacher'])->pluck('role_id')->all();

        $target = User::factory()->create(['email' => 'no-role-payload@test.local']);
        $target->roles()->sync($roleIds);

        $expected = $this->roleIdsOf($target->fresh());
        $this->assertNotEmpty($expected);

        $this->actingAs($this->superAdmin)
            ->put(route('users.update', $target->id), [
                'name' => 'Renamed Only',
                'email' => 'no-role-payload@test.local',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertEqualsCanonicalizing($expected, $this->roleIdsOf($target->fresh()));
    }

    public function test_user_form_can_intentionally_remove_all_assignable_roles(): void
    {
        $target = User::factory()->create(['email' => 'strip-all@test.local']);
        $target->roles()->sync(Role::where('role_name', 'Teacher')->pluck('role_id')->all());

        $this->actingAs($this->superAdmin)
            ->put(route('users.update', $target->id), [
                'name' => $target->name,
                'email' => 'strip-all@test.local',
                'roles_submitted' => 1,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertSame(0, $target->fresh()->roles()->count());
    }

    public function test_owner_role_survives_the_owner_editing_their_own_account(): void
    {
        $ownerRole = Role::where('role_name', 'Owner')->firstOrFail();

        $owner = User::factory()->create(['email' => 'self-edit-owner@test.local']);
        $owner->roles()->sync([$ownerRole->role_id]);
        $owner->load('roles');

        $this->assertTrue($owner->isOwner());

        // The Owner role checkbox is deliberately not rendered, so the browser
        // submits no roles at all when the Owner saves their own details.
        $this->actingAs($owner)
            ->put(route('users.update', $owner->id), [
                'name' => 'Owner Renamed',
                'email' => 'self-edit-owner@test.local',
                'roles_submitted' => 1,
            ])
            ->assertRedirect(route('users.index'));

        $owner->refresh();
        $this->assertTrue($owner->hasRole('Owner'), 'The Owner role must never be stripped through user administration.');
    }

    public function test_super_admin_cannot_escalate_to_owner_via_role_payload(): void
    {
        $ownerRole = Role::where('role_name', 'Owner')->firstOrFail();

        $target = User::factory()->create(['email' => 'escalate@test.local']);

        $this->actingAs($this->superAdmin)
            ->put(route('users.update', $target->id), [
                'name' => $target->name,
                'email' => 'escalate@test.local',
                'roles_submitted' => 1,
                'roles' => [$ownerRole->role_id],
            ])
            ->assertRedirect(route('users.index'));

        $this->assertFalse($target->fresh()->hasRole('Owner'));
    }
}
