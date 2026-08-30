<?php

/**
 * Post-deployment HTTP hook for FTP-only shared hosting.
 *
 * GitHub Actions uploads the build over FTP, then POSTs here with an
 * X-Deploy-Token header to run the artisan commands that normally require
 * SSH: migrate --force, storage:link, config:cache, view:cache.
 *
 * Security:
 *  - Token compared against DEPLOY_HOOK_SECRET with hash_equals
 *  - Wrong/missing token → 403 before Laravel is even bootstrapped
 *  - Optional IP allow-list via DEPLOY_ALLOWED_IPS (comma-separated)
 */

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

$httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$tokenInQuery = isset($_GET['token']) ? (string) $_GET['token'] : '';

// POST is the primary entry point. A GET carrying ?token=… is accepted as a
// fallback because shared-hosting WAFs (ModSecurity) frequently block
// automated POSTs while letting plain GETs through untouched.
if ($httpMethod !== 'POST' && $tokenInQuery === '') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed. Use POST.']);

    exit;
}

// ── 0. Load the few .env values we need BEFORE Laravel boots ────────────
// Shared hosting does not populate real environment variables from .env
// (only Laravel's Dotenv does, later), so read the file minimally here.
$hookEnvFile = dirname(__DIR__) . '/.env';
if (is_readable($hookEnvFile)) {
    foreach (file($hookEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $envLine) {
        $envLine = trim($envLine);

        if ($envLine === '' || str_starts_with($envLine, '#') || !str_contains($envLine, '=')) {
            continue;
        }

        [$envKey, $envValue] = array_pad(explode('=', $envLine, 2), 2, '');
        $envKey = trim($envKey);
        $envValue = trim(trim($envValue), "\"'");

        if ($envKey !== '') {
            $_ENV[$envKey] = $envValue;

            if (getenv($envKey) === false) {
                putenv($envKey . '=' . $envValue);
            }
        }
    }
}

// ── 1. Authenticate BEFORE bootstrapping anything ────────────────────────
$providedToken = (string) ($_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? $tokenInQuery);
$expectedToken = (string) ($_ENV['DEPLOY_HOOK_SECRET']
    ?? getenv('DEPLOY_HOOK_SECRET')
    ?: '');

if ($expectedToken === '' || !is_string($providedToken) || !hash_equals($expectedToken, $providedToken)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid or missing X-Deploy-Token.',
    ]);

    exit;
}

// Optional extra layer: restrict calls to known runner IPs.
$allowedIps = array_filter(array_map('trim', explode(
    ',',
    (string) ($_ENV['DEPLOY_ALLOWED_IPS'] ?? getenv('DEPLOY_ALLOWED_IPS') ?: '')
)));
if ($allowedIps !== [] && !in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedIps, true)) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status'  => 'error',
        'message' => 'IP not allowed.',
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);

    exit;
}

@set_time_limit(300);

$start = microtime(true);

// ── 2. Bootstrap Laravel's console kernel (no HTTP handling needed) ─────
require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$results = [];
$warnings = [];
$failed = false;

/**
 * Run one artisan command and capture its output + exit code.
 */
function run_command(string $command, array $parameters = []): void
{
    global $results, $failed;

    try {
        $exitCode = Artisan::call($command, $parameters);
        $output = trim(Artisan::output());

        if (mb_strlen($output) > 4000) {
            $output = mb_substr($output, 0, 4000) . ' …[truncated]';
        }

        $results[] = [
            'command'   => trim($command . ' ' . implode(' ', array_map(
                fn ($k, $v) => is_bool($v) ? '' : $k,
                array_keys($parameters),
                $parameters
            ))),
            'exit_code' => $exitCode,
            'output'    => $output,
            'ok'        => $exitCode === 0,
        ];

        if ($exitCode !== 0) {
            $failed = true;
        }
    } catch (\Throwable $e) {
        $failed = true;
        $results[] = [
            'command'   => $command,
            'exit_code' => -1,
            'output'    => $e->getMessage(),
            'ok'        => false,
        ];
    }
}

// ── 3. Make sure an APP_KEY exists (first-deployment safety net) ────────
if (empty($app['config']->get('app.key'))) {
    run_command('key:generate', ['--force' => true]);
    $warnings[] = 'APP_KEY was generated NOW. Copy it from this server\'s .env into the '
        . '*_APP_KEY GitHub secret immediately — otherwise the next deploy regenerates '
        . 'it and all encrypted/session data becomes unreadable.';
}

// ── 4. The actual post-deploy tasks ─────────────────────────────────────
run_command('migrate', ['--force' => true]);
run_command('storage:link', ['--force' => true]);

// Probe whether public/storage actually serves files. cPanel hosts often
// block symlink traversal when FollowSymLinks is disabled; public/.htaccess
// carries a rewrite fallback that covers this case either way.
$symlinkOk = null;
try {
    $probeName = '.storage-probe-' . bin2hex(random_bytes(4));
    @file_put_contents(storage_path('app/public/' . $probeName), 'probe');

    if (is_file(storage_path('app/public/' . $probeName))) {
        clearstatcache(true);
        $symlinkOk = file_exists(public_path('storage/' . $probeName));
        @unlink(storage_path('app/public/' . $probeName));
    }

    if ($symlinkOk === false) {
        $warnings[] = 'public/storage symlink is NOT directly servable on this host '
            . '(FollowSymLinks disabled?). Requests fall back to the storage rewrite rule '
            . 'in public/.htaccess. If uploads still 404, ask cPanel support to enable FollowSymLinks.';
    }
} catch (\Throwable) {
    $symlinkOk = null;
}

// ── 5. Clear ALL caches BEFORE rebuilding ──────────────────────────────
//  Old config, route, and view caches must be wiped first so that
//  stale entries from the previous deploy don't linger.
run_command('config:clear');
run_command('route:clear');
run_command('view:clear');
run_command('cache:clear');

// Now rebuild fresh caches from the newly uploaded code.
// NOTE: route:cache is intentionally skipped — this project uses closure-based
// routes (e.g. the dashboard and cache-clear routes) which Laravel cannot
// serialize.  Route caching would throw "Routes must be using a serializable
// Closure" and mark the deploy as failed.
run_command('config:cache');
run_command('view:cache');

// ── 6. OPcache flush (multiple strategies for shared hosting) ───────────
//  opcache_reset() often fails on shared hosting because the hosting
//  provider restricts it via opcache.restrict_api or because PHP-FPM
//  workers each maintain their own OPcache instance.  We try several
//  approaches in order of effectiveness:
//
//  a) opcache_reset() — works when called from CLI or unrestricted web
//  b) opcache_invalidate() on individual files — more targeted
//  c) Touching compiled files — forces OPcache to re-read by mtime

$opcacheWorked = false;

if (function_exists('opcache_reset')) {
    $result = @opcache_reset();
    $opcacheWorked = ($result === true);
}

if (!$opcacheWorked && function_exists('opcache_invalidate')) {
    // Try invalidating specific high-impact cached files.
    $targets = [
        base_path('bootstrap/cache/config.php'),
        base_path('bootstrap/cache/routes-v7.php'),
        base_path('bootstrap/cache/routes.php'),
    ];
    foreach ($targets as $target) {
        if (is_file($target)) {
            @opcache_invalidate($target, true);
        }
    }
    // Also invalidate compiled views.
    $viewPath = storage_path('framework/views');
    if (is_dir($viewPath)) {
        foreach (glob($viewPath . '/*.php') as $compiledView) {
            @opcache_invalidate($compiledView, true);
        }
    }
}

// Last resort: touch all compiled PHP files so OPcache sees them as
// changed and re-reads them from disk.
$touchPaths = [
    base_path('bootstrap/cache'),
    storage_path('framework/views'),
];
foreach ($touchPaths as $dir) {
    if (is_dir($dir)) {
        foreach (glob($dir . '/*.php') as $phpFile) {
            @touch($phpFile);
        }
    }
}

if (!$opcacheWorked) {
    $warnings[] = 'opcache_reset() did not execute (restricted by hosting). '
        . 'File timestamps were touched as a fallback. If stale content '
        . 'persists, ask your host to disable OPcache or add your domain '
        . 'to the OPcache whitelist.';
}

$durationMs = (int) round((microtime(true) - $start) * 1000);

http_response_code($failed ? 500 : 200);
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status'      => $failed ? 'failed' : 'ok',
    'duration_ms' => $durationMs,
    'symlink_ok'  => $symlinkOk,
    'results'     => $results,
    'warnings'    => $warnings,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
