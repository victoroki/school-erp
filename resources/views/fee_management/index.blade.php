@extends('layouts.app')

@section('content')
<div class="fee-mgmt-wrap">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-indigo-light text-indigo">
                <i class="fas fa-money-check-alt"></i>
            </div>
            <div>
                <h1 class="page-title mb-0">Fee Management</h1>
                <p class="page-subtitle mb-0">Track and collect student fees</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('fees.dashboard') }}" class="btn-ghost-custom">
                <i class="fas fa-chart-pie me-1"></i> Dashboard
            </a>
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="metrics-grid mb-4">
        <div class="metric-card">
            <div class="metric-icon bg-indigo-light text-indigo">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div class="metric-content">
                <span class="metric-label">Total Receivable</span>
                <span class="metric-value">KSh {{ number_format($metrics['total_receivable'], 0) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-emerald-light text-emerald">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="metric-content">
                <span class="metric-label">Total Collected</span>
                <span class="metric-value text-emerald">KSh {{ number_format($metrics['total_collected'], 0) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-amber-light text-amber">
                <i class="fas fa-clock"></i>
            </div>
            <div class="metric-content">
                <span class="metric-label">Total Pending</span>
                <span class="metric-value text-amber">KSh {{ number_format($metrics['total_pending'], 0) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon {{ $metrics['collection_rate'] >= 75 ? 'bg-emerald-light text-emerald' : ($metrics['collection_rate'] >= 50 ? 'bg-amber-light text-amber' : 'bg-rose-light text-rose') }}">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="metric-content">
                <span class="metric-label">Collection Rate</span>
                <span class="metric-value {{ $metrics['collection_rate'] >= 75 ? 'text-emerald' : ($metrics['collection_rate'] >= 50 ? 'text-amber' : 'text-rose') }}">{{ $metrics['collection_rate'] }}%</span>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-bar mb-4">
        <form action="{{ route('fee-management.index') }}" method="GET" class="filter-form">
            <div class="filter-field">
                <label for="filter-status">Status</label>
                <select name="status" id="filter-status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>
            <div class="filter-field">
                <label for="filter-class">Class</label>
                <select name="class_id" id="filter-class" class="filter-select">
                    <option value="">All Classes</option>
                    @foreach($classes as $id => $name)
                        <option value="{{ $id }}" {{ request('class_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field filter-search">
                <label for="filter-search">Search</label>
                <div class="search-input-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" id="filter-search" class="search-input" placeholder="Name or admission no" value="{{ request('search') }}">
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary-custom">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['status', 'class_id', 'search']))
                    <a href="{{ route('fee-management.index') }}" class="btn-ghost-custom">
                        <i class="fas fa-times me-1"></i> Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table Section --}}
    <div class="table-section">
        <div class="table-header">
            <h2 class="table-title">Student Fee Records</h2>
            <div class="table-actions">
                <div class="export-dropdown">
                    <button type="button" class="btn-ghost-custom btn-export" id="export-toggle">
                        <i class="fas fa-download me-1"></i> Export
                        <i class="fas fa-chevron-down ms-1" style="font-size: 0.6rem;"></i>
                    </button>
                    <div class="export-menu" id="export-menu">
                        <a href="{{ route('fee-management.export-pdf', request()->query()) }}" class="export-item">
                            <i class="fas fa-file-pdf text-rose"></i>
                            <span>Export as PDF</span>
                        </a>
                        <a href="{{ route('fee-management.export-excel', request()->query()) }}" class="export-item">
                            <i class="fas fa-file-csv text-emerald"></i>
                            <span>Export as CSV</span>
                        </a>
                        <div class="export-divider"></div>
                        <button type="button" class="export-item" onclick="window.print()">
                            <i class="fas fa-print text-indigo"></i>
                            <span>Print Page</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Admission No</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th class="text-right">Total Fee</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Balance</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td class="mono text-muted">{{ $student->admission_no }}</td>
                            <td>
                                <div class="student-cell">
                                    @if($student->photo_url)
                                        <img src="{{ $student->photo_url }}" class="student-avatar" alt="">
                                    @else
                                        <div class="student-avatar-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                    <span class="student-name">{{ $student->full_name }}</span>
                                </div>
                            </td>
                            <td>
                                @foreach($student->studentClassEnrollments as $enrollment)
                                    <span class="class-badge">{{ $enrollment->classSection->schoolClass->name ?? '' }}{{ $enrollment->classSection->section->name ? ' - ' . $enrollment->classSection->section->name : '' }}</span>
                                @endforeach
                            </td>
                            <td class="text-right mono font-semibold">KSh {{ number_format($student->total_fee, 2) }}</td>
                            <td class="text-right mono text-emerald">KSh {{ number_format($student->paid_fee, 2) }}</td>
                            <td class="text-right mono {{ $student->balance_fee > 0 ? 'text-rose font-semibold' : 'text-muted' }}">KSh {{ number_format($student->balance_fee, 2) }}</td>
                            <td class="text-center">
                                @php
                                    $status = $student->payment_status;
                                    $statusClass = match($status) {
                                        'Paid' => 'status-paid',
                                        'Partial' => 'status-partial',
                                        'Unpaid' => 'status-unpaid',
                                        default => 'status-none'
                                    };
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
                            </td>
                            <td class="text-center">
                                <div class="action-buttons">
                                    <a href="{{ route('fee-management.show', $student->student_id) }}" class="action-btn action-view" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('fee-management.collect-payment', $student->student_id) }}" class="action-btn action-pay" title="Collect Payment">
                                        <i class="fas fa-cash-register"></i>
                                    </a>
                                    <a href="{{ route('fee-management.print', $student->student_id) }}" class="action-btn action-print" title="Print Statement" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <div class="empty-content">
                                    <i class="fas fa-inbox"></i>
                                    <p>No fee records found</p>
                                    <span>Adjust your filters or assign fees to students</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
        <div class="table-footer">
            {{ $students->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5;
    --indigo-light: #eef2ff;
    --amber: #f59e0b;
    --amber-light: #fffbeb;
    --emerald: #10b981;
    --emerald-light: #ecfdf5;
    --rose: #f43f5e;
    --rose-light: #fff1f2;
    --slate-50: #f8fafc;
    --slate-100: #f1f5f9;
    --slate-200: #e2e8f0;
    --slate-300: #cbd5e1;
    --slate-400: #94a3b8;
    --slate-500: #64748b;
    --slate-600: #475569;
    --slate-700: #334155;
    --slate-800: #1e293b;
    --slate-900: #0f172a;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
}

.fee-mgmt-wrap { padding: 1.5rem 2rem; background: #f9fafb; min-height: 100vh; }

.page-title { font-size: 1.25rem; font-weight: 900; color: var(--slate-900); letter-spacing: -0.02em; }
.page-subtitle { color: var(--slate-400); font-size: 0.8rem; font-weight: 500; }

.icon-box { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.bg-indigo-light { background: var(--indigo-light); }
.text-indigo { color: var(--indigo); }
.bg-amber-light { background: var(--amber-light); }
.text-amber { color: var(--amber); }
.bg-emerald-light { background: var(--emerald-light); }
.text-emerald { color: var(--emerald); }
.bg-rose-light { background: var(--rose-light); }
.text-rose { color: var(--rose); }

.btn-primary-custom {
    display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 8px;
    font-size: 0.75rem; font-weight: 800; border: none; text-decoration: none !important; cursor: pointer;
    background: var(--indigo); color: #fff; transition: all 160ms var(--ease-out);
}
.btn-primary-custom:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }
.btn-primary-custom:active { transform: scale(0.97); }

.btn-ghost-custom {
    display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 8px;
    font-size: 0.75rem; font-weight: 700; text-decoration: none !important; cursor: pointer;
    background: #fff; border: 1px solid var(--border); color: var(--slate-700); transition: all 160ms var(--ease-out);
}
.btn-ghost-custom:hover { background: var(--slate-100); }
.btn-ghost-custom:active { transform: scale(0.97); }

/* Metrics */
.metrics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.metric-card {
    background: #fff; border-radius: 12px; border: 1px solid var(--border); padding: 1.25rem;
    display: flex; align-items: center; gap: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06); transition: box-shadow 200ms var(--ease-out);
}
.metric-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.metric-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.metric-content { display: flex; flex-direction: column; }
.metric-label { font-size: 0.7rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.05em; }
.metric-value { font-size: 1.25rem; font-weight: 900; color: var(--slate-900); letter-spacing: -0.02em; margin-top: 2px; }

/* Filter Bar */
.filter-bar { background: #fff; border-radius: 12px; border: 1px solid var(--border); padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.filter-form { display: flex; align-items: flex-end; gap: 1rem; }
.filter-field { display: flex; flex-direction: column; gap: 4px; }
.filter-field label { font-size: 0.7rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.04em; }
.filter-select {
    height: 38px; padding: 0 2rem 0 0.75rem; border-radius: 8px; border: 1px solid var(--border);
    font-size: 0.8rem; font-weight: 600; color: var(--slate-700); background: #fff;
    appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 0.75rem center; min-width: 150px;
}
.filter-search { flex: 1; }
.search-input-wrap { position: relative; }
.search-input-wrap i { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: var(--slate-400); font-size: 0.8rem; }
.search-input {
    width: 100%; height: 38px; padding: 0 0.75rem 0 2.25rem; border-radius: 8px; border: 1px solid var(--border);
    font-size: 0.8rem; font-weight: 600; color: var(--slate-700); background: #fff;
}
.search-input::placeholder { color: var(--slate-300); }
.search-input:focus { outline: none; border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
.filter-actions { display: flex; gap: 0.5rem; }

/* Table Section */
.table-section { background: #fff; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: hidden; }
.table-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); }
.table-title { font-size: 0.85rem; font-weight: 800; color: var(--slate-800); margin: 0; }
.table-actions { position: relative; }

/* Export Dropdown */
.export-dropdown { position: relative; }
.btn-export { padding: 0.4rem 0.75rem; }
.export-menu {
    display: none; position: absolute; right: 0; top: calc(100% + 4px); min-width: 180px;
    background: #fff; border-radius: 10px; border: 1px solid var(--border);
    box-shadow: 0 10px 40px rgba(0,0,0,0.12); padding: 6px; z-index: 100;
}
.export-menu.show { display: block; }
.export-item {
    display: flex; align-items: center; gap: 0.625rem; padding: 0.5rem 0.75rem; border-radius: 6px;
    font-size: 0.78rem; font-weight: 600; color: var(--slate-700); text-decoration: none; border: none; background: none; width: 100%; cursor: pointer;
    transition: background 120ms var(--ease-out);
}
.export-item:hover { background: var(--slate-50); }
.export-item i { width: 16px; text-align: center; }
.export-divider { height: 1px; background: var(--slate-100); margin: 4px 0; }

/* Table */
.table-container { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table thead { background: var(--slate-50); }
.data-table th {
    padding: 0.75rem 1rem; font-size: 0.7rem; font-weight: 800; color: var(--slate-400);
    text-transform: uppercase; letter-spacing: 0.05em; text-align: left; border-bottom: 1px solid var(--border);
}
.data-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--slate-100); vertical-align: middle; }
.data-table tbody tr { transition: background 120ms var(--ease-out); }
.data-table tbody tr:hover { background: var(--slate-50); }
.data-table tbody tr:last-child td { border-bottom: none; }

.text-right { text-align: right; }
.text-center { text-align: center; }
.mono { font-family: 'SF Mono', 'Cascadia Code', 'Consolas', monospace; font-size: 0.8rem; }
.font-semibold { font-weight: 700; }
.text-muted { color: var(--slate-400); }

/* Student Cell */
.student-cell { display: flex; align-items: center; gap: 0.625rem; }
.student-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; }
.student-avatar-placeholder {
    width: 32px; height: 32px; border-radius: 50%; background: var(--slate-100);
    display: flex; align-items: center; justify-content: center; color: var(--slate-400); font-size: 0.7rem;
}
.student-name { font-weight: 600; color: var(--slate-800); font-size: 0.85rem; }

/* Class Badge */
.class-badge {
    display: inline-block; padding: 2px 8px; border-radius: 4px;
    background: var(--indigo-light); color: var(--indigo);
    font-size: 0.7rem; font-weight: 600;
}

/* Status Badge */
.status-badge {
    display: inline-block; padding: 3px 10px; border-radius: 5px;
    font-size: 0.7rem; font-weight: 700; text-transform: capitalize;
}
.status-paid { background: var(--emerald-light); color: var(--emerald); }
.status-partial { background: var(--amber-light); color: var(--amber-600); }
.status-unpaid { background: var(--rose-light); color: var(--rose); }
.status-none { background: var(--slate-100); color: var(--slate-400); }

/* Action Buttons */
.action-buttons { display: flex; align-items: center; justify-content: center; gap: 4px; }
.action-btn {
    width: 30px; height: 30px; border-radius: 6px; display: flex; align-items: center; justify-content: center;
    border: 1px solid var(--border); background: #fff; color: var(--slate-500); font-size: 0.7rem;
    text-decoration: none; transition: all 160ms var(--ease-out); cursor: pointer;
}
.action-btn:hover { background: var(--slate-50); }
.action-btn:active { transform: scale(0.93); }
.action-view:hover { border-color: var(--indigo); color: var(--indigo); }
.action-pay:hover { border-color: var(--emerald); color: var(--emerald); }
.action-print:hover { border-color: var(--amber); color: var(--amber); }

/* Empty State */
.empty-state { padding: 3rem 1rem !important; }
.empty-content { text-align: center; color: var(--slate-400); }
.empty-content i { font-size: 2rem; margin-bottom: 0.75rem; display: block; }
.empty-content p { font-size: 0.85rem; font-weight: 700; color: var(--slate-600); margin: 0 0 4px; }
.empty-content span { font-size: 0.75rem; }

/* Table Footer */
.table-footer { padding: 1rem 1.25rem; border-top: 1px solid var(--border); }

/* Print Styles */
@media print {
    .nav-link, .main-header, .main-sidebar, .main-footer, .filter-bar, .table-actions, .action-buttons, .btn-primary-custom, .btn-ghost-custom { display: none !important; }
    .fee-mgmt-wrap { padding: 0; background: #fff; }
    .metric-card, .table-section { box-shadow: none; border: 1px solid #ddd; }
    .content-wrapper { margin: 0 !important; }
}

/* Responsive */
@media (max-width: 1024px) {
    .metrics-grid { grid-template-columns: repeat(2, 1fr); }
    .filter-form { flex-wrap: wrap; }
}
</style>

@push('page_scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var exportToggle = document.getElementById('export-toggle');
    var exportMenu = document.getElementById('export-menu');

    if (exportToggle && exportMenu) {
        exportToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            exportMenu.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
            if (!exportToggle.contains(e.target) && !exportMenu.contains(e.target)) {
                exportMenu.classList.remove('show');
            }
        });
    }
});
</script>
@endpush
@endsection
