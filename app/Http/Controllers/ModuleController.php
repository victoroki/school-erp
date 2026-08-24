<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\Module;
use App\Services\ModuleManager;
use Illuminate\Http\Request;
use Flash;

class ModuleController extends AppBaseController
{
    public function __construct(private ModuleManager $moduleManager)
    {
        // Enabling/disabling modules (the paid feature switches) is reserved
        // for the platform Owner, mirroring the audit-trail pattern rather
        // than riding on a module permission that could be renamed or revoked.
        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            if ($user && $user->isOwner()) {
                return $next($request);
            }

            abort(403);
        });
    }

    /**
     * Display the module registry with per-module enable toggles.
     */
    public function index()
    {
        $modules = Module::query()->orderBy('order')->get();

        $enabledCount = $modules->where('is_active', true)->count();
        $coreCount    = $modules->where('is_core', true)->count();

        return view('modules.index', compact('modules', 'enabledCount', 'coreCount'));
    }

    /**
     * Enable or disable a module.
     */
    public function toggle(Request $request, Module $module)
    {
        $this->authorize('toggle', $module);

        $oldData = $module->toArray();

        try {
            $this->moduleManager->toggle($module->key, $request->boolean('is_active'));
        } catch (\DomainException $e) {
            Flash::error($e->getMessage());

            return redirect()->back();
        }

        $updated = Module::find($module->id);

        AuditTrail::log('Module', 'UPDATE', $module->id, $oldData, $updated->toArray());

        $state = $request->boolean('is_active') ? 'enabled' : 'disabled';
        Flash::success("{$module->name} module {$state}.");

        return redirect()->back();
    }
}


