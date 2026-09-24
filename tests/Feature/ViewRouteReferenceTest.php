<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Every route() name a view asks for must actually exist.
 *
 * A literal route('name') for an unregistered name throws
 * RouteNotFoundException the moment that view renders — a 500 on a page that
 * looks fine in the source. This was found as one reported defect in
 * assessment_types and turned out to be a class of 45 occurrences, so it is
 * checked mechanically rather than by eye.
 *
 * The one kind of match deliberately ignored is a method call: $request->route('token')
 * and \Request::route('token') are not the route() helper, and matching them produced
 * two false positives during the sweep that found this class of defect.
 *
 * There is no allowlist. The 45 references this found were all in unreachable files —
 * the retired assessment_types feature and the pre-consolidation RBAC scaffold — and
 * those files have been removed, so every remaining reference must now resolve.
 */
class ViewRouteReferenceTest extends TestCase
{
    public function test_no_reachable_view_references_an_unregistered_route(): void
    {
        $registered = [];
        foreach (Route::getRoutes() as $route) {
            if ($name = $route->getName()) {
                $registered[$name] = true;
            }
        }

        $this->assertNotEmpty($registered, 'No routes registered — the lint would be vacuous.');

        $dangling = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $relative = str_replace('\\', '/', $file->getRelativePathname());

            foreach (file($file->getPathname(), FILE_IGNORE_NEW_LINES) as $index => $line) {
                if (! str_contains($line, 'route(')) {
                    continue;
                }

                // A view that checks the route exists before using it is safe, and
                // that is a legitimate pattern rather than a defect.
                if (str_contains($line, 'Route::has')) {
                    continue;
                }

                if (! preg_match_all('/(\S{0,3})route\(\s*[\'"]([A-Za-z0-9_.\-]+)[\'"]/', $line, $matches, PREG_SET_ORDER)) {
                    continue;
                }

                foreach ($matches as $match) {
                    $prefix = $match[1];

                    // ->route() is an instance call and ::route() a static/facade
                    // call — e.g. \Request::route('token') in the password reset
                    // view. Neither is the route() helper, and matching them
                    // produced two false positives.
                    if (str_contains($prefix, '>') || str_contains($prefix, '::') || str_contains($prefix, '$')) {
                        continue;
                    }

                    if (! isset($registered[$match[2]])) {
                        $dangling[] = $relative . ':' . ($index + 1) . '  route(\'' . $match[2] . '\')';
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $dangling,
            "These views reference route names that are not registered, which throws "
                . "RouteNotFoundException when rendered:\n  " . implode("\n  ", $dangling)
        );
    }
}
