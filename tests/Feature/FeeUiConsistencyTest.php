<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Guards for the two UI defects found by rendering the real Fee Management
 * screens. Both were invisible to `php -l`, to the route/view tests and to the
 * Blade compile check — they only showed up in the browser — so they need a
 * test that asserts the invariant itself rather than rendering a page and hoping
 * the difference is noticed.
 */
class FeeUiConsistencyTest extends TestCase
{
    /**
     * Font Awesome 5.14.0 Free (the version the AdminLTE layout loads from
     * cdnjs) ships a small Regular subset. Requesting `far` for an icon that
     * only ships in Solid means the Regular webfont has no glyph for that code
     * point, and the browser paints an empty box instead of the icon.
     *
     * Verified against Font Awesome's own 5.14.0 metadata (metadata/icons.json),
     * which records the styles each icon ships in. These are the icons used in
     * this project that have `styles: ["solid"]` and must therefore never be
     * written as `far`.
     */
    private const ICONS_WITHOUT_A_REGULAR_VARIANT = ['sms', 'briefcase'];

    /**
     * Every `.fa-search` icon-inside-field block must reserve space for the icon
     * with a selector specific enough to beat the generic filter-input rule.
     *
     * The bug: `.fa-filters input.form-control { padding: 0 2rem 0 0.75rem
     * !important; }` is (0,2,1), while `.fa-search .form-control { padding-left:
     * 2.15rem !important; }` is only (0,2,0). Both are !important, so specificity
     * decided and the field kept a 0.75rem left padding — putting the
     * absolutely-positioned icon at left:0.8rem on top of the placeholder text.
     */
    private const SEARCH_PADDING_SELECTOR = '.fa-filters .fa-search input.form-control';

    /**
     * The fee views prefix their own classes with `fa-`, which is Font Awesome's
     * namespace. These two names are also real icon names, so Font Awesome's own
     * `.fa-table:before { content: "\f0ce"; }` (0,1,1) decorates the fee module's
     * table and search-field wrapper with a glyph they never asked for. Those
     * elements do not set the Font Awesome font, so the glyph renders in the
     * inherited text font as an empty box.
     *
     * The application cancels it in public/css/sidebar-fixed-final.css. The
     * cancellation is scoped by element — `table.fa-table` and `div.fa-search` —
     * so that `fas fa-table` and `fas fa-search` icons elsewhere keep their
     * glyph. Both the rule and that scoping are asserted below.
     */
    private const COLLIDING_MODULE_CLASSES = [
        'fa-table'  => 'table',
        'fa-search' => 'div',
    ];

    /**
     * A real icon element always carries a Font Awesome style token, which is
     * what makes it an icon. `<i class="fas fa-search">` is an icon and must keep
     * its glyph; `<div class="fa-search">` is the module's own wrapper and must
     * not get one.
     */
    private const FONT_AWESOME_STYLE_TOKENS = [
        'fa', 'fas', 'far', 'fab', 'fal', 'fad',
        'fa-solid', 'fa-regular', 'fa-light', 'fa-thin', 'fa-duotone', 'fa-brands', 'fa-sharp',
    ];

    private function sharedStylesheet(): string
    {
        return file_get_contents(public_path('css/sidebar-fixed-final.css'));
    }

    private function feeViewFiles(): array
    {
        $files = [];

        foreach (['fee_management', 'fee_categories', 'fee_structures', 'discount_schemes'] as $dir) {
            $path = resource_path('views/' . $dir);
            if (! is_dir($path)) {
                continue;
            }

            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    public function test_no_icon_requests_a_font_awesome_style_that_ships_no_glyph(): void
    {
        $offenders = [];

        $sources = $this->feeViewFiles();
        $sources[] = config_path('menu.php');

        foreach ($sources as $file) {
            if (! is_file($file)) {
                continue;
            }

            $contents = file_get_contents($file);

            foreach (self::ICONS_WITHOUT_A_REGULAR_VARIANT as $icon) {
                if (preg_match('/\bfar\s+fa-' . preg_quote($icon, '/') . '\b/', $contents)) {
                    $offenders[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file) . ' uses "far fa-' . $icon . '"';
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Font Awesome 5.14.0 has no Regular glyph for these icons, so they render as an empty box:\n  "
                . implode("\n  ", $offenders)
        );
    }

    public function test_every_search_field_reserves_space_for_its_icon(): void
    {
        $missing = [];
        $checked = 0;

        foreach ($this->feeViewFiles() as $file) {
            $contents = file_get_contents($file);

            if (! str_contains($contents, '.fa-search')) {
                continue;
            }

            $checked++;

            // The icon only gets its space if a selector specific enough to
            // override the generic filter-input padding exists.
            if (! str_contains($contents, self::SEARCH_PADDING_SELECTOR)) {
                $missing[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
            }
        }

        $this->assertGreaterThan(0, $checked, 'No fee view uses the search-field pattern; this guard has become meaningless.');

        $this->assertSame(
            [],
            $missing,
            "These views position an icon inside the search input but never reserve room for it, "
                . "so the icon overlaps the placeholder text:\n  " . implode("\n  ", $missing)
        );
    }

    /**
     * The cancellation rule must exist, for each colliding name, and it must
     * cancel rather than merely restyle. `content: none` stops the pseudo-element
     * from being generated at all; anything else would leave a box behind, or an
     * empty cell in the table's box tree.
     */
    public function test_font_awesome_cannot_decorate_the_fee_modules_own_classes(): void
    {
        $rules = $this->declarationBlocks($this->sharedStylesheet());

        foreach (self::COLLIDING_MODULE_CLASSES as $class => $element) {
            $selector = $element . '.' . $class . '::before';

            $this->assertArrayHasKey(
                $selector,
                $rules,
                'The shared stylesheet no longer cancels the Font Awesome pseudo-element on ' . $selector
                    . ', so it renders as an empty box again.'
            );

            $this->assertStringContainsString(
                'content: none',
                $rules[$selector],
                $selector . ' is present but does not set `content: none`, so the box stays.'
            );
        }
    }

    /**
     * Every rule in the stylesheet, keyed by each of its own selectors, with the
     * declaration body as the value. Comments are stripped first: the comment
     * documenting this fix quotes Font Awesome's selector for the reader, and a
     * naive scan would mistake that for a real rule.
     *
     * @return array<string, string>
     */
    private function declarationBlocks(string $css): array
    {
        $css = preg_replace('#/\*.*?\*/#s', '', $css);

        $rules = [];
        preg_match_all('/([^{}]+)\{([^}]*)\}/', $css, $blocks, PREG_SET_ORDER);

        foreach ($blocks as $block) {
            foreach (array_map('trim', explode(',', trim($block[1]))) as $selector) {
                $rules[$selector] = $block[2];
            }
        }

        return $rules;
    }

    /**
     * The cancellation is scoped by element type, which is what keeps
     * `fas fa-table` and `fas fa-search` icons working. That scoping is only
     * correct while the module puts these names on those same elements — so if a
     * view moves `.fa-search` onto, say, a `<span>`, the guard silently stops
     * applying and the box comes back.
     */
    public function test_the_module_only_uses_those_names_on_the_elements_the_guard_matches(): void
    {
        $found = [];
        $offenders = [];

        foreach ($this->feeViewFiles() as $file) {
            $short = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file);
            $contents = file_get_contents($file);

            if (! preg_match_all('/<([a-z0-9]+)[^>]*\sclass\s*=\s*"([^"]*)"/i', $contents, $tags, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($tags as $tag) {
                $element = strtolower($tag[1]);
                $tokens = preg_split('/\s+/', trim($tag[2]));

                // An element carrying a Font Awesome style token is a real icon,
                // which the scoped cancellation deliberately leaves alone.
                if (array_intersect($tokens, self::FONT_AWESOME_STYLE_TOKENS)) {
                    continue;
                }

                foreach ($tokens as $token) {
                    if (! isset(self::COLLIDING_MODULE_CLASSES[$token])) {
                        continue;
                    }

                    $found[$token] = true;

                    if ($element !== self::COLLIDING_MODULE_CLASSES[$token]) {
                        $offenders[] = $short . ': .' . $token . ' is on a <' . $element . '>';
                    }
                }
            }
        }

        foreach (self::COLLIDING_MODULE_CLASSES as $class => $element) {
            $this->assertArrayHasKey(
                $class,
                $found,
                'No fee view puts .' . $class . ' on a <' . $element . '> any more; this guard has become meaningless.'
            );
        }

        $this->assertSame(
            [],
            $offenders,
            "These elements no longer match the scoped cancellation rule, so Font Awesome will decorate them "
                . "with an empty box:\n  " . implode("\n  ", $offenders)
        );
    }

    /**
     * The two tests above assert a rule inside a stylesheet. That is only worth
     * anything if the stylesheet actually reaches the fee screens, so this pins
     * the link: layouts/app.blade.php is what every fee view extends, and it is
     * what loads the file carrying the cancellation.
     *
     * The cancellation's winning specificity — (0,1,2) against Font Awesome's
     * (0,1,1), because of the element name in the selector — means it does not
     * matter which of the two stylesheets loads first.
     */
    public function test_the_stylesheet_carrying_the_cancellation_reaches_the_fee_screens(): void
    {
        $appLayout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertStringContainsString(
            "@push('page_css')",
            $appLayout,
            'layouts/app.blade.php no longer pushes its stylesheets onto page_css.'
        );

        $this->assertStringContainsString(
            "asset('css/sidebar-fixed-final.css')",
            $appLayout,
            'layouts/app.blade.php no longer loads the shared stylesheet that carries the cancellation rule, '
                . 'so the empty box would come back on every fee screen.'
        );

        $this->assertStringStartsWith(
            "@extends('layouts.app')",
            file_get_contents(resource_path('views/fee_management/reports/discount_summary.blade.php')),
            'The fee view that showed the defect no longer extends the layout that loads the cancellation.'
        );
    }
}
