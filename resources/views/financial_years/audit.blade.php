@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-5">
                    <div class="page-heading">
                        <div class="page-heading-icon indigo"><i class="fas fa-clock-rotate-left"></i></div>
                        <div>
                            <h1 class="page-heading-title">Audit History</h1>
                            <p class="page-heading-sub">{{ $financialYear->name }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-7 text-right">
                    <a href="{{ route('financial-years.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Financial Years
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card border-0 shadow-sm rounded-lg">
            <div class="card-body p-0">
                @if($logs->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-hourglass-half text-muted mb-3" style="font-size: 2.5rem;"></i>
                        <p class="text-muted font-weight-bold">No audit events recorded for this financial year yet.</p>
                    </div>
                @else
                    <div class="timeline-wrapper p-4">
                        @foreach($logs as $log)
                            <div class="audit-event">
                                <div class="audit-marker {{ strtolower($log->action) }}"></div>
                                <div class="audit-card">
                                    <div class="audit-card-head">
                                        <span class="badge badge-light px-3 py-2 font-weight-bold rounded-pill">{{ $log->action }}</span>
                                        <span class="audit-card-time">
                                            <i class="fas fa-clock mr-1"></i>{{ $log->created_at->format('M d, Y · H:i') }}
                                        </span>
                                    </div>
                                    <div class="audit-card-body">
                                        <p class="mb-2">
                                            <i class="fas fa-user text-indigo mr-1"></i>
                                            <strong>{{ $log->user->name ?? 'System' }}</strong>
                                            <span class="text-muted">performed this change</span>
                                        </p>
                                        @if(!empty($log->ip_address))
                                            <p class="small text-muted mb-3"><i class="fas fa-location-dot mr-1"></i>IP: {{ $log->ip_address }}</p>
                                        @endif

                                        <div class="code-diff">
                                            <div class="code-diff-header">
                                                <span class="text-muted text-uppercase">Changed Values</span>
                                            </div>
                                            <table class="table table-sm table-striped mb-0">
                                                <thead>
                                                    <tr>
                                                        <th class="border-top-0 key-col">Key</th>
                                                        <th class="border-top-0">Old</th>
                                                        <th class="border-top-0">New</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $oldValues = $log->old_values ?: [];
                                                        $newValues = $log->new_values ?: [];
                                                        $keys = array_merge(array_keys((array) $oldValues), array_keys((array) $newValues));
                                                        $keys = array_unique($keys);
                                                        $changed = [];
                                                        foreach ($keys as $key) {
                                                            $old = $oldValues[$key] ?? null;
                                                            $new = $newValues[$key] ?? null;
                                                            if ($old != $new) { $changed[$key] = [$old, $new]; }
                                                        }
                                                    @endphp
                                                    @forelse($changed as $key => [$old, $new])
                                                        <tr>
                                                            <td class="key-col text-dark font-weight-bold">{{ ucwords(str_replace('_', ' ', $key)) }}</td>
                                                            <td class="old-val"><span class="code-badge code-badge--old">{{ $old ?? '—' }}</span></td>
                                                            <td class="new-val"><span class="code-badge code-badge--new">{{ $new ?? '—' }}</span></td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center text-muted small py-3">No value changes detected in this event.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center px-4">
                        <small class="text-muted">{{ $logs->total() }} event{{ $logs->total() == 1 ? '' : 's' }}</small>
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .page-heading { display: flex; align-items: center; gap: 14px; }
        .page-heading-icon {
            width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
        }
        .page-heading-icon.indigo { background: #eef2ff; color: #4338ca; }
        .page-heading-title { font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2; }
        .page-heading-sub { color: #64748b; font-size: 0.85rem; font-weight: 600; margin: 2px 0 0; }

        .timeline-wrapper { max-width: 960px; margin: 0 auto; }

        .audit-event { display: flex; gap: 16px; margin-bottom: 18px; }
        .audit-marker {
            width: 14px; height: 14px; border-radius: 999px; flex-shrink: 0;
            margin-top: 18px; border: 3px solid #fff;
            box-shadow: 0 0 0 4px #f1f5f9;
        }
        .audit-marker.create { background: #059669; }
        .audit-marker.update { background: #4338ca; }
        .audit-marker.delete { background: #e11d48; }
        .audit-marker.other, .audit-marker.logout, .audit-marker.login { background: #d97706; }

        .audit-card { flex: 1; background: #fff; border: 1px solid #eef2f7; border-radius: 10px; overflow: hidden; }
        .audit-card-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 16px; background: #f8fafc; border-bottom: 1px solid #eef2f7;
        }
        .audit-card-time { font-size: 0.78rem; font-weight: 600; color: #94a3b8; }
        .audit-card-body { padding: 14px 16px; }

        .code-diff { border: 1px solid #eef2f7; border-radius: 8px; overflow: hidden; }
        .code-diff-header {
            font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em;
            padding: 8px 12px; background: #f8fafc; border-bottom: 1px solid #eef2f7;
            color: #94a3b8;
        }
        .key-col { width: 34%; }
        .code-badge {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 0.78rem; padding: 4px 10px; border-radius: 6px;
        }
        .code-badge--old { background: #fef2f2; color: #b91c1c; }
        .code-badge--new { background: #ecfdf5; color: #047857; }
        .old-val { color: #b91c1c; }
        .new-val { color: #047857; }
    </style>
@endsection
