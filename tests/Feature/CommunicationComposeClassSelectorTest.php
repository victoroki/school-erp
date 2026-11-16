<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regression test for the compose screen's dead class selector.
 *
 * The view's init script opened with $(document).ready(...) while jQuery
 * ships in the deferred Vite module that loads AFTER page_scripts, so $
 * was undefined and the whole script died — the "Specific Class" and
 * "Specific Class Section" pickers never unhid. The script now polls for
 * jQuery (the class_teachers/index.blade.php convention); this test pins
 * the markup and the init pattern.
 */
class CommunicationComposeClassSelectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_compose_renders_class_selector_with_class_options(): void
    {
        SchoolClass::create(['name' => 'Grade 9', 'numeric_value' => 9]);

        $response = $this->actingAs($this->admin())->get(route('communication.compose'));

        $response->assertOk();

        // The hidden dynamic pickers exist and the class options rendered.
        $response->assertSee('id="class_selector"', false);
        $response->assertSee('id="class_section_selector"', false);
        $response->assertSee('name="class_id"', false);
        $response->assertSee('name="class_section_id"', false);
        $response->assertSee('Grade 9');
    }

    public function test_compose_script_uses_poll_init_not_document_ready(): void
    {
        $response = $this->actingAs($this->admin())->get(route('communication.compose'));

        $response->assertOk();

        $html = $response->getContent();

        // The dead-code opener must be gone: any top-level
        // $(document).ready in page_scripts runs before jQuery exists.
        $this->assertStringNotContainsString('$(document).ready', $html);

        // The poll-based init is wired to DOMContentLoaded.
        $this->assertStringContainsString('initCompose', $html);
    }

    private function admin(): User
    {
        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $user = User::firstOrCreate(
            ['email' => 'compose.class.admin@example.test'],
            ['name' => 'Compose Class Admin', 'password' => bcrypt('secret')]
        );
        $user->roles()->syncWithoutDetaching(Role::where('role_name', 'Admin')->firstOrFail());

        return $user->load('roles.permissions');
    }
}
