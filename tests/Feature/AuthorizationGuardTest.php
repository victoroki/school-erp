<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Route/controller guard coverage found missing in the view audit.
 */
class AuthorizationGuardTest extends TestCase
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

    public function test_academic_calendar_forms_require_the_manage_permission(): void
    {
        // Teacher holds academics.view but not academics.settings.manage.
        $teacher = $this->userWithRole('Teacher');

        $this->actingAs($teacher)->get('/academic-calendar/create')->assertForbidden();
        $this->actingAs($teacher)->get('/academic-calendar/1/edit')->assertForbidden();
    }

    public function test_academic_calendar_still_lets_permitted_users_view_the_index(): void
    {
        $this->actingAs($this->userWithRole('Teacher'))
            ->get('/academic-calendar')
            ->assertOk();
    }

    public function test_academic_calendar_writes_are_blocked_for_teachers(): void
    {
        $this->actingAs($this->userWithRole('Teacher'))
            ->post('/academic-calendar', [
                'title' => 'Sneaky holiday',
                'start_date' => now()->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_exam_result_form_does_not_post_created_by_from_the_client(): void
    {
        // created_by must come from the authenticated user server-side, never
        // from the request, or a crafted POST could attribute marks to another
        // user. ExamResultController::store/update/saveOne already set it
        // explicitly, so the hidden input was both redundant and a footgun.
        $blade = file_get_contents(resource_path('views/exam_results/fields.blade.php'));

        $this->assertStringNotContainsString(
            "Form::hidden('created_by'",
            $blade,
            'The exam result form must not submit created_by from the client.'
        );
    }
}
