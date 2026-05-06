@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- Header Section --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box-md bg-emerald-light text-emerald shadow-sm">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h1 class="dash-heading-sm mb-0">Fee Structures</h1>
                <p class="dash-sub-sm mb-0">Billing overview for <span class="text-dark fw-bold">{{ $selectedYear->name ?? 'Active Session' }}</span></p>
            </div>
        </div>
        <a href="{{ route('fee-structures.create') }}" class="btn-dash-emerald shadow-sm">
            <i class="fas fa-plus me-2"></i> New Fee
        </a>
    </div>

    @include('flash::message')

    {{-- Year Navigation Bar - Premium Style --}}
    <div class="year-nav-wrapper mb-4">
        <div class="year-nav-card shadow-sm border">
            <div class="d-flex align-items-center justify-content-between px-2 py-1">
                @if($yearNavigation['previous'])
                    <a href="{{ route('fee-structures.index', ['academic_year_id' => $yearNavigation['previous']->academic_year_id]) }}" class="nav-btn" title="Previous Year">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                @else
                    <button class="nav-btn opacity-25" disabled><i class="fas fa-chevron-left"></i></button>
                @endif

                <div class="current-year-display d-flex align-items-center gap-2">
                    <span class="fw-900 text-slate h5 mb-0">{{ $selectedYear->name ?? 'Unknown Year' }}</span>
                    @if($selectedYear && $selectedYear->is_current)
                        <span class="badge bg-emerald-light text-emerald px-2 py-1 rounded-pill x-small fw-850">CURRENT</span>
                    @endif
                </div>

                @if($yearNavigation['next'])
                    <a href="{{ route('fee-structures.index', ['academic_year_id' => $yearNavigation['next']->academic_year_id]) }}" class="nav-btn" title="Next Year">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @else
                    <button class="nav-btn opacity-25" disabled><i class="fas fa-chevron-right"></i></button>
                @endif
            </div>
        </div>
    </div>

    {{-- Filter Bar - Clean & Aligned --}}
    <div class="dash-panel shadow-sm border-0 mb-5 p-3 bg-white rounded-4">
        <form action="{{ route('fee-structures.index') }}" method="GET" class="m-0">
            <input type="hidden" name="academic_year_id" value="{{ request('academic_year_id') }}">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-5">
                    <label class="x-small text-uppercase fw-850 text-muted mb-2 d-block ms-1">Filter by Class Level</label>
                    <select name="class_id" class="form-control select2">
                        <option value="">All Classes</option>
                        @foreach($classes as $id => $name)
                            <option value="{{ $id }}" {{ request('class_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-4 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-indigo-hub flex-grow-1 fw-bold">
                        <i class="fas fa-filter me-2"></i> Apply Filters
                    </button>
                    <a href="{{ route('fee-structures.index') }}" class="btn btn-light border px-3 shadow-sm rounded-3 text-muted" title="Reset View">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Content Section - The "Unified Hub Grid" --}}
    <div class="row g-3">
        @php
            // Group by Class + Term combined into one card
            $classTermGroups = $feeStructures->groupBy(function($item) {
                return ($item->schoolClass->name ?? 'Unassigned') . '|' . ($item->term ?? 'General');
            });
        @endphp

        @forelse($classTermGroups as $key => $items)
            @php 
                list($className, $termName) = explode('|', $key); 
            @endphp
            <div class="col-xxl-3 col-xl-4 col-md-6">
                <div class="hub-card h-100 shadow-sm border-0 bg-white d-flex flex-column transition-all hover-lift">
                    {{-- Card Header --}}
                    <div class="hub-card-header px-3 py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0 fw-850 text-dark" style="font-size: 0.95rem;">{{ $className }}</h6>
                            <span class="text-muted x-small fw-700 text-uppercase letter-spacing-1">{{ $termName }}</span>
                        </div>
                        <span class="badge bg-indigo-light text-indigo px-2 py-1 x-small fw-850 rounded-pill">{{ $items->count() }} Items</span>
                    </div>

                    {{-- Card Body --}}
                    <div class="hub-card-body px-3 py-2 flex-grow-1">
                        <div class="hub-fee-list">
                            @foreach($items as $fee)
                                <div class="hub-fee-row d-flex justify-content-between align-items-center py-2 border-bottom-soft">
                                    <span class="text-truncate x-small fw-600 text-slate pe-2">{{ $fee->category->name }}</span>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-850 text-dark x-small">KSh {{ number_format($fee->amount, 0) }}</span>
                                        <div class="hub-actions opacity-0">
                                            <a href="{{ route('fee-structures.edit', $fee->fee_structure_id) }}" class="text-amber x-small" title="Edit"><i class="fas fa-pencil-alt"></i></a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="hub-card-footer px-3 py-3 bg-light-soft border-top mt-auto d-flex justify-content-between align-items-center">
                        <div class="d-flex flex-column">
                            <span class="x-small text-muted fw-bold text-uppercase" style="font-size: 0.6rem;">Total Commitment</span>
                            <span class="fw-900 text-indigo" style="font-size: 1rem;">KSh {{ number_format($items->sum('amount'), 0) }}</span>
                        </div>
                        <a href="{{ route('fee-structures.show', $items->first()->fee_structure_id) }}" class="btn-hub-link">
                            Details <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="dash-panel p-5 text-center shadow-sm border-0 bg-white rounded-4">
                    <i class="fas fa-search fa-3x text-muted opacity-25 mb-3"></i>
                    <h5 class="fw-850">No Fee Structures Found</h5>
                    <p class="text-muted small">Try adjusting your class filter or navigating to a different academic year.</p>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-5 d-flex justify-content-center">
        {{ $feeStructures->appends(request()->query())->links() }}
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff; --indigo-soft: #e0e7ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --amber: #f59e0b; 
    --slate: #475569; --slate-light: #f1f5f9;
    --text: #1e293b; --muted: #64748b;
    --border: #e2e8f0;
}

.dash-wrap { padding: 2rem 2.5rem; background: #f8fafc; min-height: 100vh; }
.dash-heading-sm { font-size: 1.5rem; font-weight: 900; color: var(--text); letter-spacing: -0.02em; }
.dash-sub-sm { color: var(--muted); font-size: 0.85rem; font-weight: 500; }

.icon-box-md { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.125rem; }
.x-small { font-size: 0.725rem; }
.fw-900 { font-weight: 900; }
.fw-850 { font-weight: 850; }
.fw-700 { font-weight: 700; }
.fw-600 { font-weight: 600; }
.letter-spacing-1 { letter-spacing: 0.05em; }

/* Year Navigation */
.year-nav-card { background: #fff; border-radius: 14px; max-width: 400px; margin: 0 auto; overflow: hidden; }
.nav-btn { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; color: var(--text); border-radius: 10px; transition: all 200ms; border: none; background: transparent; }
.nav-btn:hover:not(:disabled) { background: var(--slate-light); color: var(--indigo); transform: scale(1.1); }

/* Hub Cards */
.hub-card { border-radius: 16px; border: 1px solid var(--border); transition: all 300ms ease; overflow: hidden; }
.hub-card:hover { border-color: var(--indigo-soft); }
.hover-lift:hover { transform: translateY(-6px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.06) !important; }

.bg-light-soft { background-color: #fafbfc; }
.border-bottom-soft { border-bottom: 1px solid #f1f5f9; }
.border-bottom-soft:last-child { border-bottom: none; }

.btn-indigo-hub { background: var(--indigo); color: #fff; padding: 0.65rem 1.5rem; border-radius: 10px; border: none; font-size: 0.85rem; transition: all 200ms; }
.btn-indigo-hub:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }

.btn-dash-emerald { background: var(--emerald); color: #fff; padding: 0.65rem 1.25rem; border-radius: 10px; font-size: 0.85rem; font-weight: 800; border: none; text-decoration: none !important; transition: all 200ms; }
.btn-dash-emerald:hover { background: #059669; transform: translateY(-1px); }

.btn-hub-link { font-size: 0.75rem; font-weight: 800; color: var(--indigo); text-decoration: none !important; transition: all 150ms; display: flex; align-items: center; }
.btn-hub-link:hover { color: #4338ca; transform: translateX(3px); }

.hub-fee-row:hover .hub-actions { opacity: 1; }
.hub-actions { transition: opacity 200ms; }

.dash-panel { background: #fff; border-radius: 16px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }

/* Fixed Select2 - Visiblity & Alignment */
.select2-container--default .select2-selection--single {
    height: 44px !important;
    border-radius: 10px !important;
    border: 1px solid var(--border) !important;
    background-color: #fcfdfe !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered { 
    line-height: 42px !important; 
    font-size: 0.875rem !important; 
    font-weight: 600 !important;
    padding-left: 15px !important;
    color: var(--text) !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow { 
    height: 42px !important; 
    right: 10px !important;
}
</style>

@push('page_scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'default',
            width: '100%'
        });
    });
</script>
@endpush
@endsection
