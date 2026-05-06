@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">Staff Directory</h1>
            <p class="dash-sub">Manage school faculty and administrative personnel</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <a class="btn-dash btn-ghost px-3" href="#">
                    <i class="fas fa-filter me-2"></i> Advanced Filters
                </a>
                <a class="btn-dash btn-primary-dash shadow-sm px-3" href="{{ route('staff.create') }}">
                    <i class="fas fa-plus me-2"></i> Add Personnel
                </a>
            </div>
        </div>
    </div>

    @include('flash::message')

    {{-- ② SEARCH & QUICK FILTERS --}}
    <div class="dash-panel mb-4 border-0 shadow-sm">
        <div class="dash-panel-body p-3">
            <form method="GET" action="{{ route('staff.index') }}" class="row g-3">
                <div class="col-md-8">
                    <div class="filter-input-wrap">
                        <i class="fas fa-search filter-icon"></i>
                        <input type="text" name="search" class="filter-input ps-5" placeholder="Search by name, employee ID, or email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="staff_type" class="filter-input" onchange="this.form.submit()">
                        <option value="">— All Staff Types —</option>
                        <option value="teaching" {{ request('staff_type') == 'teaching' ? 'selected' : '' }}>Teaching</option>
                        <option value="non-teaching" {{ request('staff_type') == 'non-teaching' ? 'selected' : '' }}>Non-Teaching</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    {{-- ③ STAFF GRID --}}
    @if($staff->isEmpty())
        <div class="empty-state bg-white border rounded-3 p-5 text-center">
            <div class="empty-icon mb-3">
                <i class="fas fa-users-slash fs-1 text-muted opacity-25"></i>
            </div>
            <h4 class="fw-bold">No Staff Found</h4>
            <p class="text-muted">Start by adding your first employee to the directory.</p>
            <a href="{{ route('staff.create') }}" class="btn-dash btn-primary-dash">Add New Staff</a>
        </div>
    @else
        <div class="row g-3">
            @foreach($staff as $member)
                @php
                    $initials = strtoupper(substr($member->first_name, 0, 1)) . strtoupper(substr($member->last_name, 0, 1));
                    $typeColor = $member->staff_type == 'teaching' ? 'indigo' : 'emerald';
                @endphp
                <div class="col-xl-4 col-md-6">
                    <div class="staff-card border shadow-sm">
                        <div class="sc-body p-3">
                            <div class="d-flex align-items-start justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="sc-avatar bg-{{ $typeColor }}-light text-{{ $typeColor }}">
                                        @if($member->photo_url)
                                            <img src="{{ $member->photo_url }}" alt="Photo">
                                        @else
                                            {{ $initials }}
                                        @endif
                                    </div>
                                    <div>
                                        <h5 class="sc-name mb-0">{{ $member->full_name }}</h5>
                                        <div class="sc-meta fs-xs text-muted fw-600">
                                            <i class="fas fa-id-card me-1"></i> {{ $member->employee_number ?: 'NO-ID' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="action-btn" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li><a class="dropdown-item" href="{{ route('staff.show', $member->staff_id) }}"><i class="far fa-eye me-2"></i> View Profile</a></li>
                                        <li><a class="dropdown-item" href="{{ route('staff.edit', $member->staff_id) }}"><i class="far fa-edit me-2"></i> Edit Details</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            {!! Form::open(['route' => ['staff.destroy', $member->staff_id], 'method' => 'delete']) !!}
                                                {!! Form::button('<i class="far fa-trash-alt me-2 text-rose"></i> <span class="text-rose">Delete Staff</span>', ['type' => 'submit', 'class' => 'dropdown-item', 'onclick' => "return confirm('Are you sure you want to delete this staff record?')"]) !!}
                                            {!! Form::close() !!}
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="sc-details mb-3">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge-soft bg-{{ $typeColor }}-light text-{{ $typeColor }} fs-xs px-2 py-1 fw-800 rounded text-uppercase">
                                        {{ $member->staff_type }}
                                    </span>
                                    <span class="badge-soft bg-slate-light text-slate fs-xs px-2 py-1 fw-800 rounded text-uppercase">
                                        {{ $member->employment_status ?: 'Status N/A' }}
                                    </span>
                                </div>
                                <div class="sc-info-grid">
                                    <div class="sc-info-item">
                                        <i class="fas fa-sitemap"></i>
                                        <span>{{ $member->department->name ?? 'No Department' }}</span>
                                    </div>
                                    <div class="sc-info-item">
                                        <i class="fas fa-user-tag"></i>
                                        <span>{{ $member->jobPosition->name ?? 'No Position' }}</span>
                                    </div>
                                    <div class="sc-info-item">
                                        <i class="fas fa-phone"></i>
                                        <span>{{ $member->phone_primary ?: 'No Phone' }}</span>
                                    </div>
                                    <div class="sc-info-item">
                                        <i class="fas fa-envelope"></i>
                                        <span class="text-truncate">{{ $member->work_email ?: 'No Email' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sc-footer p-2 border-top bg-light-soft d-flex gap-1">
                            <a href="{{ route('staff.show', $member->staff_id) }}" class="btn-dash btn-ghost flex-grow-1 py-1">Profile</a>
                            <a href="{{ route('staff.edit', $member->staff_id) }}" class="btn-dash btn-ghost px-2 py-1"><i class="far fa-edit"></i></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ④ PAGINATION --}}
        @if($staff->hasPages())
            <div class="p-4 mt-4 border-0 shadow-sm bg-white rounded-3 d-flex align-items-center justify-content-between">
                <p class="text-muted small mb-0">Showing <strong>{{ $staff->firstItem() }}</strong> to <strong>{{ $staff->lastItem() }}</strong> of <strong>{{ $staff->total() }}</strong> records</p>
                {!! $staff->appends(request()->query())->links() !!}
            </div>
        @endif
    @endif
</div>

<style>
/* ── Staff Directory Custom Styling ── */
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --slate: #64748b; --slate-light: #f1f5f9;
    --text: #0f172a; --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 1.5rem; background: #fafafa; min-height: 100vh; }
.dash-heading { font-size: 1.5rem; font-weight: 850; color: var(--text); letter-spacing: -0.04em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.875rem; color: var(--muted); font-weight: 500; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; }
.filter-input-wrap { position: relative; }
.filter-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 0.9rem; pointer-events: none; }
.filter-input { width: 100%; border-radius: 10px; border: 1px solid var(--border); background: #fff; padding: 0.625rem 1rem; font-size: 0.875rem; font-weight: 600; color: var(--text); transition: all 200ms var(--ease-out); appearance: none; }
.filter-input:focus { border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); outline: none; }

/* Staff Cards */
.staff-card { background: #fff; border-radius: 14px; overflow: hidden; transition: all 300ms var(--ease-out); height: 100%; display: flex; flex-direction: column; }
.staff-card:hover { transform: translateY(-4px); border-color: var(--indigo); box-shadow: 0 12px 24px rgba(0,0,0,0.06) !important; }

.sc-avatar { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 850; font-size: 1rem; overflow: hidden; flex-shrink: 0; }
.sc-avatar img { width: 100%; height: 100%; object-fit: cover; }
.sc-name { font-size: 0.938rem; font-weight: 800; color: var(--text); letter-spacing: -0.01em; }
.sc-meta { margin-top: 1px; }

.sc-info-grid { display: grid; grid-template-columns: 1fr; gap: 0.5rem; margin-top: 0.75rem; }
.sc-info-item { display: flex; align-items: center; gap: 0.75rem; font-size: 0.813rem; color: var(--muted); font-weight: 500; }
.sc-info-item i { width: 14px; text-align: center; font-size: 0.75rem; opacity: 0.6; }
.sc-info-item span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.action-btn { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--slate); border: 1px solid transparent; background: transparent; transition: all 150ms ease; }
.action-btn:hover { background: var(--slate-light); color: var(--text); }

.badge-soft { border-radius: 6px; }
.bg-light-soft { background: #fcfcfd; }
.fs-xs { font-size: 0.7rem; }
.fw-600 { font-weight: 600; }
.fw-800 { font-weight: 800; }

.btn-dash { display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 0.813rem; font-weight: 750; transition: all 200ms var(--ease-out); text-decoration: none !important; }
.btn-primary-dash { background: var(--indigo); color: #fff; padding: 0.625rem 1.25rem; }
.btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text); padding: 0.5rem 1rem; }
.btn-ghost:hover { background: var(--slate-light); border-color: #cbd5e1; }

.dropdown-item { font-size: 0.813rem; font-weight: 600; padding: 0.5rem 1rem; color: var(--text); }
.dropdown-item:hover { background-color: var(--slate-light); }
.text-rose { color: var(--rose) !important; }
</style>
@endsection
