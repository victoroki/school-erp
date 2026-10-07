<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Guards for the HR interface defects reported from the browser:
 *
 *  1. The Job Positions and Staff row-actions menus never opened, because they
 *     were written against the Bootstrap 5 data API (`data-bs-toggle`) while the
 *     app ships Bootstrap 4.6.2 + AdminLTE 3, which only binds `data-toggle`.
 *     Bootstrap 4 silently ignores the unknown attribute, so nothing was wired
 *     to the click and the menu stayed `display: none` — the button looked dead.
 *
 *  2. The Job Positions menu additionally sat inside a Bootstrap
 *     `.table-responsive` (`overflow-x: auto`, whose used `overflow-y` becomes
 *     `auto`) inside a `.dash-panel` with `overflow: hidden`. A
 *     `position: absolute` dropdown is clipped by both, so the menu would have
 *     opened truncated and unreachable.
 *
 *  3. The leave screens redirect back with Flash::error/Flash::success but never
 *     included `flash::message`, so a failed "Apply for Leave" produced no
 *     output at all and read as a dead button.
 *
 * None of these are reachable by `php -l`, by a route test, or by the Blade
 * compile check, so the invariants are asserted against the sources directly.
 * No database is needed, which also keeps these guards runnable while the shared
 * test database is being migrated by another process.
 */
class HrUiConsistencyTest extends TestCase
{
    /** Views whose row-actions menus are Bootstrap dropdowns. */
    private const DROPDOWN_VIEWS = [
        'job_positions/table.blade.php',
        'staff/index.blade.php',
    ];

    /** The leave screens that redirect back with a flash message. */
    private const LEAVE_VIEWS = [
        'hr/leave/index.blade.php',
        'hr/leave/create.blade.php',
        'hr/leave/show.blade.php',
    ];

    private function viewSource(string $relative): string
    {
        $path = resource_path('views/'.$relative);
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    /**
     * The root cause of the dead actions button. Bootstrap 4.6.2 ships no
     * `data-bs-*` handling at all, so the attribute is inert markup.
     */
    public function test_row_action_menus_use_the_bootstrap4_data_api(): void
    {
        foreach (self::DROPDOWN_VIEWS as $view) {
            $contents = $this->viewSource($view);

            $this->assertStringContainsString(
                'data-toggle="dropdown"',
                $contents,
                $view.' has no Bootstrap 4 dropdown toggle, so the actions menu never opens.'
            );

            $this->assertStringNotContainsString(
                'data-bs-toggle',
                $contents,
                $view.' still uses the Bootstrap 5 data-bs-* API. This app ships Bootstrap 4.6.2, '
                    .'which ignores that attribute, so the actions menu silently never opens.'
            );
        }
    }

    /**
     * Pin the version assumption the test above rests on, so a future upgrade
     * to Bootstrap 5 fails here with a clear message instead of leaving the
     * assertions quietly guarding the wrong API.
     */
    public function test_the_app_still_runs_bootstrap_4(): void
    {
        $manifest = base_path('node_modules/bootstrap/package.json');
        $this->assertFileExists($manifest);

        $version = json_decode(file_get_contents($manifest), true)['version'] ?? '';

        $this->assertStringStartsWith(
            '4.',
            $version,
            "Bootstrap is now {$version}. The data-toggle assertions in this file are Bootstrap 4 API; "
                .'if this is a deliberate upgrade to Bootstrap 5, convert them to data-bs-toggle.'
        );
    }

    /**
     * A `position: absolute` dropdown is clipped by any ancestor that scrolls or
     * hides its overflow, so both wrappers the Job Positions table sits in have
     * to stay unclipped.
     */
    public function test_nothing_clips_the_job_positions_actions_menu(): void
    {
        $this->assertStringContainsString(
            'overflow: visible',
            $this->viewSource('job_positions/table.blade.php'),
            'The actions menu lives inside .table-responsive, which clips it. '
                .'The wrapper must be opted out of clipping.'
        );

        $matched = preg_match(
            '/\.dash-panel\s*\{([^}]*)\}/',
            $this->viewSource('job_positions/index.blade.php'),
            $panel
        );

        $this->assertSame(1, $matched, 'The .dash-panel rule disappeared from job_positions/index.blade.php.');

        $this->assertStringNotContainsString(
            'overflow: hidden',
            $panel[1],
            '.dash-panel clips the actions dropdown, so the menu opens truncated. '
                .'Round the first/last rows instead.'
        );
    }

    /**
     * The leave screens redirect back with a flash message. Without this include
     * the message is discarded on render and the failure looks like a dead
     * button rather than an error.
     */
    public function test_the_leave_screens_render_flash_messages(): void
    {
        foreach (self::LEAVE_VIEWS as $view) {
            $this->assertStringContainsString(
                "@include('flash::message')",
                $this->viewSource($view),
                $view.' never renders flash messages, so every error and success on the leave '
                    .'screens is invisible to the user.'
            );
        }
    }
}
