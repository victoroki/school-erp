@extends('layouts.app')

@section('content')
<div class="dash-wrap">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div class="d-flex align-items-center gap-3">
            <div class="show-icon-box bg-indigo-light text-indigo shadow-sm">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h1 class="show-heading mb-0">Fee Structure Details</h1>
                <p class="show-sub mb-0">
                    <span class="fw-bold text-dark">{{ optional($feeStructure->schoolClass)->name ?? 'Class' }}</span>
                    &mdash; {{ optional($feeStructure->academicYear)->name ?? 'Year' }}
                    @if($feeStructure->term)
                        &mdash; <span class="text-indigo fw-bold">{{ $feeStructure->term }}</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('fee-structures.index') }}" class="show-btn show-btn-ghost">
                <i class="fas fa-arrow-left me-2"></i> Back to Hub
            </a>
            <a href="{{ route('fee-structures.edit', $feeStructure->fee_structure_id) }}" class="show-btn show-btn-amber">
                <i class="fas fa-edit me-2"></i> Edit
            </a>
        </div>
    </div>

    @include('flash::message')

    <div class="row g-4">

        {{-- Left Column: Package Breakdown + Item Details --}}
        <div class="col-lg-8">

            {{-- Complete Package Table --}}
            <div class="show-panel mb-4">
                <div class="show-panel-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 show-panel-title text-indigo">
                        <i class="fas fa-cubes me-2"></i> Full Package — {{ optional($feeStructure->schoolClass)->name }}
                    </h5>
                    <span class="show-badge bg-indigo text-white">{{ $relatedFees->count() }} items</span>
                </div>
                <div class="show-panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="x-small text-uppercase fw-bold text-muted letter-sp">
                                    <th class="ps-4 py-3 border-0">Category</th>
                                    <th class="py-3 border-0">Term</th>
                                    <th class="py-3 border-0">Frequency</th>
                                    <th class="py-3 border-0">Due Date</th>
                                    <th class="pe-4 py-3 border-0 text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($relatedFees as $item)
                                <tr class="{{ $item->fee_structure_id == $feeStructure->fee_structure_id ? 'row-selected' : '' }}">
                                    <td class="ps-4 py-3">
                                        <span class="fw-bold text-dark">{{ optional($item->category)->name ?? 'N/A' }}</span>
                                        @if($item->fee_structure_id == $feeStructure->fee_structure_id)
                                            <span class="ms-2 show-badge bg-indigo text-white" style="font-size:0.6rem;">Viewing</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-muted small">{{ $item->term ?? '—' }}</td>
                                    <td class="py-3 text-muted small fw-bold text-capitalize">{{ str_replace('-', ' ', $item->payment_frequency) }}</td>
                                    <td class="py-3 text-muted small">{{ $item->due_date ? $item->due_date->format('M d, Y') : 'N/A' }}</td>
                                    <td class="pe-4 py-3 text-end fw-850 text-dark">KSh {{ number_format($item->amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="4" class="ps-4 py-3 fw-850 text-uppercase text-muted small text-end">Total Package</td>
                                    <td class="pe-4 py-3 text-end fw-850 text-emerald h5 mb-0">KSh {{ number_format($relatedFees->sum('amount'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Configuration Details --}}
            <div class="show-panel">
                <div class="show-panel-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 show-panel-title text-slate">
                        <i class="fas fa-sliders-h me-2"></i> Item Configuration
                    </h5>
                    <span class="show-badge {{ $feeStructure->status === 'active' ? 'bg-emerald-light text-emerald' : 'bg-slate-light text-slate' }}">
                        {{ ucfirst($feeStructure->status) }}
                    </span>
                </div>
                <div class="show-panel-body">
                    @include('fee_structures.show_fields')
                </div>
            </div>

        </div>

        {{-- Right Column: Stats & Controls --}}
        <div class="col-lg-4">

            {{-- Total Amount Hero --}}
            <div class="show-panel mb-4 bg-indigo text-white overflow-hidden position-relative">
                <div class="show-panel-body p-4 text-center">
                    <p class="x-small text-uppercase fw-bold mb-2 opacity-75 letter-sp">Total Package Value</p>
                    <div class="display-5 fw-850 mb-1">KSh</div>
                    <div class="h2 fw-850 mb-2">{{ number_format($relatedFees->sum('amount'), 2) }}</div>
                    <p class="small opacity-75 mb-0">
                        Assigned to <strong>{{ $feeStructure->assignments()->count() }}</strong> student(s)
                    </p>
                    <i class="fas fa-money-bill-wave position-absolute text-white opacity-10"
                       style="font-size:9rem;right:-1.5rem;bottom:-1rem;"></i>
                </div>
            </div>

            {{-- Billing Settings --}}
            <div class="show-panel">
                <div class="show-panel-header">
                    <h6 class="mb-0 show-panel-title text-slate">
                        <i class="fas fa-cog me-2"></i> Billing Rules
                    </h6>
                </div>
                <div class="show-panel-body p-0">
                    <div class="setting-row px-4 py-3 border-bottom">
                        <span class="x-small text-uppercase fw-bold text-muted">Status</span>
                        <span class="show-badge mt-1 {{ $feeStructure->status === 'active' ? 'bg-emerald-light text-emerald' : 'bg-slate-light text-slate' }}">
                            {{ ucfirst($feeStructure->status) }}
                        </span>
                    </div>
                    <div class="setting-row px-4 py-3 border-bottom">
                        <span class="x-small text-uppercase fw-bold text-muted">Pro-Rata Billing</span>
                        <span class="show-badge mt-1 {{ $feeStructure->pro_rata_enabled ? 'bg-emerald-light text-emerald' : 'bg-rose-light text-rose' }}">
                            {{ $feeStructure->pro_rata_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="setting-row px-4 py-3 border-bottom">
                        <span class="x-small text-uppercase fw-bold text-muted">Late Fee</span>
                        <span class="small fw-bold text-dark mt-1">
                            {{ $feeStructure->late_fee_amount > 0 ? 'KSh ' . number_format($feeStructure->late_fee_amount, 2) : 'None' }}
                        </span>
                    </div>
                    <div class="setting-row px-4 py-3">
                        <span class="x-small text-uppercase fw-bold text-muted">Payment Frequency</span>
                        <span class="small fw-bold text-dark mt-1 text-capitalize">{{ str_replace('-', ' ', $feeStructure->payment_frequency) }}</span>
                    </div>
                </div>
            </div>

            @if($feeStructure->notes)
            <div class="show-panel mt-4 border-amber-soft">
                <div class="show-panel-body bg-amber-light p-3">
                    <span class="x-small text-uppercase fw-bold text-amber letter-sp mb-2 d-block">
                        <i class="fas fa-sticky-note me-1"></i> Notes
                    </span>
                    <p class="mb-0 small text-dark">{{ $feeStructure->notes }}</p>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --slate: #475569; --slate-light: #f1f5f9;
    --text: #1e293b; --muted: #64748b;
    --border: #e2e8f0;
}
.dash-wrap { padding: 2rem 2.5rem; background: #f8fafc; min-height: 100vh; }
.show-heading { font-size: 1.5rem; font-weight: 900; color: var(--text); letter-spacing: -0.02em; }
.show-sub { color: var(--muted); font-size: 0.875rem; }
.show-icon-box { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }

.show-panel { background: #fff; border-radius: 16px; border: 1px solid var(--border); overflow: hidden; }
.show-panel-header { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); background: #fafbfc; }
.show-panel-body { padding: 1.5rem; }
.show-panel-title { font-size: 0.95rem; font-weight: 850; }

.show-btn { display: inline-flex; align-items: center; padding: 0.6rem 1.2rem; border-radius: 10px; font-size: 0.8rem; font-weight: 800; border: none; text-decoration: none !important; transition: all 200ms; }
.show-btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text); }
.show-btn-ghost:hover { background: var(--slate-light); }
.show-btn-amber { background: var(--amber); color: #fff; }
.show-btn-amber:hover { background: #d97706; transform: translateY(-1px); }

.show-badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 999px; font-size: 0.7rem; font-weight: 800; }

.row-selected { background-color: #f5f7ff !important; }
.setting-row { display: flex; flex-direction: column; }

.fw-850 { font-weight: 850; }
.x-small { font-size: 0.7rem; }
.letter-sp { letter-spacing: 0.06em; }
.opacity-10 { opacity: 0.1; }

.bg-indigo { background-color: var(--indigo) !important; }
.bg-indigo-light { background-color: var(--indigo-light); }
.text-indigo { color: var(--indigo); }
.bg-emerald-light { background-color: var(--emerald-light); }
.text-emerald { color: var(--emerald); }
.bg-rose-light { background-color: var(--rose-light); }
.text-rose { color: var(--rose); }
.bg-amber-light { background-color: var(--amber-light); }
.text-amber { color: var(--amber); }
.bg-slate-light { background-color: var(--slate-light); }
.text-slate { color: var(--slate); }
.border-amber-soft { border-color: #fde68a !important; }
</style>
@endsection
