<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Module;
use App\Models\School;
use App\Models\Term;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileBootstrapController extends Controller
{
    /**
     * GET /api/mobile/bootstrap
     *
     * Returns everything the mobile client needs to render the role-based
     * UI on first paint: the authenticated user, their roles + flattened
     * permissions, enabled modules, the school profile, and the current
     * academic period (year + active term).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles.permissions');

        // Flatten all permission names from all roles.
        $permissions = $user->roles->flatMap->permissions
            ->pluck('permission_name')
            ->unique()
            ->values();

        // Roles as a simple array of { role_name } objects.
        $roles = $user->roles->map(fn ($r) => ['role_name' => $r->role_name]);

        // Active modules — the mobile client uses these to gate tabs.
        $modules = Module::where('is_active', true)
            ->orderBy('order')
            ->get(['key', 'name', 'is_core', 'is_active', 'order']);

        // School record (single-tenant: there is one school per installation).
        $school = School::first(['name']);

        // Current academic year + active term.
        $academicYear = AcademicYear::where('is_current', true)->first();
        $activeTerm = Term::where('status', 'active')->first();

        $period = null;
        if ($academicYear) {
            $period = [
                'academicYear' => $academicYear->name,
                'term'         => $activeTerm?->code ?? '',
                'termLabel'    => $activeTerm?->name ?? '',
                'startDate'    => $activeTerm?->start_date?->toDateString(),
                'endDate'      => $activeTerm?->end_date?->toDateString(),
            ];
        }

        // The visible sidebar menu computed by the same MenuService the web
        // renders. The mobile client mirrors this tree exactly, so no role can
        // gain or lose access compared to the web — permission, module, role
        // whitelist and owner_only rules all live in ONE place (config/menu.php
        // + MenuService), not duplicated in the app.
        $menu = collect(app(MenuService::class)->getVisibleMenu())
            ->map(fn ($item) => $this->serializeMenuNode($item))
            ->values();

        return response()->json([
            'user'        => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'user_type'  => $user->user_type,
                'is_active'  => $user->is_active,
            ],
            'roles'       => $roles,
            'permissions' => $permissions->values(),
            'modules'     => $modules,
            'school'      => $school ? ['name' => $school->name] : null,
            'period'      => $period,
            'menu'        => $menu,
        ]);
    }

    /**
     * Reduce a raw MenuService node to the fields the mobile client needs.
     * Section headers and nested sub-headers are preserved so the app can
     * reproduce the exact web grouping.
     *
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>
     */
    private function serializeMenuNode(array $node): array
    {
        if (isset($node['header'])) {
            return ['header' => $node['header']];
        }

        $out = [
            'key'   => $node['key'] ?? null,
            'label' => $node['label'] ?? '',
        ];

        if (isset($node['route'])) {
            $out['route'] = $node['route'];
        }

        if (isset($node['children'])) {
            $out['children'] = collect($node['children'])
                ->map(fn ($child) => $this->serializeMenuNode($child))
                ->values();
        }

        return $out;
    }
}
