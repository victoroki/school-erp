@extends('layouts.app')

@section('content')
<div class="report-wrap">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-indigo-light text-indigo">
                <i class="fas fa-chart-line"></i>
            </div>
            <div>
                <h1 class="page-title mb-0">Revenue Forecast</h1>
                <p class="page-subtitle mb-0">Expected revenue, collection tracking, and breakdowns</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('fees.dashboard') }}" class="btn-ghost-custom">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
            <a href="{{ route('fees.reports.export.expected-revenue.pdf', request()->query()) }}" class="btn-ghost-custom">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-bar mb-4">
        <form action="{{ route('fees.reports.expected-revenue') }}" method="GET" class="filter-form">
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

    {{-- Metrics Grid --}}
    <div class="metrics-grid mb-4">
        <div class="metric-card">
            <div class="metric-icon bg-indigo-light text-indigo"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="metric-content">
                <span class="metric-label">Gross Revenue</span>
                <span class="metric-value">KSh {{ number_format($totalOriginal, 0) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-amber-light text-amber"><i class="fas fa-percent"></i></div>
            <div class="metric-content">
                <span class="metric-label">Total Discounts</span>
                <span class="metric-value text-amber">KSh {{ number_format($totalDiscounts, 0) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-emerald-light text-emerald"><i class="fas fa-check-double"></i></div>
            <div class="metric-content">
                <span class="metric-label">Net Expected</span>
                <span class="metric-value">KSh {{ number_format($totalExpected, 0) }}</span>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon {{ $collectionRate >= 75 ? 'bg-emerald-light text-emerald' : ($collectionRate >= 50 ? 'bg-amber-light text-amber' : 'bg-rose-light text-rose') }}">
                <i class="fas fa-coins"></i>
            </div>
            <div class="metric-content">
                <span class="metric-label">Collected</span>
                <span class="metric-value {{ $collectionRate >= 75 ? 'text-emerald' : ($collectionRate >= 50 ? 'text-amber' : 'text-rose') }}">KSh {{ number_format($totalCollected, 0) }}</span>
                <span class="metric-rate">{{ $collectionRate }}% collection rate</span>
            </div>
        </div>
    </div>

    {{-- Payment Status --}}
    <div class="status-bar mb-4">
        <div class="status-segment status-paid-seg" style="width: {{ $paymentStatusBreakdown['paid'] + $paymentStatusBreakdown['partial'] + $paymentStatusBreakdown['unpaid'] > 0 ? ($paymentStatusBreakdown['paid'] / ($paymentStatusBreakdown['paid'] + $paymentStatusBreakdown['partial'] + $paymentStatusBreakdown['unpaid'])) * 100 : 0 }}%">
            <span class="status-count">{{ $paymentStatusBreakdown['paid'] }}</span>
            <span class="status-label">Paid</span>
        </div>
        <div class="status-segment status-partial-seg" style="width: {{ $paymentStatusBreakdown['paid'] + $paymentStatusBreakdown['partial'] + $paymentStatusBreakdown['unpaid'] > 0 ? ($paymentStatusBreakdown['partial'] / ($paymentStatusBreakdown['paid'] + $paymentStatusBreakdown['partial'] + $paymentStatusBreakdown['unpaid'])) * 100 : 0 }}%">
            <span class="status-count">{{ $paymentStatusBreakdown['partial'] }}</span>
            <span class="status-label">Partial</span>
        </div>
        <div class="status-segment status-unpaid-seg" style="width: {{ $paymentStatusBreakdown['paid'] + $paymentStatusBreakdown['partial'] + $paymentStatusBreakdown['unpaid'] > 0 ? ($paymentStatusBreakdown['unpaid'] / ($paymentStatusBreakdown['paid'] + $paymentStatusBreakdown['partial'] + $paymentStatusBreakdown['unpaid'])) * 100 : 0 }}%">
            <span class="status-count">{{ $paymentStatusBreakdown['unpaid'] }}</span>
            <span class="status-label">Unpaid</span>
        </div>
    </div>

    <div class="row g-4">
        {{-- Revenue by Class --}}
        <div class="col-lg-6">
            <div class="report-card">
                <div class="report-card-header">
                    <i class="fas fa-school"></i>
                    <span>Revenue by Class</span>
                </div>
                <div class="report-card-body p-0">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Students</th>
                                <th class="text-right">Expected</th>
                                <th class="text-right">Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($revenueByClass as $row)
                                <tr>
                                    <td class="font-semibold">{{ $row->class_name }}</td>
                                    <td class="text-muted-sm">{{ $row->student_count }}</td>
                                    <td class="text-right mono">KSh {{ number_format($row->expected, 0) }}</td>
                                    <td class="text-right mono text-emerald">KSh {{ number_format($row->collected ?? 0, 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="empty-cell"><div class="empty-mini"><i class="fas fa-chart-bar"></i><p>No data</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Revenue by Category --}}
        <div class="col-lg-6">
            <div class="report-card">
                <div class="report-card-header">
                    <i class="fas fa-tags"></i>
                    <span>Revenue by Fee Category</span>
                </div>
                <div class="report-card-body p-0">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Type</th>
                                <th class="text-right">Assignments</th>
                                <th class="text-right">Expected</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($revenueByCategory as $row)
                                <tr>
                                    <td class="font-semibold">{{ $row->category_name }}</td>
                                    <td><span class="type-badge {{ $row->category_type === 'mandatory' ? 'type-mandatory' : 'type-optional' }}">{{ ucfirst($row->category_type) }}</span></td>
                                    <td class="text-right text-muted-sm">{{ $row->assignment_count }}</td>
                                    <td class="text-right mono">KSh {{ number_format($row->total, 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="empty-cell"><div class="empty-mini"><i class="fas fa-chart-bar"></i><p>No data</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
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
.metric-card { background: #fff; border-radius: 12px; border: 1px solid var(--border); padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.06); transition: box-shadow 200ms var(--ease-out); }
.metric-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.metric-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.metric-content { display: flex; flex-direction: column; }
.metric-label { font-size: 0.7rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.05em; }
.metric-value { font-size: 1.2rem; font-weight: 900; color: var(--slate-900); letter-spacing: -0.02em; margin-top: 2px; }
.metric-rate { font-size: 0.7rem; font-weight: 600; color: var(--slate-400); margin-top: 2px; }

/* Status Bar */
.status-bar { display: flex; border-radius: 8px; overflow: hidden; height: 40px; }
.status-segment { display: flex; align-items: center; justify-content: center; gap: 4px; transition: width 400ms var(--ease-out); }
.status-segment span { white-space: nowrap; }
.status-count { font-size: 0.8rem; font-weight: 800; }
.status-label { font-size: 0.7rem; font-weight: 600; opacity: 0.85; }
.status-paid-seg { background: var(--emerald); color: #fff; }
.status-partial-seg { background: var(--amber); color: #fff; }
.status-unpaid-seg { background: var(--rose); color: #fff; }

/* Report Cards */
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

.text-right { text-align: right; } .text-center { text-align: center; }
.mono { font-family: 'SF Mono', 'Cascadia Code', 'Consolas', monospace; font-size: 0.8rem; }
.font-semibold { font-weight: 700; } .text-muted-sm { font-size: 0.78rem; color: var(--slate-400); }

.type-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.68rem; font-weight: 700; text-transform: capitalize; }
.type-mandatory { background: var(--indigo-light); color: var(--indigo); }
.type-optional { background: var(--slate-100); color: var(--slate-500); }

.empty-cell { padding: 2.5rem 1rem !important; }
.empty-mini { text-align: center; color: var(--slate-300); }
.empty-mini i { font-size: 1.5rem; margin-bottom: 0.5rem; display: block; }
.empty-mini p { font-size: 0.82rem; font-weight: 600; color: var(--slate-400); margin: 0; }

@media (max-width: 1024px) { .metrics-grid { grid-template-columns: repeat(2, 1fr); } }
</style>
@endsection
