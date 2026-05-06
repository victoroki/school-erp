@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- Header Section --}}
    <div class="row align-items-center mb-5">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-slate-light text-slate">
                    <i class="fas fa-fingerprint"></i>
                </div>
                <div>
                    <h1 class="dash-heading mb-0">System Audit Trail</h1>
                    <p class="dash-sub mb-0">Monitoring and security logs of all administrative actions</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <button onclick="window.location.reload()" class="btn-dash btn-ghost">
                <i class="fas fa-sync-alt me-2"></i> Refresh Logs
            </button>
        </div>
    </div>

    @include('flash::message')

    <div class="dash-panel shadow-sm border-0">
        <div class="dash-panel-header px-4 py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-850"><i class="fas fa-stream me-2 text-muted"></i> Activity Feed</h5>
            <div class="d-flex gap-2">
                <input type="text" class="form-control form-control-sm" placeholder="Filter by module..." style="width: 200px;">
            </div>
        </div>
        <div class="dash-panel-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light-soft text-muted small text-uppercase fw-bold letter-spacing-1">
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
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark">{{ $log->created_at->format('M d, Y') }}</span>
                                        <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm bg-indigo-light text-indigo rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                            {{ substr($log->user->name ?? 'S', 0, 1) }}
                                        </div>
                                        <span class="fw-600 text-slate">{{ $log->user->name ?? 'System' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-slate-light text-slate px-2 py-1 rounded-sm text-uppercase small fw-bold">
                                        {{ str_replace('-', ' ', $log->module) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $actionColors = [
                                            'create' => 'text-emerald fw-bold',
                                            'update' => 'text-amber fw-bold',
                                            'delete' => 'text-rose fw-bold',
                                            'login' => 'text-indigo fw-bold'
                                        ];
                                    @endphp
                                    <span class="{{ $actionColors[strtolower($log->action)] ?? 'text-dark fw-bold' }}">
                                        {{ strtoupper($log->action) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="font-monospace text-muted small">#{{ $log->record_id }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <code class="small text-muted">{{ $log->ip_address }}</code>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted opacity-50 mb-3">
                                        <i class="fas fa-clipboard-list fa-3x"></i>
                                    </div>
                                    <h6 class="fw-bold">No activity logs found</h6>
                                    <p class="small text-muted mb-0">System events will appear here as they occur.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="dash-panel-footer px-4 py-3 border-top bg-light-soft">
            {{ $logs->links() }}
        </div>
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --slate: #64748b; --slate-light: #f1f5f9;
    --text: #0f172a; --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 2.5rem; background: #fafafa; min-height: 100vh; }
.dash-heading { font-size: 1.875rem; font-weight: 850; color: var(--text); letter-spacing: -0.04em; }
.dash-sub { color: var(--muted); font-size: 0.9375rem; font-weight: 500; }

.icon-box { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.dash-panel { background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid var(--border); }
.fw-850 { font-weight: 850; }
.fw-600 { font-weight: 600; }
.letter-spacing-1 { letter-spacing: 0.05em; }

.avatar-sm { width: 32px; height: 32px; font-size: 0.75rem; }
.bg-light-soft { background-color: #fcfcfd; }

.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: 0.625rem 1.25rem; border-radius: 10px; font-size: 0.875rem; font-weight: 750; transition: all 200ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; }
.btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text); }
.btn-ghost:hover { background: var(--slate-light); }

.text-indigo { color: var(--indigo); }
.bg-indigo-light { background-color: var(--indigo-light); }
.text-emerald { color: var(--emerald); }
.text-amber { color: var(--amber); }
.text-rose { color: var(--rose); }
.text-slate { color: var(--slate); }
.bg-slate-light { background-color: var(--slate-light); }

.font-monospace { font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; }
</style>
@endsection
