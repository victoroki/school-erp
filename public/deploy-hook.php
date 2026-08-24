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

// Caches last so they always reflect the freshly uploaded code.
run_command('config:cache');
run_command('view:cache');

// Best-effort: flush OPcache so PHP stops serving stale bytecode.
if (function_exists('opcache_reset')) {
    @opcache_reset();
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
