@extends('layouts.app')

@section('content')
<div class="report-wrap">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-indigo-light text-indigo">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div>
                <h1 class="page-title mb-0">Assignment Status</h1>
                <p class="page-subtitle mb-0">Track fee assignments and payment status per student</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('fees.dashboard') }}" class="btn-ghost-custom">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
            <a href="{{ route('fees.reports.export.assignment-status.pdf', request()->query()) }}" class="btn-ghost-custom">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-bar mb-4">
        <form action="{{ route('fees.reports.assignment-status') }}" method="GET" class="filter-form">
            <div class="filter-field">
                <label for="academic_year_id">Academic Year</label>
                <select name="academic_year_id" id="academic_year_id" class="filter-select">
                    @foreach($academicYears as $id => $name)
                        <option value="{{ $id }}" {{ $yearId == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="class_id">Class</label>
                <select name="class_id" id="class_id" class="filter-select">
                    <option value="">All Classes</option>
                    @foreach($classes as $id => $name)
                        <option value="{{ $id }}" {{ $classId == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="payment_status">Payment Status</label>
                <select name="payment_status" id="payment_status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="paid" {{ $statusFilter == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="partial" {{ $statusFilter == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="unpaid" {{ $statusFilter == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary-custom">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['academic_year_id', 'class_id', 'payment_status']))
                    <a href="{{ route('fees.reports.assignment-status') }}" class="btn-ghost-custom">
                        <i class="fas fa-times me-1"></i> Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Stats Row --}}
    <div class="stats-row mb-4">
        <div class="stat-chip">
            <span class="stat-chip-label">Total</span>
            <span class="stat-chip-value">{{ $stats['total'] }}</span>
        </div>
        <div class="stat-chip stat-chip-green">
            <span class="stat-chip-label">Paid</span>
            <span class="stat-chip-value">{{ $stats['paid'] }}</span>
        </div>
        <div class="stat-chip stat-chip-amber">
            <span class="stat-chip-label">Partial</span>
            <span class="stat-chip-value">{{ $stats['partial'] }}</span>
        </div>
        <div class="stat-chip stat-chip-rose">
            <span class="stat-chip-label">Unpaid</span>
            <span class="stat-chip-value">{{ $stats['unpaid'] }}</span>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-section">
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Admission No</th>
                        <th>Class</th>
                        <th>Fee Type</th>
                        <th>Term</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Discount</th>
                        <th class="text-right">Payable</th>
                        <th class="text-right">Paid</th>
                        <th class="text-right">Balance</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assignment)
                        @php
                            $pStatus = $assignment->payment_status;
                            $statusClass = match($pStatus) {
                                'paid' => 'status-paid',
                                'partial' => 'status-partial',
                                default => 'status-unpaid'
                            };
                            $classInfo = $assignment->student->studentClassEnrollments->first();
                            $className = $classInfo ? ($classInfo->classSection->schoolClass->name ?? 'N/A') : 'N/A';
                            $sectionName = $classInfo ? ($classInfo->classSection->section->name ?? '') : '';
                        @endphp
                        <tr>
                            <td class="font-semibold">{{ $assignment->student->full_name ?? 'N/A' }}</td>
                            <td class="mono text-muted">{{ $assignment->student->admission_no ?? 'N/A' }}</td>
                            <td><span class="class-badge">{{ $className }}{{ $sectionName ? ' - ' . $sectionName : '' }}</span></td>
                            <td class="font-semibold">{{ $assignment->feeStructure->category->name ?? 'N/A' }}</td>
                            <td class="text-muted-sm">{{ ucfirst($assignment->term ?? 'N/A') }}</td>
                            <td class="text-right mono">KSh {{ number_format($assignment->amount, 2) }}</td>
                            <td class="text-right mono text-emerald">-KSh {{ number_format($assignment->discount_amount, 2) }}</td>
                            <td class="text-right mono font-semibold">KSh {{ number_format($assignment->final_amount, 2) }}</td>
                            <td class="text-right mono text-emerald">KSh {{ number_format($assignment->paid_amount, 2) }}</td>
                            <td class="text-right mono {{ $assignment->balance > 0 ? 'text-rose font-semibold' : 'text-muted' }}">KSh {{ number_format($assignment->balance, 2) }}</td>
                            <td class="text-center">
                                <span class="status-badge {{ $statusClass }}">{{ ucfirst($pStatus) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="empty-cell"><div class="empty-mini"><i class="fas fa-inbox"></i><p>No assignments found</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assignments->hasPages())
        <div class="table-footer">
            {{ $assignments->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff; --amber: #f59e0b; --amber-600: #d97706; --amber-light: #fffbeb;
    --emerald: #10b981; --emerald-light: #ecfdf5; --rose: #f43f5e; --rose-light: #fff1f2;
    --slate-50: #f8fafc; --slate-100: #f1f5f9; --slate-200: #e2e8f0; --slate-300: #cbd5e1;
    --slate-400: #94a3b8; --slate-500: #64748b; --slate-600: #475569; --slate-700: #334155;
    --slate-800: #1e293b; --slate-900: #0f172a; --border: #e2e8f0;
    --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
}

.report-wrap { padding: 1.5rem 2rem; background: #f9fafb; min-height: 100vh; }
.page-title { font-size: 1.25rem; font-weight: 900; color: var(--slate-900); letter-spacing: -0.02em; }
.page-subtitle { color: var(--slate-400); font-size: 0.8rem; font-weight: 500; }
.icon-box { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.bg-indigo-light { background: var(--indigo-light); } .text-indigo { color: var(--indigo); }
.bg-emerald-light { background: var(--emerald-light); } .text-emerald { color: var(--emerald); }
.bg-amber-light { background: var(--amber-light); } .text-amber { color: var(--amber); }
.bg-rose-light { background: var(--rose-light); } .text-rose { color: var(--rose); }

.btn-primary-custom { display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800; border: none; cursor: pointer; background: var(--indigo); color: #fff; transition: all 160ms var(--ease-out); }
.btn-primary-custom:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }
.btn-primary-custom:active { transform: scale(0.97); }
.btn-ghost-custom { display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-decoration: none !important; cursor: pointer; background: #fff; border: 1px solid var(--border); color: var(--slate-700); transition: all 160ms var(--ease-out); }
.btn-ghost-custom:hover { background: var(--slate-100); } .btn-ghost-custom:active { transform: scale(0.97); }

.filter-bar { background: #fff; border-radius: 12px; border: 1px solid var(--border); padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.filter-form { display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap; }
.filter-field { display: flex; flex-direction: column; gap: 4px; }
.filter-field label { font-size: 0.7rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.04em; }
.filter-select { height: 38px; padding: 0 2rem 0 0.75rem; border-radius: 8px; border: 1px solid var(--border); font-size: 0.8rem; font-weight: 600; color: var(--slate-700); background: #fff; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 0.75rem center; min-width: 150px; }
.filter-actions { display: flex; gap: 0.5rem; }

.stats-row { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.stat-chip { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 8px; background: #fff; border: 1px solid var(--border); }
.stat-chip-label { font-size: 0.7rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.04em; }
.stat-chip-value { font-size: 1rem; font-weight: 900; color: var(--slate-900); }
.stat-chip-green { border-color: var(--emerald-light); background: var(--emerald-light); }
.stat-chip-green .stat-chip-value { color: var(--emerald); }
.stat-chip-amber { border-color: var(--amber-light); background: var(--amber-light); }
.stat-chip-amber .stat-chip-value { color: var(--amber-600); }
.stat-chip-rose { border-color: var(--rose-light); background: var(--rose-light); }
.stat-chip-rose .stat-chip-value { color: var(--rose); }

.table-section { background: #fff; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: hidden; }
.table-container { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; min-width: 900px; }
.data-table thead { background: var(--slate-50); }
.data-table th { padding: 0.75rem 1rem; font-size: 0.7rem; font-weight: 800; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.05em; text-align: left; border-bottom: 1px solid var(--border); }
.data-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--slate-100); vertical-align: middle; }
.data-table tbody tr { transition: background 120ms var(--ease-out); }
.data-table tbody tr:hover { background: var(--slate-50); }
.data-table tbody tr:last-child td { border-bottom: none; }

.text-right { text-align: right; } .text-center { text-align: center; }
.mono { font-family: 'SF Mono', 'Cascadia Code', 'Consolas', monospace; font-size: 0.8rem; }
.font-semibold { font-weight: 700; } .text-muted { color: var(--slate-400); } .text-muted-sm { font-size: 0.78rem; color: var(--slate-400); }

.class-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; background: var(--indigo-light); color: var(--indigo); font-size: 0.7rem; font-weight: 600; }

.status-badge { display: inline-block; padding: 3px 10px; border-radius: 5px; font-size: 0.7rem; font-weight: 700; text-transform: capitalize; }
.status-paid { background: var(--emerald-light); color: var(--emerald); }
.status-partial { background: var(--amber-light); color: var(--amber-600); }
.status-unpaid { background: var(--rose-light); color: var(--rose); }

.empty-cell { padding: 2.5rem 1rem !important; }
.empty-mini { text-align: center; color: var(--slate-300); }
.empty-mini i { font-size: 1.5rem; margin-bottom: 0.5rem; display: block; }
.empty-mini p { font-size: 0.82rem; font-weight: 600; color: var(--slate-400); margin: 0; }

.table-footer { padding: 1rem 1.25rem; border-top: 1px solid var(--border); }
</style>
@endsection
