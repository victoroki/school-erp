@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="mod-hero-icon">
                    <i class="fas fa-puzzle-piece"></i>
                </div>
                <div>
                    <h1 class="dash-heading mb-0">Modules</h1>
                    <p class="dash-sub mb-0">Control which features are enabled for this school installation</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <span class="mod-chip mod-chip-indigo">
                <i class="fas fa-check-circle me-1"></i> {{ $enabledCount }} enabled
            </span>
            <span class="mod-chip mod-chip-muted">
                <i class="fas fa-lock me-1"></i> {{ $coreCount }} core
            </span>
        </div>
    </div>

    @include('flash::message')

    {{-- ② CORE MODULES NOTE --}}
    <div class="mod-note mb-4">
        <div class="mod-note-icon"><i class="fas fa-info-circle"></i></div>
        <div>
            <strong class="text-slate">Disabling a module hides its features.</strong>
            <span class="text-muted">Core modules (Dashboard, user management, students, academics and administration) can also be toggled by privileged users. Disabling a module only hides its sidebar menu and blocks its routes — it never deletes or touches the module's data.</span>
        </div>
    </div>

    {{-- ③ MODULES TABLE --}}
    <div class="dash-panel">
        <div class="dash-panel-body p-0">
            <div class="mod-table-wrap">
                <table class="table mod-table mb-0">
                    <thead>
                        <tr class="mod-thead">
                            <th class="ps-4 py-3">Module</th>
                            <th class="py-3">Route Prefix</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modules as $module)
                            <tr class="mod-row">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="mod-mod-icon"><i class="fas {{ $module->icon ?? 'fa-th' }}"></i></div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-slate">{{ $module->name }}</span>
                                            <small class="text-muted font-monospace">{{ $module->key }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <code class="small text-muted">{{ $module->route_prefix ?? '—' }}</code>
                                </td>
                                <td class="py-3">
                                    @if($module->is_core)
                                        <span class="mod-badge mod-badge-muted"><i class="fas fa-lock me-1"></i> Core</span>
                                    @else
                                        <span class="mod-badge mod-badge-opt">Optional</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    @if($module->is_active)
                                        <span class="mod-badge mod-badge-on"><i class="fas fa-circle me-1"></i> Enabled</span>
                                    @else
                                        <span class="mod-badge mod-badge-off"><i class="fas fa-circle me-1"></i> Disabled</span>
                                    @endif
                                </td>
                                <td class="py-3 text-end pe-4">
                                    @if($module->is_active)
                                        <form action="{{ route('modules.toggle', $module->key) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="is_active" value="0">
                                            <button type="submit" class="btn-dash btn-ghost btn-danger-ghost" title="Disable {{ $module->name }}">
                                                <i class="fas fa-ban me-1"></i> Disable
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('modules.toggle', $module->key) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="is_active" value="1">
                                            <button type="submit" class="btn-dash btn-primary-dash" title="Enable {{ $module->name }}">
                                                <i class="fas fa-power-off me-1"></i> Enable
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Modules screen — matches the dash style used across settings screens ── */
:root {
    --blue: #3b82f6; --indigo: #4f46e5; --emerald: #10b981;
    --slate: #64748b; --text: #0f172a; --muted: #64748b; --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}
.dash-wrap { padding: 1.75rem 1.5rem 2.5rem; }
.dash-heading { font-size: 1.375rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.813rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }
.text-slate { color: var(--text); }
.mod-hero-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: var(--indigo); background: #eef2ff; }
.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
.dash-panel-body { padding: 1rem; }

.mod-chip { display: inline-flex; align-items: center; font-size: .6875rem; font-weight: 750; text-transform: uppercase; letter-spacing: .05em; padding: .3rem .625rem; border-radius: 999px; }
.mod-chip-indigo { background: #eef2ff; color: var(--indigo); }
.mod-chip-muted { background: #f4f4f5; color: var(--muted); }

.mod-note { display: flex; gap: .875rem; padding: 1rem 1.125rem; border-radius: 12px; background: #eff6ff; border: 1px solid #dbeafe; align-items: flex-start; }
.mod-note-icon { width: 32px; height: 32px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 9px; background: #fff; color: var(--blue); }
.mod-note strong { font-size: .8125rem; display: block; }
.mod-note span.text-muted { font-size: .75rem; display: block; }

.mod-table-wrap { overflow-x: auto; }
.mod-table .mod-thead th { font-size: .625rem; font-weight: 750; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); background: #fcfcfd; border-bottom: 1px solid var(--border); }
.mod-table .mod-row td { border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.mod-table .mod-row:last-child td { border-bottom: none; }
@media (hover: hover) and (pointer: fine) { .mod-table .mod-row:hover td { background: #fafbfc; } }

.mod-mod-icon { width: 32px; height: 32px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .875rem; color: var(--indigo); background: #eef2ff; flex-shrink: 0; }

.mod-badge { display: inline-flex; align-items: center; font-size: .6875rem; font-weight: 700; padding: .3rem .625rem; border-radius: 8px; white-space: nowrap; }
.mod-badge-on { background: #ecfdf5; color: #047857; }
.mod-badge-off { background: #fef2f2; color: #b91c1c; }
.mod-badge-muted { background: #f4f4f5; color: #52525b; }
.mod-badge-opt { background: #fffbeb; color: #b45309; }
.mod-badge i { font-size: .375rem; vertical-align: middle; }

.btn-dash {
    display: inline-flex; align-items: center; justify-content: center; padding: .4rem .875rem; border-radius: 8px;
    font-size: .813rem; font-weight: 600; transition: all 150ms var(--ease-out);
    border: 1px solid transparent; text-decoration: none !important; cursor: pointer;
}
.btn-primary-dash { background: var(--indigo); color: #fff; border-color: var(--indigo); }
.btn-primary-dash:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
.btn-ghost { background: transparent; color: var(--muted); border-color: var(--border); }
.btn-ghost:hover { background: #f8fafc; color: var(--text); border-color: #cbd5e1; }
.btn-danger-ghost:hover { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }

.mod-locked { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; background: #f4f4f5; color: #a1a1aa; font-size: .8125rem; }
</style>
@endsection
