@extends('layouts.app')

@section('content')
<div class="report-wrap">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-rose-light text-rose">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h1 class="page-title mb-0">Fee Arrears</h1>
                <p class="page-subtitle mb-0">Students with outstanding balances</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('fees.dashboard') }}" class="btn-ghost-custom">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
            <a href="{{ route('fees.arrears.export-csv', request()->query()) }}" class="btn-ghost-custom">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a>
            <a href="{{ route('fees.arrears.export-pdf', request()->query()) }}" class="btn-ghost-custom">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-bar mb-4">
        <form action="{{ route('fees.arrears.index') }}" method="GET" class="filter-form">
            <div class="filter-field">
                <label for="academic_year_id">Academic Year</label>
                <select name="academic_year_id" id="academic_year_id" class="filter-select">
                    @foreach($academicYears as $id => $name)
                        <option value="{{ $id }}" {{ $yearId == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="class_id">Class / Form</label>
                <select name="class_id" id="class_id" class="filter-select">
                    <option value="">All Classes</option>
                    @foreach($classes as $id => $name)
                        <option value="{{ $id }}" {{ $classId == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="term_id">Term</label>
                <select name="term_id" id="term_id" class="filter-select">
                    <option value="">All Terms</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $termId == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="min_amount">Minimum Outstanding (KSh)</label>
                <input type="number" name="min_amount" id="min_amount" value="{{ $minAmount }}" class="filter-select" placeholder="e.g. 500">
            </div>
            <div class="filter-field">
                <label for="search">Search Student</label>
                <input type="text" name="search" id="search" value="{{ $search }}" class="filter-select" placeholder="Name or Admission No">
            </div>
            <div class="filter-field">
                <label for="sort">Sort</label>
                <select name="sort" id="sort" class="filter-select">
                    <option value="largest" {{ $sort == 'largest' ? 'selected' : '' }}>Largest Balance First</option>
                    <option value="smallest" {{ $sort == 'smallest' ? 'selected' : '' }}>Smallest Balance First</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary-custom">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('fees.arrears.index') }}" class="btn-ghost-custom">Clear</a>
            </div>
        </form>
    </div>

    {{-- Metrics Grid --}}
    <div class="metrics-grid mb-4">
        <div class="metric-card">
            <div class="metric-icon bg-indigo-light text-indigo"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="metric-content">
                <span class="metric-label">Total Expected</span>
                <span class="metric-value">KSh {{ number_format($totalExpected, 0) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-emerald-light text-emerald"><i class="fas fa-check-double"></i></div>
            <div class="metric-content">
                <span class="metric-label">Total Collected</span>
                <span class="metric-value text-emerald">KSh {{ number_format($totalCollected, 0) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-rose-light text-rose"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="metric-content">
                <span class="metric-label">Outstanding</span>
                <span class="metric-value text-rose">KSh {{ number_format($totalOutstanding, 0) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon {{ $collectionRate >= 75 ? 'bg-emerald-light text-emerald' : ($collectionRate >= 50 ? 'bg-amber-light text-amber' : 'bg-rose-light text-rose') }}">
                <i class="fas fa-coins"></i>
            </div>
            <div class="metric-content">
                <span class="metric-label">Collection Rate</span>
                <span class="metric-value {{ $collectionRate >= 75 ? 'text-emerald' : ($collectionRate >= 50 ? 'text-amber' : 'text-rose') }}">{{ $collectionRate }}%</span>
                <span class="metric-rate">{{ $studentsInArrears }} students in arrears</span>
            </div>
        </div>
    </div>

    {{-- Arrears Table --}}
    <div class="report-card">
        <div class="card-header-custom">
            <div class="card-title-group">
                <i class="fas fa-list"></i>
                <span>Students in Arrears ({{ $arrears->total() }})</span>
            </div>
        </div>
        <div class="table-section">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Admission No</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th class="text-right">Expected</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Outstanding</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arrears as $student)
                        @php
                            $name = trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name);
                            $className = $student->studentClass ?? 'N/A';
                            $outstanding = $student->expected_total - $student->paid_total;
                        @endphp
                        <tr>
                            <td><span class="mono-sm">{{ $student->admission_no }}</span></td>
                            <td class="font-semibold">{{ $name }}</td>
                            <td><span class="class-badge">{{ $className }}</span></td>
                            <td class="text-right mono">KSh {{ number_format($student->expected_total, 2) }}</td>
                            <td class="text-right mono text-emerald">KSh {{ number_format($student->paid_total, 2) }}</td>
                            <td class="text-right mono text-rose font-semibold">KSh {{ number_format($outstanding, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('fee-management.show', $student->student_id) }}" class="btn-ghost-custom btn-xs">
                                    <i class="fas fa-receipt me-1"></i> Statement
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-cell">
                                <div class="empty-mini">
                                    <i class="fas fa-check-circle"></i>
                                    <p>No students in arrears.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="table-footer">
                {{ $arrears->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --amber: #f59e0b; --amber-600: #d97706; --amber-light: #fffbeb;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --slate-50: #f8fafc; --slate-100: #f1f5f9; --slate-200: #e2e8f0;
    --slate-300: #cbd5e1; --slate-400: #94a3b8; --slate-500: #64748b;
    --slate-600: #475569; --slate-700: #334155; --slate-800: #1e293b; --slate-900: #0f172a;
    --border: #e2e8f0; --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
}
.report-wrap { padding: 1.5rem 2rem; background: #f9fafb; min-height: 100vh; }
.page-title { font-size: 1.25rem; font-weight: 900; color: var(--slate-900); letter-spacing: -0.02em; }
.page-subtitle { color: var(--slate-400); font-size: 0.8rem; font-weight: 500; }
.mono { font-family: 'SF Mono','Cascadia Code','Consolas',monospace; font-size: 0.8rem; }
.mono-sm { font-family: monospace; font-size: 0.75rem; font-weight: 600; }
.font-semibold { font-weight: 700; }
.text-emerald { color: var(--emerald); } .text-rose { color: var(--rose); } .text-amber { color: var(--amber-600); }
.text-right { text-align: right; } .text-center { text-align: center; }
.icon-box { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.bg-indigo-light { background: var(--indigo-light); } .text-indigo { color: var(--indigo); }
.bg-amber-light { background: var(--amber-light); } .bg-emerald-light { background: var(--emerald-light); }
.bg-rose-light { background: var(--rose-light); }
.btn-primary-custom { display:inline-flex; align-items:center; padding:.5rem 1.25rem; border-radius:8px; font-size:.75rem; font-weight:800; border:none; text-decoration:none!important; cursor:pointer; background:var(--emerald); color:#fff; transition:all 160ms var(--ease-out);}
.btn-primary-custom:hover { background:#059669; transform:translateY(-1px); box-shadow:0 4px 12px rgba(16,185,129,.3);}
.btn-ghost-custom { display:inline-flex; align-items:center; padding:.5rem 1.25rem; border-radius:8px; font-size:.75rem; font-weight:700; text-decoration:none!important; cursor:pointer; background:#fff; border:1px solid var(--border); color:var(--slate-700); transition:all 160ms var(--ease-out);}
.btn-ghost-custom:hover { background:var(--slate-100); }
.btn-xs { padding:.3rem .75rem; font-size:.68rem; }
.filter-bar { background:#fff; border:1px solid var(--border); border-radius:12px; padding:1rem 1.25rem; }
.filter-form { display:flex; flex-wrap:wrap; gap:1rem; align-items:flex-end; }
.filter-field { display:flex; flex-direction:column; gap:.35rem; }
.filter-field label { font-size:.68rem; font-weight:700; color:var(--slate-500); text-transform:uppercase; letter-spacing:.04em; }
.filter-select { padding:.5rem .75rem; border:1px solid var(--border); border-radius:8px; font-size:.8rem; color:var(--slate-700); background:#fff; min-width:160px; }
.filter-actions { display:flex; gap:.5rem; align-items:flex-end; }
.metrics-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; }
.metric-card { background:#fff; border:1px solid var(--border); border-radius:12px; padding:1rem 1.25rem; display:flex; align-items:center; gap:1rem; box-shadow:0 1px 3px rgba(0,0,0,.06); }
.metric-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
.metric-content { display:flex; flex-direction:column; }
.metric-label { font-size:.7rem; font-weight:700; color:var(--slate-400); text-transform:uppercase; letter-spacing:.05em; }
.metric-value { font-size:1.05rem; font-weight:900; color:var(--slate-900); font-family:monospace; }
.metric-rate { font-size:.72rem; color:var(--slate-400); font-weight:600; }
.report-card { background:#fff; border:1px solid var(--border); border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.06); }
.card-header-custom { display:flex; align-items:center; justify-content:space-between; padding:.9rem 1.25rem; border-bottom:1px solid var(--border); background:var(--slate-50); }
.card-title-group { display:flex; align-items:center; gap:.5rem; font-size:.78rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; color:var(--slate-500); }
.card-title-group i { color:var(--rose); }
.table-section { overflow-x:auto; }
.data-table { width:100%; border-collapse:collapse; }
.data-table thead { background:var(--slate-50); }
.data-table th { padding:.75rem 1rem; font-size:.7rem; font-weight:800; color:var(--slate-400); text-transform:uppercase; letter-spacing:.05em; text-align:left; border-bottom:1px solid var(--border); }
.data-table td { padding:.75rem 1rem; border-bottom:1px solid var(--slate-100); vertical-align:middle; font-size:.82rem; color:var(--slate-700); }
.data-table tbody tr:hover { background:var(--slate-50); }
.class-badge { display:inline-block; padding:3px 10px; border-radius:5px; background:var(--indigo-light); color:var(--indigo); font-size:.7rem; font-weight:700; }
.table-footer { padding:1rem 1.25rem; border-top:1px solid var(--border); }
.empty-cell { padding:2.5rem 1rem!important; }
.empty-mini { text-align:center; color:var(--slate-300); }
.empty-mini i { font-size:1.5rem; margin-bottom:.5rem; display:block; }
.empty-mini p { font-size:.82rem; font-weight:600; color:var(--slate-400); margin:0; }

@media (max-width:768px) {
    .report-wrap { padding:1rem; }
    .d-flex.align-items-center.justify-content-between.mb-4 { flex-direction:column; align-items:flex-start!important; gap:0.75rem; }
    .d-flex.align-items-center.justify-content-between.mb-4 > .d-flex { width:100%; flex-wrap:wrap; gap:0.5rem; }
    .d-flex.align-items-center.justify-content-between.mb-4 .btn-ghost-custom { flex:1; justify-content:center; padding:0.5rem 0.625rem; font-size:0.7rem; }
    .page-title { font-size:1.1rem; }
    .filter-form { flex-direction:column; gap:0.625rem; }
    .filter-field { width:100%; }
    .filter-select { width:100%; min-width:0; }
    .filter-actions { width:100%; }
    .filter-actions .btn-primary-custom, .filter-actions .btn-ghost-custom { flex:1; justify-content:center; }
    .metrics-grid { grid-template-columns:1fr 1fr; gap:0.625rem; }
    .metric-card { padding:0.75rem 1rem; }
    .metric-icon { width:36px; height:36px; font-size:0.9rem; }
    .metric-value { font-size:0.9rem; }
    .data-table th:nth-child(1), .data-table td:nth-child(1),
    .data-table th:nth-child(3), .data-table td:nth-child(3) { display:none; }
    .data-table th { padding:0.6rem 0.625rem; font-size:0.6rem; }
    .data-table td { padding:0.6rem 0.625rem; font-size:0.75rem; }
    .btn-xs { padding:0.25rem 0.5rem; font-size:0.62rem; }
}

@media (max-width:420px) {
    .metrics-grid { grid-template-columns:1fr; }
    .icon-box { width:34px; height:34px; font-size:0.85rem; }
    .page-title { font-size:1rem; }
}
</style>
@endsection
