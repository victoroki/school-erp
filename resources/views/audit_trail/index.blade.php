@extends('layouts.app')

@section('content')
@php
    $actionColors = [
        'create'  => ['bg' => '#ecfdf5', 'text' => '#047857', 'dot' => '#10b981'],
        'update'  => ['bg' => '#fffbeb', 'text' => '#b45309', 'dot' => '#f59e0b'],
        'delete'  => ['bg' => '#fff1f2', 'text' => '#be123c', 'dot' => '#f43f5e'],
        'login'   => ['bg' => '#eef2ff', 'text' => '#4338ca', 'dot' => '#4f46e5'],
        'logout'  => ['bg' => '#eef2ff', 'text' => '#4338ca', 'dot' => '#4f46e5'],
        'default' => ['bg' => '#f4f4f5', 'text' => '#52525b', 'dot' => '#a1a1aa'],
    ];

    $moduleIcons = [
        'Auth' => 'fa-fingerprint', 'User' => 'fa-user-cog', 'Staff' => 'fa-users',
        'Finance' => 'fa-coins', 'Fee Structure' => 'fa-receipt', 'Academic' => 'fa-chalkboard',
        'Exam' => 'fa-clipboard-check', 'Inventory' => 'fa-boxes', 'Library' => 'fa-book',
        'Hostel' => 'fa-hotel', 'Transport' => 'fa-bus', 'Communication' => 'fa-comments',
    ];
@endphp

<div class="at-wrap">
    {{-- ── Header ──────────────────────────────────────────────── --}}
    <div class="row align-items-end mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="at-hero-icon">
                    <i class="fas fa-fingerprint"></i>
                </div>
                <div>
                    <h1 class="at-title mb-0">System Audit Trail</h1>
                    <p class="at-sub mb-0">A complete, tamper-evident record of every action across the school</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('audit-trail.export', request()->query()) }}" class="at-btn at-btn-ghost me-2">
                <i class="fas fa-download me-2"></i> Export CSV
            </a>
            <button onclick="window.location.reload()" class="at-btn at-btn-ghost">
                <i class="fas fa-sync-alt me-2"></i> Refresh
            </button>
        </div>
    </div>

    @include('flash::message')

    {{-- ── Summary stats ───────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="at-card at-stat">
                <div class="at-stat-top">
                    <div class="at-stat-icon at-si-indigo"><i class="fas fa-fingerprint"></i></div>
                    <span class="at-stat-delta">all-time</span>
                </div>
                <div class="at-stat-value">{{ number_format($totalEvents) }}</div>
                <div class="at-stat-label">Events Recorded</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="at-card at-stat">
                <div class="at-stat-top">
                    <div class="at-stat-icon at-si-emerald"><i class="fas fa-calendar-day"></i></div>
                    <span class="at-stat-delta">today</span>
                </div>
                <div class="at-stat-value">{{ number_format($eventsToday) }}</div>
                <div class="at-stat-label">Events Today</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="at-card at-stat">
                <div class="at-stat-top">
                    <div class="at-stat-icon at-si-amber"><i class="fas fa-user-astronaut"></i></div>
                    <span class="at-stat-delta">unique</span>
                </div>
                <div class="at-stat-value">{{ number_format($uniqueExecutors) }}</div>
                <div class="at-stat-label">Unique Executors</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="at-card at-stat">
                <div class="at-stat-top">
                    <div class="at-stat-icon at-si-rose"><i class="fas fa-bolt"></i></div>
                    <span class="at-stat-delta">busiest</span>
                </div>
                <div class="at-stat-value at-stat-truncate" title="{{ $topModule ?? 'No activity yet' }}">{{ $topModule ?? '—' }}</div>
                <div class="at-stat-label">Most Active Module</div>
            </div>
        </div>
    </div>

    {{-- ── Overview panels ─────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        {{-- Most active modules --}}
        <div class="col-xl-6">
            <div class="at-card at-panel h-100">
                <div class="at-panel-head">
                    <div>
                        <h3 class="at-panel-title">Most Active Modules</h3>
                        <p class="at-panel-sub">Where the system sees the most traffic</p>
                    </div>
                    <span class="at-chip at-chip-muted">{{ $moduleStats->count() }} modules</span>
                </div>
                <div class="at-panel-body">
                    @forelse($moduleStats as $i => $mod)
                        <div class="at-mod-row">
                            <div class="at-mod-label">
                                <div class="at-mod-icon"><i class="fas {{ $moduleIcons[$mod['module']] ?? 'fa-th' }}"></i></div>
                                <span class="fw-600 text-slate">{{ $mod['module'] }}</span>
                            </div>
                            <div class="at-mod-track">
                                <div class="at-bar-fill" style="width: {{ $mod['total'] / $maxModule * 100 }}%; animation-delay: {{ 60 + $i * 40 }}ms;"></div>
                            </div>
                            <span class="at-mod-count">{{ number_format($mod['total']) }}</span>
                        </div>
                    @empty
                        <p class="at-empty">No module activity recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Top executors --}}
        <div class="col-md-6 col-xl-3">
            <div class="at-card at-panel h-100">
                <div class="at-panel-head">
                    <div>
                        <h3 class="at-panel-title">Top Executors</h3>
                        <p class="at-panel-sub">Users making the most changes</p>
                    </div>
                </div>
                <div class="at-panel-body">
                    @forelse($topExecutors as $i => $exec)
                        <div class="at-exec-row" style="animation-delay: {{ 80 + $i * 45 }}ms;">
                            <div class="at-avatar" style="background: {{ $actionColors['default']['bg'] }}; color: #52525b;">
                                {{ strtoupper(substr($exec['name'], 0, 1)) }}
                            </div>
                            <div class="at-exec-meta">
                                <span class="at-exec-name">{{ $exec['name'] }}</span>
                                <span class="at-exec-count">{{ number_format($exec['total']) }} action{{ $exec['total'] === 1 ? '' : 's' }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="at-empty">No executor activity yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Action mix --}}
        <div class="col-md-6 col-xl-3">
            <div class="at-card at-panel h-100">
                <div class="at-panel-head">
                    <div>
                        <h3 class="at-panel-title">Action Mix</h3>
                        <p class="at-panel-sub">How events are distributed</p>
                    </div>
                </div>
                <div class="at-panel-body">
                    @forelse($actionStats as $i => $act)
                        @php
                            $key = strtolower($act['action']);
                            $style = $actionColors[$key] ?? $actionColors['default'];
                            $pct = $totalEvents > 0 ? round($act['total'] / $totalEvents * 100) : 0;
                        @endphp
                        <div class="at-action-chip" style="animation-delay: {{ 80 + $i * 40 }}ms;">
                            <span class="at-dot" style="background: {{ $style['dot'] }};"></span>
                            <span class="fw-600 text-slate">{{ $act['action'] }}</span>
                            <span class="ms-auto fw-bold" style="color: {{ $style['text'] }};">{{ $pct }}%</span>
                        </div>
                    @empty
                        <p class="at-empty">No action data yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filters ─────────────────────────────────────────────── --}}
    <div class="at-card at-panel mb-4">
        <div class="at-panel-head">
            <div>
                <h3 class="at-panel-title"><i class="fas fa-filter me-2 text-muted"></i> Filter Logs</h3>
                <p class="at-panel-sub">Narrow the feed by module, actor, time or record</p>
            </div>
            @if(request()->hasAny(['module', 'action', 'user', 'record_id', 'from', 'to']))
                <a href="{{ route('audit-trail.index') }}" class="at-btn at-btn-ghost at-btn-sm">
                    <i class="fas fa-times me-1"></i> Clear
                </a>
            @endif
        </div>
        <form method="GET" action="{{ route('audit-trail.index') }}" class="at-panel-body">
            <div class="row g-2 align-items-end">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="at-field-label">Module</label>
                    <select name="module" class="form-select form-select-sm">
                        <option value="">All modules</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}" @selected(request('module') === $module)>{{ $module }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="at-field-label">Action</label>
                    <select name="action" class="form-select form-select-sm">
                        <option value="">All actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="at-field-label">User</label>
                    <input type="text" name="user" value="{{ request('user') }}" class="form-control form-control-sm" placeholder="Name...">
                </div>
                <div class="col-lg-1 col-md-4 col-sm-6">
                    <label class="at-field-label">Record #</label>
                    <input type="text" name="record_id" value="{{ request('record_id') }}" class="form-control form-control-sm" placeholder="#">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="at-field-label">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <label class="at-field-label">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
                </div>
                <div class="col-lg-1 col-md-4 col-sm-6">
                    <button type="submit" class="at-btn at-btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Apply
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Activity feed ───────────────────────────────────────── --}}
    <div class="at-card at-panel">
        <div class="at-panel-head">
            <div>
                <h3 class="at-panel-title"><i class="fas fa-stream me-2 text-muted"></i> Activity Feed</h3>
                <p class="at-panel-sub">
                    Showing {{ $logs->firstItem() ? number_format($logs->firstItem()) . '–' . number_format($logs->lastItem()) : '0' }}
                    of {{ number_format($logs->total()) }} events
                </p>
            </div>
            <span class="at-chip at-chip-indigo">{{ number_format($logs->total()) }} events</span>
        </div>
        <div class="at-table-wrap">
            <table class="table at-table mb-0">
                <thead>
                    <tr class="at-thead">
                        <th class="ps-4 py-3">Timestamp</th>
                        <th class="py-3">Executor</th>
                        <th class="py-3">Resource</th>
                        <th class="py-3">Action</th>
                        <th class="py-3">Reference</th>
                        <th class="py-3 text-end pe-4">Client IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $oldJson = $log->old_values
                                ? json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                                : null;
                            $newJson = $log->new_values
                                ? json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                                : null;
                            $actionKey = strtolower($log->action);
                            $actionStyle = $actionColors[$actionKey] ?? $actionColors['default'];
                        @endphp
                        <tr class="at-row">
                            <td class="ps-4 py-3">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-slate">{{ $log->created_at->format('M d, Y') }}</span>
                                    <small class="text-muted font-monospace">{{ $log->created_at->format('H:i:s') }}</small>
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="at-avatar-sm" style="background: #eef2ff; color: #4f46e5;">
                                        {{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <span class="fw-600 text-slate">{{ $log->user->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="at-badge">
                                    <i class="fas {{ $moduleIcons[$log->module] ?? 'fa-th' }} me-1"></i>
                                    {{ str_replace('-', ' ', $log->module) }}
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="at-action-pill" style="background: {{ $actionStyle['bg'] }}; color: {{ $actionStyle['text'] }};">
                                    {{ strtoupper($log->action) }}
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="font-monospace text-muted small">#{{ $log->record_id }}</span>
                            </td>
                            <td class="py-3 text-end pe-4">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <code class="small text-muted">{{ $log->ip_address }}</code>
                                    @if($oldJson || $newJson)
                                        <button type="button" class="at-btn at-btn-icon at-toggle" data-target="#detail-{{ $log->id }}"
                                                aria-label="Toggle details" aria-expanded="false">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @if($oldJson || $newJson)
                        <tr class="at-detail" id="detail-{{ $log->id }}">
                            <td colspan="6" class="p-0">
                                <div class="at-detail-inner">
                                    <div class="row g-3">
                                        @if($oldJson)
                                        <div class="col-md-6">
                                            <h6 class="at-diff-label"><i class="fas fa-arrow-left me-1"></i> Before</h6>
                                            <pre class="at-diff mb-0">{{ $oldJson }}</pre>
                                        </div>
                                        @endif
                                        @if($newJson)
                                        <div class="{{ $oldJson ? 'col-md-6' : 'col-12' }}">
                                            <h6 class="at-diff-label"><i class="fas fa-arrow-right me-1"></i> After</h6>
                                            <pre class="at-diff mb-0">{{ $newJson }}</pre>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="at-empty-state">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <h6 class="fw-bold text-slate mt-3">No activity logs found</h6>
                                <p class="small text-muted mb-0">
                                    @if(request()->hasAny(['module', 'action', 'user', 'record_id', 'from', 'to']))
                                        No events match the current filters — try widening your search.
                                    @else
                                        System events will appear here as they occur.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="at-panel-foot">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<style>
:root {
    --at-indigo: #4f46e5; --at-indigo-deep: #4338ca;
    --at-emerald: #10b981; --at-amber: #f59e0b; --at-rose: #f43f5e;
    --at-slate: #18181b; --at-muted: #71717a; --at-border: #e4e4e7;
    --at-bg: #f7f7f8; --at-surface: #ffffff;
    --at-ease: cubic-bezier(0.16, 1, 0.3, 1);
    --at-shadow-sm: 0 1px 2px rgb(24 24 27 / 0.04);
    --at-shadow-md: 0 8px 24px -8px rgb(24 24 27 / 0.12);
}

.at-wrap { padding: 1.75rem 1.5rem 2.5rem; background: var(--at-bg); min-height: 100vh; }
.at-title { font-size: 1.625rem; font-weight: 800; color: var(--at-slate); letter-spacing: -0.03em; }
.at-sub { color: var(--at-muted); font-size: 0.875rem; font-weight: 500; }
.at-hero-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: var(--at-indigo); background: #eef2ff; box-shadow: var(--at-shadow-sm); }
.fw-600 { font-weight: 600; }

/* ── Cards & panels ─────────────────────────────────────────── */
.at-card { background: var(--at-surface); border: 1px solid var(--at-border); border-radius: 16px; box-shadow: var(--at-shadow-sm); }
.at-panel { overflow: hidden; }
.at-panel-head { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--at-border); }
.at-panel-title { font-size: 0.9375rem; font-weight: 750; color: var(--at-slate); margin: 0; letter-spacing: -0.01em; }
.at-panel-sub { font-size: 0.6875rem; color: var(--at-muted); margin: .125rem 0 0; }
.at-panel-body { padding: 1rem 1.25rem; }
.at-panel-foot { padding: .875rem 1.25rem; border-top: 1px solid var(--at-border); background: #fcfcfd; }

/* ── Stat cards ─────────────────────────────────────────────── */
.at-stat { padding: 1.125rem 1.25rem; opacity: 0; transform: translateY(10px); animation: atRise 420ms var(--at-ease) forwards; }
.at-stat:nth-child(1) { animation-delay: 40ms; }
.at-stat:nth-child(2) { animation-delay: 90ms; }
.at-stat:nth-child(3) { animation-delay: 140ms; }
.at-stat:nth-child(4) { animation-delay: 190ms; }
@keyframes atRise { to { opacity: 1; transform: translateY(0); } }
.at-stat-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: .625rem; }
.at-stat-icon { width: 32px; height: 32px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .875rem; }
.at-si-indigo { background: #eef2ff; color: var(--at-indigo); }
.at-si-emerald { background: #ecfdf5; color: var(--at-emerald); }
.at-si-amber { background: #fffbeb; color: var(--at-amber); }
.at-si-rose { background: #fff1f2; color: var(--at-rose); }
.at-stat-delta { font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--at-muted); background: #f4f4f5; padding: .2rem .5rem; border-radius: 20px; }
.at-stat-value { font-size: 1.75rem; font-weight: 800; color: var(--at-slate); line-height: 1.1; letter-spacing: -0.03em; }
.at-stat-truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.at-stat-label { font-size: .6875rem; font-weight: 650; color: var(--at-muted); margin-top: .25rem; text-transform: uppercase; letter-spacing: .04em; }

/* ── Module bars ────────────────────────────────────────────── */
.at-mod-row { display: grid; grid-template-columns: 1fr minmax(0, 2fr) auto; align-items: center; gap: .75rem; padding: .5rem 0; }
.at-mod-label { display: flex; align-items: center; gap: .625rem; min-width: 0; }
.at-mod-icon { width: 26px; height: 26px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .6875rem; color: var(--at-indigo); background: #eef2ff; flex-shrink: 0; }
.at-mod-track { height: 6px; border-radius: 999px; background: #f0f0f1; overflow: hidden; }
.at-bar-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--at-indigo), #818cf8); transform: scaleX(0); transform-origin: left; animation: atGrow 600ms var(--at-ease) forwards; }
@keyframes atGrow { to { transform: scaleX(1); } }
.at-mod-count { font-size: .75rem; font-weight: 750; color: var(--at-muted); font-variant-numeric: tabular-nums; }

/* ── Executors ──────────────────────────────────────────────── */
.at-exec-row { display: flex; align-items: center; gap: .75rem; padding: .5rem 0; opacity: 0; transform: translateY(6px); animation: atRise 360ms var(--at-ease) forwards; }
.at-avatar { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .8125rem; flex-shrink: 0; }
.at-exec-meta { display: flex; flex-direction: column; min-width: 0; }
.at-exec-name { font-size: .8125rem; font-weight: 700; color: var(--at-slate); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.at-exec-count { font-size: .6875rem; color: var(--at-muted); }

/* ── Action mix ─────────────────────────────────────────────── */
.at-action-chip { display: flex; align-items: center; gap: .5rem; padding: .4375rem .625rem; border-radius: 10px; background: #fafafa; border: 1px solid var(--at-border); margin-bottom: .4375rem; font-size: .75rem; opacity: 0; transform: translateY(6px); animation: atRise 320ms var(--at-ease) forwards; }
.at-action-chip:last-child { margin-bottom: 0; }
.at-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

.at-chip { font-size: .625rem; font-weight: 750; text-transform: uppercase; letter-spacing: .05em; padding: .3rem .625rem; border-radius: 999px; white-space: nowrap; }
.at-chip-muted { background: #f4f4f5; color: var(--at-muted); }
.at-chip-indigo { background: #eef2ff; color: var(--at-indigo); }

/* ── Fields & buttons ───────────────────────────────────────── */
.at-field-label { display: block; font-size: .625rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--at-muted); margin-bottom: .375rem; }
.at-btn { display: inline-flex; align-items: center; justify-content: center; gap: .375rem; border: 1px solid transparent; border-radius: 10px; padding: .5625rem 1rem; font-size: .8125rem; font-weight: 700; text-decoration: none !important; cursor: pointer; transition: transform 160ms var(--at-ease), background-color 150ms ease, border-color 150ms ease, color 150ms ease, box-shadow 200ms var(--at-ease); }
.at-btn:active { transform: scale(0.97); }
.at-btn-sm { padding: .375rem .75rem; font-size: .75rem; }
.at-btn-icon { width: 28px; height: 28px; padding: 0; border-radius: 8px; background: transparent; color: var(--at-muted); }
.at-btn-ghost { background: var(--at-surface); border-color: var(--at-border); color: var(--at-slate); }
.at-btn-primary { background: var(--at-indigo); color: #fff; box-shadow: 0 4px 12px rgb(79 70 229 / 0.25); }
@media (hover: hover) and (pointer: fine) {
    .at-btn-ghost:hover { background: #fafafa; border-color: #d4d4d8; }
    .at-btn-primary:hover { background: var(--at-indigo-deep); }
    .at-btn-icon:hover { background: #f4f4f5; color: var(--at-slate); }
}

/* ── Table ──────────────────────────────────────────────────── */
.at-table-wrap { overflow-x: auto; }
.at-table { background: var(--at-surface); }
.at-table .at-thead th { font-size: .625rem; font-weight: 750; text-transform: uppercase; letter-spacing: .08em; color: var(--at-muted); background: #fcfcfd; border-bottom: 1px solid var(--at-border); }
.at-table .at-row td { border-bottom: 1px solid #f0f0f1; vertical-align: middle; }
.at-table .at-row:last-child td { border-bottom: none; }
@media (hover: hover) and (pointer: fine) {
    .at-table .at-row:hover td { background: #fafbfc; }
}
.at-badge { display: inline-flex; align-items: center; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #52525b; background: #f4f4f5; padding: .3rem .625rem; border-radius: 8px; white-space: nowrap; }
.at-action-pill { display: inline-block; font-size: .6875rem; font-weight: 800; letter-spacing: .05em; padding: .3rem .625rem; border-radius: 8px; white-space: nowrap; }
.at-avatar-sm { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .6875rem; flex-shrink: 0; }

/* ── Detail expansion ───────────────────────────────────────── */
.at-detail { display: none; }
.at-detail.open { display: table-row; }
.at-detail-inner { padding: 1.25rem 1.5rem; background: #fafafa; border-bottom: 1px solid var(--at-border); opacity: 0; transform: translateY(-4px); transition: opacity 180ms var(--at-ease), transform 180ms var(--at-ease); }
.at-detail.open .at-detail-inner { opacity: 1; transform: translateY(0); }
.at-diff-label { font-size: .625rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: var(--at-muted); margin-bottom: .5rem; }
.at-diff { background: #18181b; color: #e4e4e7; border-radius: 10px; padding: .875rem 1rem; font-size: .75rem; line-height: 1.55; max-height: 260px; overflow: auto; white-space: pre-wrap; word-break: break-word; margin: 0; }

.at-empty { font-size: .8125rem; color: var(--at-muted); text-align: center; padding: 1rem 0; margin: 0; }
.at-empty-state { width: 56px; height: 56px; margin: 0 auto; border-radius: 16px; background: #f4f4f5; color: #a1a1aa; display: flex; align-items: center; justify-content: center; font-size: 1.375rem; }
.font-monospace { font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; }

@media (prefers-reduced-motion: reduce) {
    .at-stat, .at-exec-row, .at-action-chip { animation: none; opacity: 1; transform: none; }
    .at-bar-fill { animation: none; transform: scaleX(1); }
    .at-detail-inner { transition: none; }
    .at-btn { transition: none; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.at-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = document.querySelector(btn.dataset.target);
            if (!target) return;
            var isOpen = target.classList.contains('open');
            target.classList.toggle('open');
            btn.setAttribute('aria-expanded', String(!isOpen));
            btn.querySelector('i').classList.toggle('fa-chevron-down');
            btn.querySelector('i').classList.toggle('fa-chevron-up');
        });
    });
});
</script>
@endsection
