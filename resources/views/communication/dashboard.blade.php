@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- Header --}}
    <div class="row align-items-center mb-5">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-indigo-light text-indigo">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div>
                    <h1 class="dash-heading mb-0">Communication Hub</h1>
                    <p class="dash-sub mb-0">Manage automated and direct messaging channels</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('communication.compose') }}" class="btn-dash btn-indigo">
                <i class="fas fa-plus me-2"></i> Compose Message
            </a>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-blue-light text-blue"><i class="fas fa-sms"></i></div>
                <div class="stat-content">
                    <h3 class="stat-val">{{ $stats['total_sms_sent'] }}</h3>
                    <p class="stat-label">SMS Sent</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-emerald-light text-emerald"><i class="fas fa-envelope"></i></div>
                <div class="stat-content">
                    <h3 class="stat-val">{{ $stats['total_email_sent'] }}</h3>
                    <p class="stat-label">Emails Sent</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-amber-light text-amber"><i class="fas fa-file-alt"></i></div>
                <div class="stat-content">
                    <h3 class="stat-val">{{ $stats['total_templates'] }}</h3>
                    <p class="stat-label">Templates</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-rose-light text-rose"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-content">
                    <h3 class="stat-val">{{ $stats['failed_messages'] }}</h3>
                    <p class="stat-label">Failed</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Recent Messages --}}
        <div class="col-xl-8">
            <div class="dash-panel h-100 shadow-sm border-0">
                <div class="dash-panel-header px-4 py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-850"><i class="fas fa-history me-2 text-muted"></i> Recent Activity</h5>
                    <a href="{{ route('communication.history.index') }}" class="btn-dash btn-ghost py-1 px-3">History</a>
                </div>
                <div class="dash-panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="bg-light-soft text-muted small text-uppercase fw-bold letter-spacing-1">
                                    <th class="ps-4 py-3">Channel</th>
                                    <th class="py-3">Content Preview</th>
                                    <th class="py-3">Recipients</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3 text-end pe-4">Sent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recent_messages'] as $message)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="icon-sm {{ $message->message_type == 'SMS' ? 'bg-blue-light text-blue' : 'bg-emerald-light text-emerald' }} rounded-circle">
                                                    <i class="fas {{ $message->message_type == 'SMS' ? 'fa-sms' : 'fa-envelope' }}"></i>
                                                </div>
                                                <span class="fw-600">{{ $message->message_type }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-slate text-truncate d-inline-block" style="max-width: 250px;">
                                                {{ $message->subject ?? Str::limit($message->content, 40) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark">{{ $message->recipient_count }}</span>
                                                <small class="text-muted">{{ str_replace('_', ' ', $message->recipient_type) }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = [
                                                    'Sent' => 'bg-emerald-light text-emerald',
                                                    'Failed' => 'bg-rose-light text-rose',
                                                    'Sending' => 'bg-amber-light text-amber'
                                                ][$message->status] ?? 'bg-slate-light text-slate';
                                            @endphp
                                            <span class="badge {{ $statusClass }} rounded-pill px-3 py-1 fw-bold small">
                                                {{ strtoupper($message->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="text-muted small">{{ $message->created_at->diffForHumans() }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No messages found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Popular Templates --}}
        <div class="col-xl-4">
            <div class="dash-panel h-100 shadow-sm border-0">
                <div class="dash-panel-header px-4 py-3 border-bottom">
                    <h5 class="mb-0 fw-850"><i class="fas fa-th-large me-2 text-muted"></i> Top Templates</h5>
                </div>
                <div class="dash-panel-body p-4">
                    <div class="template-list d-flex flex-column gap-3">
                        @foreach($stats['popular_sms_templates'] as $template)
                            <a href="{{ route('communication.compose') }}?template_id={{ $template->id }}&type=SMS" class="template-item text-decoration-none">
                                <div class="d-flex align-items-center gap-3 p-3 rounded-lg border">
                                    <div class="icon-md bg-amber-light text-amber"><i class="fas fa-sms"></i></div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold text-dark">{{ $template->title }}</h6>
                                        <small class="text-muted">{{ $template->usage_count }} transmissions</small>
                                    </div>
                                    <i class="fas fa-chevron-right text-muted small"></i>
                                </div>
                            </a>
                        @endforeach
                        @foreach($stats['popular_email_templates'] as $template)
                            <a href="{{ route('communication.compose') }}?template_id={{ $template->id }}&type=Email" class="template-item text-decoration-none">
                                <div class="d-flex align-items-center gap-3 p-3 rounded-lg border">
                                    <div class="icon-md bg-emerald-light text-emerald"><i class="fas fa-envelope"></i></div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold text-dark">{{ $template->title }}</h6>
                                        <small class="text-muted">{{ $template->usage_count }} transmissions</small>
                                    </div>
                                    <i class="fas fa-chevron-right text-muted small"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --blue: #3b82f6; --blue-light: #eff6ff;
    --slate: #64748b; --slate-light: #f1f5f9;
    --text: #0f172a; --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 2.5rem; background: #fafafa; min-height: 100vh; }
.dash-heading { font-size: 1.875rem; font-weight: 850; color: var(--text); letter-spacing: -0.04em; }
.dash-sub { color: var(--muted); font-size: 0.9375rem; font-weight: 500; }

.stat-card { background: #fff; padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border); display: flex; align-items: center; gap: 1.25rem; transition: transform 200ms var(--ease-out); }
.stat-card:hover { transform: translateY(-4px); }
.stat-icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.stat-val { font-size: 1.5rem; font-weight: 850; color: var(--text); margin-bottom: 0.125rem; }
.stat-label { font-size: 0.8125rem; font-weight: 600; color: var(--muted); margin: 0; text-uppercase: uppercase; letter-spacing: 0.025em; }

.dash-panel { background: #fff; border-radius: 20px; overflow: hidden; border: 1px solid var(--border); }
.fw-850 { font-weight: 850; }
.fw-600 { font-weight: 600; }

.icon-box { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.icon-sm { width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; }
.icon-md { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.125rem; }

.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: 0.75rem 1.5rem; border-radius: 12px; font-size: 0.875rem; font-weight: 750; transition: all 200ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; }
.btn-indigo { background: var(--indigo); color: #fff; }
.btn-indigo:hover { background: #4338ca; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.2); }
.btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text); }
.btn-ghost:hover { background: var(--slate-light); }

.template-item .rounded-lg { transition: all 200ms var(--ease-out); }
.template-item:hover .rounded-lg { border-color: var(--indigo) !important; background: var(--indigo-light); }

.text-indigo { color: var(--indigo); }
.bg-indigo-light { background-color: var(--indigo-light); }
.text-emerald { color: var(--emerald); }
.bg-emerald-light { background-color: var(--emerald-light); }
.text-amber { color: var(--amber); }
.bg-amber-light { background-color: var(--amber-light); }
.text-rose { color: var(--rose); }
.bg-rose-light { background-color: var(--rose-light); }
.text-blue { color: var(--blue); }
.bg-blue-light { background-color: var(--blue-light); }
.bg-light-soft { background-color: #fcfcfd; }
</style>
@endsection
