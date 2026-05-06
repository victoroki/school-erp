@extends('layouts.app')

@section('content')
<div class="report-wrap">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-amber-light text-amber">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <div>
                <h1 class="page-title mb-0">Discount Summary</h1>
                <p class="page-subtitle mb-0">Track all discounts applied to student fee assignments</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('fees.dashboard') }}" class="btn-ghost-custom">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
            <a href="{{ route('fees.reports.export.discount-summary.pdf', request()->query()) }}" class="btn-ghost-custom">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-bar mb-4">
        <form action="{{ route('fees.reports.discount-summary') }}" method="GET" class="filter-form">
            <div class="filter-field">
                <label for="academic_year_id">Academic Year</label>
                <select name="academic_year_id" id="academic_year_id" class="filter-select">
                    @foreach($academicYears as $id => $name)
                        <option value="{{ $id }}" {{ $yearId == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary-custom">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="metrics-grid mb-4">
        <div class="metric-card">
            <div class="metric-icon bg-amber-light text-amber"><i class="fas fa-hand-holding-usd"></i></div>
            <div class="metric-content">
                <span class="metric-label">Total Discounts Applied</span>
                <span class="metric-value text-amber">KSh {{ number_format($totalDiscounts, 0) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-indigo-light text-indigo"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="metric-content">
                <span class="metric-label">Original Amount (Discounted)</span>
                <span class="metric-value">KSh {{ number_format($totalOriginalForDiscounted, 0) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-emerald-light text-emerald"><i class="fas fa-users"></i></div>
            <div class="metric-content">
                <span class="metric-label">Students with Discounts</span>
                <span class="metric-value">{{ $discounts->total() }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-rose-light text-rose"><i class="fas fa-percentage"></i></div>
            <div class="metric-content">
                <span class="metric-label">Discount Rate</span>
                <span class="metric-value">{{ $totalOriginalForDiscounted > 0 ? round(($totalDiscounts / $totalOriginalForDiscounted) * 100, 1) : 0 }}%</span>
            </div>
        </div>
    </div>

    {{-- Breakdown by Scheme --}}
    @if($discountSchemes->count() > 0)
    <div class="report-card mb-4">
        <div class="report-card-header">
            <i class="fas fa-chart-pie"></i>
            <span>Discount Breakdown by Scheme</span>
        </div>
        <div class="report-card-body p-0">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Scheme Name</th>
                        <th>Eligibility</th>
                        <th class="text-center">Students</th>
                        <th class="text-right">Total Discount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($discountSchemes as $scheme)
                        <tr>
                            <td class="font-semibold">{{ $scheme->scheme_name }}</td>
                            <td><span class="criteria-badge">{{ ucfirst(str_replace('_', ' ', $scheme->criteria)) }}</span></td>
                            <td class="text-center text-muted-sm">{{ $scheme->student_count }}</td>
                            <td class="text-right mono text-rose font-semibold">KSh {{ number_format($scheme->total_discount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Student Discount Details --}}
    <div class="table-section">
        <div class="table-header">
            <h2 class="table-title">Student Discount Details</h2>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Fee Type</th>
                        <th>Discount Scheme</th>
                        <th class="text-right">Original</th>
                        <th class="text-right">Discount</th>
                        <th class="text-right">Final Amount</th>
                        <th>Assigned Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($discounts as $discount)
                        @php
                            $classInfo = $discount->student->studentClassEnrollments->first();
                            $className = $classInfo ? ($classInfo->classSection->schoolClass->name ?? 'N/A') : 'N/A';
                        @endphp
                        <tr>
                            <td>
                                <div class="student-cell">
                                    <span class="student-name">{{ $discount->student->full_name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td><span class="class-badge">{{ $className }}</span></td>
                            <td class="font-semibold">{{ $discount->feeStructure->category->name ?? 'N/A' }}</td>
                            <td><span class="scheme-badge">{{ $discount->discount->name ?? 'Manual' }}</span></td>
                            <td class="text-right mono">KSh {{ number_format($discount->amount, 2) }}</td>
                            <td class="text-right mono text-rose font-semibold">-KSh {{ number_format($discount->discount_amount, 2) }}</td>
                            <td class="text-right mono font-semibold">KSh {{ number_format($discount->final_amount, 2) }}</td>
                            <td class="text-muted-sm">{{ $discount->assigned_date?->format('d M Y') ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty-cell"><div class="empty-mini"><i class="fas fa-percent"></i><p>No discounts found</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($discounts->hasPages())
        <div class="table-footer">
            {{ $discounts->appends(request()->query())->links() }}
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
.bg-amber-light { background: var(--amber-light); } .text-amber { color: var(--amber); }
.bg-emerald-light { background: var(--emerald-light); } .text-emerald { color: var(--emerald); }
.bg-rose-light { background: var(--rose-light); } .text-rose { color: var(--rose); }

.btn-primary-custom { display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800; border: none; cursor: pointer; background: var(--indigo); color: #fff; transition: all 160ms var(--ease-out); }
.btn-primary-custom:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }
.btn-primary-custom:active { transform: scale(0.97); }
.btn-ghost-custom { display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-decoration: none !important; cursor: pointer; background: #fff; border: 1px solid var(--border); color: var(--slate-700); transition: all 160ms var(--ease-out); }
.btn-ghost-custom:hover { background: var(--slate-100); } .btn-ghost-custom:active { transform: scale(0.97); }

.filter-bar { background: #fff; border-radius: 12px; border: 1px solid var(--border); padding: 1rem 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.filter-form { display: flex; align-items: flex-end; gap: 1rem; }
.filter-field { display: flex; flex-direction: column; gap: 4px; }
.filter-field label { font-size: 0.7rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.04em; }
.filter-select { height: 38px; padding: 0 2rem 0 0.75rem; border-radius: 8px; border: 1px solid var(--border); font-size: 0.8rem; font-weight: 600; color: var(--slate-700); background: #fff; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 0.75rem center; min-width: 200px; }
.filter-actions { display: flex; gap: 0.5rem; }

.metrics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.metric-card { background: #fff; border-radius: 12px; border: 1px solid var(--border); padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.metric-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.metric-content { display: flex; flex-direction: column; }
.metric-label { font-size: 0.7rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.05em; }
.metric-value { font-size: 1.2rem; font-weight: 900; color: var(--slate-900); letter-spacing: -0.02em; margin-top: 2px; }

/* Scheme Breakdown */
.report-card { background: #fff; border-radius: 12px; border: 1px solid var(--border); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.report-card-header { display: flex; align-items: center; gap: 0.5rem; padding: 0.875rem 1.25rem; border-bottom: 1px solid var(--border); background: var(--slate-50); font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--slate-400); }
.report-card-header i { color: var(--indigo); font-size: 0.75rem; }
.report-card-body { padding: 0.25rem 0; }
.report-table { width: 100%; border-collapse: collapse; }
.report-table thead { background: var(--slate-50); }
.report-table th { padding: 0.75rem 1rem; font-size: 0.7rem; font-weight: 800; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.05em; text-align: left; border-bottom: 1px solid var(--border); }
.report-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--slate-100); vertical-align: middle; }
.report-table tbody tr { transition: background 120ms var(--ease-out); }
.report-table tbody tr:hover { background: var(--slate-50); }
.report-table tbody tr:last-child td { border-bottom: none; }

.criteria-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; background: var(--amber-light); color: var(--amber-600); font-size: 0.68rem; font-weight: 700; }

/* Table Section */
.table-section { background: #fff; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: hidden; }
.table-header { display: flex; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); }
.table-title { font-size: 0.85rem; font-weight: 800; color: var(--slate-800); margin: 0; }
.table-container { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; min-width: 800px; }
.data-table thead { background: var(--slate-50); }
.data-table th { padding: 0.75rem 1rem; font-size: 0.7rem; font-weight: 800; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.05em; text-align: left; border-bottom: 1px solid var(--border); }
.data-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--slate-100); vertical-align: middle; }
.data-table tbody tr { transition: background 120ms var(--ease-out); }
.data-table tbody tr:hover { background: var(--slate-50); }
.data-table tbody tr:last-child td { border-bottom: none; }

.text-right { text-align: right; } .text-center { text-align: center; }
.mono { font-family: 'SF Mono', 'Cascadia Code', 'Consolas', monospace; font-size: 0.8rem; }
.font-semibold { font-weight: 700; } .text-muted-sm { font-size: 0.78rem; color: var(--slate-400); }

.student-cell { display: flex; align-items: center; gap: 0.5rem; }
.student-name { font-weight: 600; color: var(--slate-800); font-size: 0.85rem; }
.class-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; background: var(--indigo-light); color: var(--indigo); font-size: 0.7rem; font-weight: 600; }
.scheme-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; background: var(--amber-light); color: var(--amber-600); font-size: 0.7rem; font-weight: 700; }

.empty-cell { padding: 2.5rem 1rem !important; }
.empty-mini { text-align: center; color: var(--slate-300); }
.empty-mini i { font-size: 1.5rem; margin-bottom: 0.5rem; display: block; }
.empty-mini p { font-size: 0.82rem; font-weight: 600; color: var(--slate-400); margin: 0; }
.table-footer { padding: 1rem 1.25rem; border-top: 1px solid var(--border); }

@media (max-width: 1024px) { .metrics-grid { grid-template-columns: repeat(2, 1fr); } }
</style>
@endsection
