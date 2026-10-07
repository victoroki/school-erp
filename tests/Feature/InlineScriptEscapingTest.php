<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * A Blade directive that expands to markup containing a closing `</script>` must
 * never sit inside a `<script>` block. The HTML parser terminates the enclosing
 * element at that `</script>`, so every line after it is rendered as visible page
 * text.
 *
 * That is how the Select2 bootstrap comment leaked into the footer of every page:
 * the comment at layouts/app.blade.php:94 read
 * `// ... rendered straight after @vite('resources/js/app.js'),` and `@vite`
 * compiles to a full `<script type="module" src="..."></script>` tag, whose
 * closing tag ended the block early.
 */
class InlineScriptEscapingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    public function test_the_select2_bootstrap_comment_does_not_escape_into_the_page(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'inline.script.admin@example.test'],
            ['name' => 'Inline Script Admin', 'password' => bcrypt('secret')]
        );

        DB::table('user_roles')->insert([
            'user_id' => $admin->id,
            'role_id' => Role::where('role_name', 'Admin')->value('role_id'),
        ]);

        $html = $this->actingAs($admin->fresh()->load('roles.permissions'))
            ->get(route('routeStops.create'))
            ->assertOk()
            ->getContent();

        // The inline initializer must still be delivered as executable script.
        $this->assertStringContainsString('function initializeSelect2(attempt)', $html);

        $this->assertStringNotContainsString(
            'but that bundle is an ES module',
            $this->textContent($html),
            'The JavaScript comment was rendered as page text — a Blade directive compiled to a </script> inside a <script> block.'
        );

        $this->assertStringNotContainsString('function initializeSelect2(attempt)', $this->textContent($html));
    }

    /**
     * The same guard across every view, so a future comment or a page-specific
     * `@vite` call cannot reintroduce it.
     */
    public function test_no_view_puts_a_script_closing_directive_inside_a_script_block(): void
    {
        $views = resource_path('views');
        $directives = ['vite', 'viteReactRefresh', 'stack', 'push', 'livewireStyles'];
        $offenders = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($views)) as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($views) + 1));
            $lines = file($file->getPathname(), FILE_IGNORE_NEW_LINES);

            $inScript = false;

            foreach ($lines as $index => $line) {
                $wasIn = $inScript;

                if (preg_match('/<script\b/i', $line)) {
                    $inScript = true;
                }
                if (preg_match('/<\/script\s*>/i', $line)) {
                    $inScript = false;
                }

                if (! $wasIn) {
                    continue;
                }

                foreach ($directives as $directive) {
                    if (preg_match('/(?<!@)@'.$directive.'\s*[\(\[]/', $line)) {
                        $offenders[] = $relative.':'.($index + 1).'  @'.$directive;
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "These directives expand to markup containing </script> but sit inside a <script> block, "
                ."so they end the block early and dump the remaining JavaScript into the page:\n  "
                .implode("\n  ", $offenders)
        );
    }

    /** Everything the browser shows as text rather than markup. */
    private function textContent(string $html): string
    {
        $withoutScripts = preg_replace('#<script\b[^>]*>.*?</script\s*>#is', ' ', $html) ?? $html;

        return html_entity_decode(strip_tags($withoutScripts), ENT_QUOTES | ENT_HTML5);
    }
}
