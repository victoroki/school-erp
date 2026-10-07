<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regression test for the /route-stops footer leak.
 *
 * The layout's inline init script once contained a JS comment mentioning
 * the @vite directive. Blade compiles directives even inside JS comments,
 * so Vite injected its <script type="module"> tag mid-comment and its
 * closing </script> terminated the inline block early. Everything after
 * it (the initializeSelect2 body) leaked out as visible text directly
 * under the main footer.
 *
 * These tests strip every <script> block from the rendered HTML and
 * assert none of the init-script internals survive as page text.
 */
class LayoutFooterScriptLeakTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_init_script_text_leaks_below_the_footer(): void
    {
        $response = $this->actingAs($this->admin())->get(route('routeStops.index'));

        $response->assertOk();

        // Sanity: the real footer rendered.
        $response->assertSee('main-footer', false);
        $response->assertSee('All rights reserved.', false);

        $html = $response->getContent();

        // Balanced script tags: an early </script> from an injected tag
        // unbalances the count.
        $this->assertSame(
            preg_match_all('/<script\b/i', $html),
            preg_match_all('/<\/script>/i', $html),
            'Unbalanced <script>/</script> tags — a tag was injected mid-script.'
        );

        // Remove every script block; nothing from the init script may
        // remain as visible text.
        $withoutScripts = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);

        $this->assertStringNotContainsString('initializeSelect2', $withoutScripts);
        $this->assertStringNotContainsString('initializeAdminLTE', $withoutScripts);
        $this->assertStringNotContainsString('dead code', $withoutScripts);
        $this->assertStringNotContainsString('but that bundle is an ES module', $withoutScripts);
    }

    public function test_inline_init_script_is_present_exactly_once(): void
    {
        $response = $this->actingAs($this->admin())->get(route('routeStops.index'));

        $response->assertOk();

        $this->assertSame(
            1,
            substr_count($response->getContent(), 'function initializeSelect2'),
            'The select2 init script must appear exactly once per page.'
        );
    }

    private function admin(): User
    {
        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $user = User::firstOrCreate(
            ['email' => 'layout.leak.admin@example.test'],
            ['name' => 'Layout Leak Admin', 'password' => bcrypt('secret')]
        );
        $user->roles()->syncWithoutDetaching(Role::where('role_name', 'Admin')->firstOrFail());

        return $user->load('roles.permissions');
    }
}
