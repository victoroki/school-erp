import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { copyFileSync, mkdirSync } from 'node:fs';
import { resolve } from 'node:path';

/**
 * jQuery, Bootstrap and AdminLTE are served as classic scripts, not bundled
 * into an ES module. Module scripts only run after the page has parsed, so
 * every inline `$(document).ready(...)` in a view ran before `$` existed and
 * silently did nothing (31 screens' dynamic rows, filters and charts). As
 * classic scripts they are on window before any page script executes.
 *
 * The files are the packages' own dist builds at the versions pinned in
 * package.json, copied next to the Vite output so they stay self-hosted.
 */
const VENDOR_SCRIPTS = {
    'jquery.min.js': 'node_modules/jquery/dist/jquery.min.js',
    'bootstrap.bundle.min.js': 'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',
    'adminlte.min.js': 'node_modules/admin-lte/dist/js/adminlte.min.js',
};

function copyVendorScripts() {
    const copy = () => {
        const dir = resolve('public/build/vendor');
        mkdirSync(dir, { recursive: true });
        for (const [name, from] of Object.entries(VENDOR_SCRIPTS)) {
            copyFileSync(resolve(from), resolve(dir, name));
        }
    };

    return {
        name: 'copy-vendor-scripts',
        configureServer: copy,
        writeBundle: copy,
    };
}

/**
 * AdminLTE compiles a dark-mode copy of nearly every rule, an `accent-*`
 * colour theme per palette entry and a `bg-gradient-*` per colour, with no
 * flag to turn them off. None of them are used here (only the two gradients
 * kept below are), and together they were over half of the stylesheet every
 * page downloads and parses. Drop those selectors at build time.
 */
const UNUSED_SELECTOR = /\.dark-mode\b|\.accent-[a-z]|\.bg-gradient-(?!primary\b|secondary\b)[a-z]/;

const stripUnusedAdminLte = {
    postcssPlugin: 'strip-unused-adminlte',
    Rule(rule) {
        if (!UNUSED_SELECTOR.test(rule.selector)) {
            return;
        }
        const kept = rule.selectors.filter((selector) => !UNUSED_SELECTOR.test(selector));
        if (kept.length === 0) {
            rule.remove();
        } else {
            rule.selectors = kept;
        }
    },
};

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/sass/app.scss'],
            refresh: true,
        }),
        copyVendorScripts(),
    ],
    css: {
        postcss: {
            plugins: [stripUnusedAdminLte],
        },
    },
    resolve: {
        alias: [
            {
                // this is required for the SCSS modules
                find: /^~(.*)$/,
                replacement: '$1',
            },
        ],
    },
});
