-@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- HEADER --}}
    <div class="dash-header d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="dash-heading"><i class="fas fa-user-friends ic-lead" style="color: var(--indigo);"></i>Teacher Management</h1>
            <p class="dash-sub mb-0">Manage teaching staff records</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a class="btn-dash btn-primary-dash shadow-sm" href="{{ route('teacher-onboarding.create') }}">
                <i class="fas fa-user-plus"></i> New Teacher
            </a>
        </div>
    </div>

    @include('flash::message')

    {{-- SEARCH & QUICK FILTERS --}}
    <div class="dash-panel mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('teacher-management.index') }}" class="form-row">
                <div class="form-group col-md-8 mb-3 mb-md-0">
                    <label class="filter-label">Search Faculty Member</label>
                    <div class="filter-input-wrap">
                        <i class="fas fa-search filter-icon"></i>
                        <input type="text" name="search" class="filter-input" placeholder="Name, employee ID, TSC number, or email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="form-group col-md-4 mb-0">
                    <label class="filter-label">Employment Status</label>
                    <div class="filter-field">
                        <i class="fas fa-filter"></i>
                        <select name="status" onchange="this.form.submit()">
                            <option value="">- All Statuses -</option>
                            @foreach(['active' => 'Active', 'on_leave' => 'On Leave', 'suspended' => 'Suspended', 'terminated' => 'Terminated', 'resigned' => 'Resigned', 'retired' => 'Retired'] as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TEACHER GRID --}}
    @if($teachers->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <h4 class="empty-title">No Teachers Found</h4>
            <p class="empty-desc">No staff match your search. Start by onboarding a new teacher.</p>
            <a href="{{ route('teacher-onboarding.create') }}" class="btn-dash btn-primary-dash mt-2">Onboard Teacher</a>
        </div>
    @else
        <div class="row">
            @foreach($teachers as $teacher)
                @php
                    $initials = strtoupper(mb_substr($teacher->first_name ?? '?', 0, 1)) . strtoupper(mb_substr($teacher->last_name ?? '', 0, 1));
                    $avatarColors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#f6a821', '#e9506d'];
                    $avatarColor = $avatarColors[$teacher->staff_id % count($avatarColors)];
                @endphp
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="staff-card">
                        <div class="sc-body">
                            <div class="d-flex align-items-start justify-content-between sc-head">
                                <div class="d-flex align-items-center">
                                    <div class="sc-avatar" style="background: {{ $avatarColor }}">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <h5 class="sc-name mb-0">{{ $teacher->full_name }}</h5>
                                        <div class="sc-meta">
                                            <i class="fas fa-id-card"></i>{{ $teacher->employee_number ?: 'NO-ID' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="action-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="More actions">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                        <li><a class="dropdown-item" href="{{ route('teacher-management.show', $teacher->staff_id) }}"><i class="far fa-eye dd-icon"></i> View Profile</a></li>
                                        <li><a class="dropdown-item" href="{{ route('teacher-management.edit', $teacher->staff_id) }}"><i class="far fa-edit dd-icon"></i> Edit Details</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            {!! Form::open(['route' => ['teacher-management.destroy', $teacher->staff_id], 'method' => 'delete']) !!}
                                                {!! Form::button('<span class="dd-icon dd-danger"><i class="far fa-trash-alt"></i></span><span class="text-rose">Delete Teacher</span>', ['type' => 'submit', 'class' => 'dropdown-item', 'onclick' => "return confirm('Are you sure you want to delete this teacher record?')"]) !!}
                                            {!! Form::close() !!}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mb-2 d-flex flex-wrap">
                                <span class="badge-soft bg-indigo-light text-indigo">{{ $teacher->designation ?: 'Teacher' }}</span>
                                <span class="badge-soft bg-slate-light text-slate ml-2">{{ $teacher->employment_status ?: 'Status N/A' }}</span>
                            </div>

                            <div class="sc-info-grid">
                                <div class="sc-info-item">
                                    <i class="fas fa-id-badge"></i>
                                    <span>TSC: {{ $teacher->tsc_number ?: 'Not provided' }}</span>
                                </div>
                                <div class="sc-info-item">
                                    <i class="fas fa-sitemap"></i>
                                    <span>{{ $teacher->department->name ?? 'No Department' }}</span>
                                </div>
                                <div class="sc-info-item">
                                    <i class="fas fa-phone"></i>
                                    <span>{{ $teacher->phone_primary ?: 'No Phone' }}</span>
                                </div>
                                <div class="sc-info-item">
                                    <i class="fas fa-envelope"></i>
                                    <span class="text-truncate">{{ $teacher->work_email ?: 'No Email' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="sc-footer">
                            <a href="{{ route('teacher-management.show', $teacher->staff_id) }}" class="btn-dash btn-ghost flex-grow-1 py-1">Profile</a>
                            <a href="{{ route('teacher-management.edit', $teacher->staff_id) }}" class="btn-dash btn-ghost px-3 py-1"><i class="far fa-edit"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- PAGINATION --}}
        @if($teachers->hasPages())
            <div class="pagination-panel mt-2">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="pagination-info mb-2 mb-md-0">
                        Showing <strong>{{ $teachers->firstItem() }}</strong>-<strong>{{ $teachers->lastItem() }}</strong>
                        of <strong>{{ $teachers->total() }}</strong> records
                    </div>
                    <div class="pagination-links">
                        {!! $teachers->appends(request()->query())->links() !!}
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<style>
    :root {
        --indigo: #4f46e5;
        --indigo-dark: #4338ca;
        --indigo-light: #eef2ff;
        --rose: #f43f5e; --rose-light: #fff1f2;
        --slate: #64748b; --slate-light: #f1f5f9;
        --text: #0f172a;
        --muted: #64748b;
        --border: #e2e8f0;
        --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
    }

    .dash-wrap { padding: 2rem 1.5rem; }
    .dash-heading { font-size: 1.65rem; font-weight: 800; color: var(--text); letter-spacing: -0.03em; margin-bottom: 0.15rem; }
    .dash-sub { font-size: 0.9rem; color: var(--muted); font-weight: 500; }

    .dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 16px !important; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important; }

    /* - Icon spacing guarantees -
       Never rely on spacing utilities for icon gaps: enforce
       them structurally so icons can never touch words. */
    .ic-lead { margin-right: 12px; }
    .btn-dash i { margin-right: 7px; }
    .btn-dash i:only-child { margin-right: 0; }
    .sc-meta i { margin-right: 7px; }
    .dropdown-item i, .dd-icon { display: inline-block; min-width: 18px; margin-right: 9px; }
    .card-footer small i, .empty-desc i { margin-right: 6px; }

    /* Filters */
    .filter-label { font-size: 0.68rem; font-weight: 800; color: var(--slate); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem; display: block; }
    .filter-input-wrap { position: relative; }
    .filter-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 0.85rem; pointer-events: none; z-index: 5; }
    .filter-input {
        width: 100%; min-height: 44px; border-radius: 12px; border: 1px solid var(--border); background-color: #fff;
        padding: 0.7rem 2.4rem 0.7rem 2.55rem; font-size: 0.875rem; font-weight: 600; color: var(--text);
        transition: border-color 200ms var(--ease-out), box-shadow 200ms var(--ease-out); -webkit-appearance: none; -moz-appearance: none; appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2364748b' d='M1.41 0 6 4.58 10.59 0 12 1.41l-6 6-6-6z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
    }
    input.filter-input { background-image: none; padding-right: 1rem; }
    .filter-input:focus { border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); outline: none; }

    /* Teacher cards */
    .staff-card { background: #fff; border: 1px solid var(--border); border-radius: 16px; overflow: hidden; transition: transform 250ms var(--ease-out), box-shadow 250ms var(--ease-out), border-color 250ms var(--ease-out); height: 100%; display: flex; flex-direction: column; }
    .staff-card:hover { transform: translateY(-3px); border-color: #c7d2fe; box-shadow: 0 10px 24px rgba(79, 70, 229, 0.1) !important; }

    .sc-body { padding: 1.25rem 1.25rem 0.9rem; flex-grow: 1; }
    .sc-head { margin-bottom: 0.9rem; }
    .sc-avatar {
        width: 46px; height: 46px; min-width: 46px; border-radius: 13px; margin-right: 13px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 0.95rem; overflow: hidden;
    }
    .sc-name { font-size: 0.98rem; font-weight: 800; color: var(--text); letter-spacing: -0.01em; }
    .sc-meta { margin-top: 4px; font-size: 0.75rem; color: var(--muted); font-weight: 600; }

    .badge-soft { border-radius: 20px; font-weight: 800; font-size: 0.68rem; padding: 0.3rem 0.75rem; letter-spacing: 0.05em; text-transform: uppercase; display: inline-block; }
    .bg-indigo-light { background: var(--indigo-light); } .bg-slate-light { background: var(--slate-light); }
    .text-indigo { color: var(--indigo); } .text-slate { color: var(--slate); } .text-rose { color: var(--rose) !important; }

    .sc-info-grid { display: grid; grid-template-columns: 1fr; gap: 0.5rem; margin-top: 0.85rem; padding-top: 0.85rem; border-top: 1px solid #f6f8fb; }
    .sc-info-item { display: flex; align-items: center; font-size: 0.82rem; color: var(--muted); font-weight: 500; }
    .sc-info-item i { width: 16px; min-width: 16px; text-align: center; font-size: 0.78rem; opacity: 0.65; margin-right: 11px; }
    .sc-info-item span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .action-btn { width: 30px; height: 30px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; color: var(--slate); border: 1px solid var(--border); background: #fff; transition: all 150ms ease; font-size: 0.8rem; }
    .action-btn:hover { background: var(--slate-light); color: var(--text); }
    .action-btn i { margin-right: 0 !important; }

    .sc-footer { padding: 0.75rem; border-top: 1px solid #f1f5f9; background: #fcfcfd; display: flex; }
    .sc-footer .btn-ghost + .btn-ghost { margin-left: 8px; }

    /* Dropdown */
    .dropdown-item { font-size: 0.83rem; font-weight: 600; padding: 0.5rem 1rem; color: var(--text); display: flex; align-items: center; white-space: nowrap; }
    .dropdown-item:hover { background-color: var(--slate-light); }

    /* Buttons */
    .btn-dash { display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 0.875rem; font-weight: 700; transition: all 200ms var(--ease-out); text-decoration: none !important; padding: 0.65rem 1.25rem; }
    .btn-primary-dash { background: var(--indigo); color: #fff !important; border: 1px solid var(--indigo); }
    .btn-primary-dash:hover { background: var(--indigo-dark); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.28); }
    .btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text) !important; }
    .btn-ghost:hover { background: #f8fafc; border-color: #cbd5e1; }

    /* Empty state */
    .empty-state { background: #fff; border: 1px dashed var(--border); border-radius: 18px; padding: 4rem 2rem; text-align: center; }
    .empty-icon { width: 74px; height: 74px; margin: 0 auto 1.25rem; border-radius: 20px; background: var(--slate-light); color: var(--slate); font-size: 1.6rem; display: flex; align-items: center; justify-content: center; }
    .empty-title { font-weight: 800; color: var(--text); letter-spacing: -0.02em; }
    .empty-desc { color: var(--muted); max-width: 420px; margin: 0 auto 0.5rem; }

    /* Pagination */
    .pagination-panel { background: #fff; padding: 1rem 1.5rem; border-radius: 16px; border: 1px solid var(--border); }
    .pagination-info { font-size: 0.875rem; color: var(--muted); }
    .pagination-info strong { color: var(--text); font-weight: 800; }
    .pagination { margin: 0; }
    .pagination .page-link { border-radius: 10px !important; margin: 0 3px; border: 1px solid var(--border); color: var(--slate); font-weight: 700; font-size: 0.85rem; min-width: 34px; text-align: center; padding: 0.35rem 0.6rem; }
    .pagination .page-item.active .page-link { background: var(--indigo); border-color: var(--indigo); color: #fff; }
    .pagination .page-link:focus { box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); }
    .pagination .disabled .page-link { opacity: 0.45; }

    @media (max-width: 768px) {
        .dash-wrap { padding: 1.25rem 1rem; }
        .sc-body { padding: 1rem 1rem 0.8rem; }
    }
</style>
@endsection
