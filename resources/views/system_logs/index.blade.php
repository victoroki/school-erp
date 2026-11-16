@extends('layouts.app')

@section('content')
@php
    $levelStyles = [
        'Debug'     => ['bg' => '#f4f4f5', 'text' => '#52525b'],
        'Info'      => ['bg' => '#eff6ff', 'text' => '#1d4ed8'],
        'Notice'    => ['bg' => '#eff6ff', 'text' => '#1d4ed8'],
        'Warning'   => ['bg' => '#fffbeb', 'text' => '#b45309'],
        'Error'     => ['bg' => '#fef2f2', 'text' => '#b91c1c'],
        'Critical'  => ['bg' => '#fef2f2', 'text' => '#b91c1c'],
        'Alert'     => ['bg' => '#fef2f2', 'text' => '#b91c1c'],
        'Emergency' => ['bg' => '#fef2f2', 'text' => '#b91c1c'],
    ];
@endphp

<div class="container-fluid">
    {{-- ── Header ──────────────────────────────────────────────── --}}
    <div class="row align-items-end mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-1"><i class="fas fa-bug text-danger me-2"></i>System Logs</h1>
            <p class="text-muted mb-0">Application error log — platform owner only</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <button onclick="window.location.reload()" class="btn btn-outline-secondary">
                <i class="fas fa-sync-alt me-2"></i> Refresh
            </button>
        </div>
    </div>

    @include('flash::message')

    @unless($logExists)
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No application log file found at <code>storage/logs/laravel.log</code> yet.
        </div>
    @endunless

    {{-- ── Filters ─────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('system-logs.index') }}" class="card card-body mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <select name="level" class="form-select">
                    <option value="">All levels</option>
                    @foreach ($levels as $l)
                        <option value="{{ $l }}" @selected($level === $l)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Search message or environment...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-2"></i>Filter</button>
                <a href="{{ route('system-logs.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>

    {{-- ── Entries ─────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><strong>{{ number_format($totalShown) }}</strong> entries (latest {{ number_format(min(512, $totalShown)) }} KB of log scanned)</span>
        </div>
        <div class="list-group list-group-flush">
            @forelse ($logs as $log)
                @php $style = $levelStyles[$log['level']] ?? $levelStyles['Info']; @endphp
                <div class="list-group-item">
                    <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                        <span class="badge rounded-pill" style="background: {{ $style['bg'] }}; color: {{ $style['text'] }};">
                            {{ $log['level'] }}
                        </span>
                        <small class="text-muted">{{ $log['datetime'] }} · {{ $log['env'] }}</small>
                    </div>
                    <div class="text-break" style="font-family: monospace; font-size: .875rem;">
                        {{ \Illuminate\Support\Str::limit($log['message'], 500) }}
                    </div>
                    @if (trim($log['stack']) !== '')
                        <a class="btn btn-sm btn-link px-0 mt-1" data-bs-toggle="collapse" href="#log-stack-{{ $loop->index }}">
                            Stack trace
                        </a>
                        <div class="collapse" id="log-stack-{{ $loop->index }}">
                            <pre class="bg-light p-2 rounded mb-0 text-break" style="font-size: .75rem; max-height: 320px; overflow: auto;"><code>{{ trim($log['stack']) }}</code></pre>
                        </div>
                    @endif
                </div>
            @empty
                <div class="list-group-item text-center text-muted py-5">
                    <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                    {{ $logExists ? 'No matching log entries.' : 'Nothing to show yet.' }}
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-center">
        {{ $logs->links() }}
    </div>
</div>
@endsection
