@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">Job Positions</h1>
            <p class="dash-sub">Manage organizational roles, responsibilities, and hierarchy</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <a class="btn-dash btn-ghost px-3" href="#">
                    <i class="fas fa-file-export me-2"></i> Export
                </a>
                <a class="btn-dash btn-primary-dash shadow-sm px-3" href="{{ route('job-positions.create') }}">
                    <i class="fas fa-plus me-2"></i> Create Position
                </a>
            </div>
        </div>
    </div>

    @include('flash::message')

    {{-- ② STATS OVERVIEW --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card bg-white border shadow-sm p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-indigo-light text-indigo">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Positions</div>
                        <div class="stat-value">{{ $jobPositions->total() }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card bg-white border shadow-sm p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-emerald-light text-emerald">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="stat-label">Active Roles</div>
                        <div class="stat-value">{{ $jobPositions->where('is_active', 1)->count() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ③ MAIN TABLE --}}
    <div class="dash-panel border-0 shadow-sm mb-4">
        <div class="dash-panel-body p-0">
            @include('job_positions.table')
        </div>
    </div>
</div>

<style>
/* ── Job Positions Impeccable Suite ── */
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

.dash-wrap { padding: 1.5rem; background: #fafafa; min-height: 100vh; }
.dash-heading { font-size: 1.5rem; font-weight: 850; color: var(--text); letter-spacing: -0.04em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.875rem; color: var(--muted); font-weight: 500; }

.stat-card { border-radius: 12px; }
.stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
.stat-label { font-size: 0.75rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; }
.stat-value { font-size: 1.25rem; font-weight: 850; color: var(--text); }

.dash-panel { background: #fff; border-radius: 16px; overflow: hidden; }

.btn-dash { display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 0.813rem; font-weight: 750; transition: all 200ms var(--ease-out); text-decoration: none !important; }
.btn-primary-dash { background: var(--indigo); color: #fff; padding: 0.625rem 1.25rem; }
.btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text); padding: 0.5rem 1rem; }
.btn-ghost:hover { background: var(--slate-light); border-color: #cbd5e1; }
</style>
@endsection
