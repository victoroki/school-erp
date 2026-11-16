<?php

namespace Tests\Feature;

use App\Models\Parents;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentParentRelationship;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The fee, attendance and profile portal pages resolved the viewer from
 * users.user_type, a legacy column the role system never sets. Every parent
 * and student was therefore turned away from their own fees and saw an empty
 * attendance page and profile. They now resolve by role and link, like the
 * report-card portal.
 */
class PortalAccessByRoleTest extends TestCase
{
    use RefreshDatabase;

    protected Student $child;

    protected Student $stranger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->child = $this->learner('Linked', 'Pupil');
        $this->stranger = $this->learner('Unlinked', 'Stranger');
    }

    public function test_a_linked_parent_sees_their_childs_fees_attendance_and_profile(): void
    {
        $user = $this->portalUser('Parent');
        $parent = Parents::create([
            'user_id' => $user->id,
            'first_name' => 'Grace',
            'last_name' => 'Tester',
            'relationship' => 'mother',
            'phone' => '0712345678',
        ]);
        StudentParentRelationship::create([
            'student_id' => $this->child->student_id,
            'parent_id' => $parent->parent_id,
            'is_primary_contact' => true,
        ]);

        $this->actingAs($user)->get(route('portal.fees'))
            ->assertOk()
            ->assertSee($this->child->full_name, false)
            ->assertDontSee($this->stranger->full_name, false);

        $this->actingAs($user)->get(route('portal.attendance'))
            ->assertOk()
            ->assertSee($this->child->full_name, false);

        $this->actingAs($user)->get(route('portal.profile'))
            ->assertOk()
            ->assertSee('Parent details');
    }

    public function test_a_student_sees_their_own_fees_and_profile(): void
    {
        $user = $this->portalUser('Student');
        $this->child->update(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('portal.fees'))
            ->assertOk()
            ->assertSee($this->child->full_name, false)
            ->assertDontSee($this->stranger->full_name, false);

        $this->actingAs($user)->get(route('portal.profile'))
            ->assertOk()
            ->assertSee('Student details')
            ->assertSee($this->child->admission_no);
    }

    public function test_parent_and_student_sign_in_lands_on_a_real_portal_page(): void
    {
        foreach (['Parent', 'Student'] as $role) {
            $user = $this->portalUser($role);

            $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
                ->assertRedirect('/portal');

            $this->actingAs($user)->get('/portal')->assertRedirect(route('portal.fees'));

            $this->post(route('logout'));
        }
    }

    public function test_an_account_with_no_linked_learner_is_refused_fees(): void
    {
        $this->actingAs($this->portalUser('Parent'))
            ->get(route('portal.fees'))
            ->assertForbidden();
    }

    private function learner(string $first, string $last): Student
    {
        return Student::create([
            'admission_no' => 'PAR'.substr(uniqid(), -8),
            'first_name' => $first,
            'last_name' => $last,
            'date_of_birth' => '2014-01-01',
            'gender' => 'male',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    private function portalUser(string $roleName): User
    {
        $user = User::factory()->create([
            'name' => $roleName.' '.uniqid(),
            'email' => strtolower($roleName).'.'.uniqid().'@test.local',
        ]);
        $user->roles()->sync([Role::where('role_name', $roleName)->firstOrFail()->role_id]);

        return $user->load('roles.permissions');
    }
}
