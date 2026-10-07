<?php

namespace Tests\Feature;

use App\Models\Parents;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Parents / Guardians module.
 *
 * Regression coverage for two reported defects and for the guardian-deletion
 * rules that replaced the raw foreign-key failure:
 *  - GET /parents/{id} fataled with "Class App\Http\Controllers\Parents not
 *    found" because the controller referred to the model without importing it.
 *  - Deleting a guardian raised MySQL 1451 on
 *    student_parent_relationship_ibfk_2.
 */
class ParentsModuleTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $admin;

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

    private function makeParent(array $overrides = []): Parents
    {
        static $seq = 0;

        $seq++;

        return Parents::create(array_merge([
            'first_name' => 'Amani',
            'last_name' => 'Wanjiru',
            'relationship' => 'mother',
            'email' => "guardian{$seq}@example.test",
            'phone' => '0712' . str_pad((string) $seq, 6, '0', STR_PAD_LEFT),
            'occupation' => 'Teacher',
        ], $overrides));
    }

    private function link(Student $student, Parents $parent, bool $isPrimary = false): void
    {
        Schema::getConnection()->table('student_parent_relationship')->insert([
            'student_id' => $student->student_id,
            'parent_id' => $parent->parent_id,
            'is_primary_contact' => $isPrimary,
        ]);
    }

    public function test_index_lists_guardians_with_linked_student_count(): void
    {
        $parent = $this->makeParent();
        $student = Student::factory()->create(['first_name' => 'Nia', 'last_name' => 'Wanjiru']);
        $this->link($student, $parent);

        $response = $this->actingAs($this->admin)->get(route('parents.index'));

        $response->assertOk();
        $response->assertSee('Wanjiru');
        $response->assertSee('Guardians on file');
        $this->assertSame(1, $parent->fresh()->students()->count());
    }

    public function test_index_search_narrows_by_guardian_name(): void
    {
        $this->makeParent(['first_name' => 'Wanjiru', 'last_name' => 'Achieng']);
        $this->makeParent(['first_name' => 'Brian', 'last_name' => 'Otieno']);

        $response = $this->actingAs($this->admin)->get(route('parents.index', ['q' => 'Otieno']));

        $response->assertOk();
        $response->assertSee('Brian');
        $response->assertDontSee('Achieng');
    }

    public function test_index_shows_an_empty_state_when_no_guardians_exist(): void
    {
        $response = $this->actingAs($this->admin)->get(route('parents.index'));

        $response->assertOk();
        $response->assertSee('No guardians found');
    }

    /**
     * The reported "/parents/3 -> Class App\Http\Controllers\Parents not
     * found" fatal. The controller has to resolve the model, not itself.
     */
    public function test_parent_profile_page_loads(): void
    {
        $parent = $this->makeParent(['first_name' => 'Grace', 'last_name' => 'Njeri']);

        $response = $this->actingAs($this->admin)->get(route('parents.show', $parent->parent_id));

        $response->assertOk();
        $response->assertSee('Guardian Profile');
        $response->assertSee('Grace Njeri');
    }

    public function test_parent_profile_lists_linked_students(): void
    {
        $parent = $this->makeParent();
        $student = Student::factory()->create(['first_name' => 'Amina', 'last_name' => 'Wanjiru', 'admission_no' => 'ADM-7788']);
        $this->link($student, $parent, true);

        $response = $this->actingAs($this->admin)->get(route('parents.show', $parent->parent_id));

        $response->assertOk();
        $response->assertSee('Amina Wanjiru');
        $response->assertSee('ADM-7788');
    }

    public function test_parent_profile_handles_a_guardian_without_a_portal_account(): void
    {
        $parent = $this->makeParent();

        $response = $this->actingAs($this->admin)->get(route('parents.show', $parent->parent_id));

        $response->assertOk();
        $response->assertSee('No portal account linked');
    }

    public function test_parent_profile_redirects_when_the_record_is_missing(): void
    {
        $response = $this->actingAs($this->admin)->get(route('parents.show', 999999));

        $response->assertRedirect(route('parents.index'));
        $response->assertSessionHas('flash_notification');
    }

    public function test_deleting_an_unused_parent_succeeds(): void
    {
        $parent = $this->makeParent();

        $response = $this->actingAs($this->admin)->delete(route('parents.destroy', $parent->parent_id));

        $response->assertRedirect(route('parents.index'));
        $this->assertDatabaseMissing('parents', ['parent_id' => $parent->parent_id]);
    }

    /**
     * The reported MySQL 1451. A non-primary guardian who is one of several
     * guardians can be removed; the pivot is detached inside the transaction
     * rather than the foreign key being dropped.
     */
    public function test_deleting_a_guardian_of_multiple_guardians_detaches_the_pivot(): void
    {
        $student = Student::factory()->create();
        $coGuardian = $this->makeParent(['first_name' => 'Baba']);
        $removable = $this->makeParent(['first_name' => 'Mama', 'last_name' => 'Otieno']);

        $this->link($student, $coGuardian, true);
        $this->link($student, $removable, false);

        $response = $this->actingAs($this->admin)->delete(route('parents.destroy', $removable->parent_id));

        $response->assertRedirect(route('parents.index'));
        $this->assertDatabaseMissing('parents', ['parent_id' => $removable->parent_id]);
        $this->assertDatabaseMissing('student_parent_relationship', ['parent_id' => $removable->parent_id]);
        // The co-guardian link is untouched and the learner is not orphaned.
        $this->assertDatabaseHas('student_parent_relationship', [
            'student_id' => $student->student_id,
            'parent_id' => $coGuardian->parent_id,
        ]);
    }

    public function test_deleting_the_sole_guardian_of_an_active_learner_is_refused(): void
    {
        $student = Student::factory()->create(['is_active' => true]);
        $parent = $this->makeParent();
        $this->link($student, $parent, true);

        $response = $this->actingAs($this->admin)->delete(route('parents.destroy', $parent->parent_id));

        $response->assertRedirect(route('parents.show', $parent->parent_id));
        $response->assertSessionHas('flash_notification');
        $this->assertDatabaseHas('parents', ['parent_id' => $parent->parent_id]);
        $this->assertDatabaseHas('student_parent_relationship', ['parent_id' => $parent->parent_id]);
    }

    public function test_deleting_the_primary_guardian_is_refused_even_with_a_second_guardian(): void
    {
        $student = Student::factory()->create(['is_active' => true]);
        $secondary = $this->makeParent(['first_name' => 'Baba']);
        $primary = $this->makeParent(['first_name' => 'Mama']);

        $this->link($student, $primary, true);
        $this->link($student, $secondary, false);

        $response = $this->actingAs($this->admin)->delete(route('parents.destroy', $primary->parent_id));

        $response->assertRedirect(route('parents.show', $primary->parent_id));
        $this->assertDatabaseHas('parents', ['parent_id' => $primary->parent_id]);
    }

    public function test_the_refusal_message_names_the_blocking_learner(): void
    {
        $student = Student::factory()->create([
            'is_active' => true,
            'first_name' => 'Zawadi',
            'last_name' => 'Mwangi',
            'admission_no' => 'ADM-4242',
        ]);
        $parent = $this->makeParent();
        $this->link($student, $parent, true);

        $this->actingAs($this->admin)->delete(route('parents.destroy', $parent->parent_id))
            ->assertSessionHas('flash_notification');

        $this->assertStringContainsString('Zawadi Mwangi', session('flash_notification'));
        $this->assertStringContainsString('ADM-4242', session('flash_notification'));
    }

    public function test_deleting_a_guardian_keeps_the_linked_portal_account(): void
    {
        $user = User::factory()->create();
        $parent = $this->makeParent(['user_id' => $user->id]);

        $response = $this->actingAs($this->admin)->delete(route('parents.destroy', $parent->parent_id));

        $response->assertRedirect(route('parents.index'));
        $this->assertDatabaseMissing('parents', ['parent_id' => $parent->parent_id]);
        // The shared user row is not ours to delete: audit history references it.
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_deleting_a_guardian_of_an_inactive_learner_is_allowed(): void
    {
        $student = Student::factory()->create(['is_active' => false]);
        $parent = $this->makeParent();
        $this->link($student, $parent, true);

        $this->actingAs($this->admin)->delete(route('parents.destroy', $parent->parent_id))
            ->assertRedirect(route('parents.index'));

        $this->assertDatabaseMissing('parents', ['parent_id' => $parent->parent_id]);
    }

    public function test_a_teacher_cannot_manage_parents(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');

        $parent = $this->makeParent();

        $this->actingAs($teacher)->get(route('parents.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('parents.show', $parent->parent_id))->assertForbidden();
        $this->actingAs($teacher)->delete(route('parents.destroy', $parent->parent_id))->assertForbidden();

        $this->assertDatabaseHas('parents', ['parent_id' => $parent->parent_id]);
    }

    public function test_the_enrolled_learner_class_is_reported_on_the_profile(): void
    {
        if (! Schema::hasTable('student_class_enrollments')) {
            $this->markTestSkipped('Enrollment table not available.');
        }

        $parent = $this->makeParent();
        $student = Student::factory()->create();
        $this->link($student, $parent);

        StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => null,
            'roll_number' => '1',
            'academic_year_id' => \App\Models\AcademicYear::create([
                'name' => 'PT-'.uniqid(),
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_current' => true,
            ])->academic_year_id,
            'is_current' => true,
            'enrollment_date' => '2026-01-05',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('parents.show', $parent->parent_id));

        $response->assertOk();
        $response->assertSee('Unassigned');
    }
}
