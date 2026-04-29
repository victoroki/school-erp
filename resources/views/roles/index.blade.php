@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">User Roles</h1>
            <p class="dash-sub">Define responsibility groups and access blueprints</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <a class="btn-dash btn-primary-dash" href="{{ route('roles.create') }}">
                <i class="fas fa-plus me-1"></i> Create New Role
            </a>
        </div>
    </div>

    @include('flash::message')

    {{-- ② ROLES LIST --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="dash-panel">
                @include('roles.table')
            </div>
        </div>
    </div>
</div>

<style>
/* ── Emil Kowalski Utility Suite ── */
:root {
    --blue: #3b82f6;
    --indigo: #4f46e5;
    --emerald: #10b981;
    --slate: #64748b;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 1rem; }
.dash-heading { font-size: 1.375rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.813rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden; }

/* Buttons */
.btn-dash { 
    display: inline-flex; align-items: center; justify-content: center; padding: .4rem .875rem; border-radius: 8px; font-size: .813rem; font-weight: 600; 
    transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer;
}
.btn-primary-dash { background: var(--indigo); color: #fff; border-color: var(--indigo); }
.btn-primary-dash:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
.btn-ghost { background: transparent; color: var(--muted); border-color: var(--border); }
.btn-ghost:hover { background: #f8fafc; color: var(--text); border-color: #cbd5e1; }

/* Table Styling */
.table { margin-bottom: 0; }
.table thead th { 
    background: #f8fafc; border-bottom: 1px solid var(--border); font-size: .688rem; font-weight: 800; 
    text-transform: uppercase; color: var(--slate); letter-spacing: 0.05em; padding: .25rem .5rem;
}
.table tbody td { padding: .25rem .5rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; border-top: 0; }
.table tbody tr:last-child td { border-bottom: 0; }
.table-hover tbody tr:hover { background-color: #f8fafc; }

.role-name { font-weight: 700; color: var(--text); font-size: .875rem; display: block; }
.role-desc { font-size: .75rem; color: var(--muted); margin-top: .125rem; display: block; }

.badge-perm { background: #eff6ff; color: #3b82f6; font-size: .625rem; font-weight: 800; padding: .15rem .45rem; border-radius: 6px; }

/* Action Buttons */
.action-btn { 
    width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; 
    border-radius: 8px; color: var(--muted); transition: all 150ms ease; border: 1px solid transparent; background: transparent; font-size: .813rem;
}
.action-btn:hover { background: #f1f5f9; color: var(--text); border-color: #e2e8f0; }
.btn-delete:hover { background: #fee2e2; color: #ef4444; border-color: #fecaca; }
</style>
@endsection
