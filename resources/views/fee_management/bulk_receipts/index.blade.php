@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-7">
                    <h1><i class="fas fa-hand-holding-usd text-success mr-2"></i>Bulk Bursary / Sponsor Receipts</h1>
                    <p class="text-muted mb-0">Money received once from a sponsor and distributed to students. The unallocated remainder always stays on the receipt.</p>
                </div>
                <div class="col-sm-5 text-right">
                    @can('fees.manage')
                        <a href="{{ route('fees.bulk-receipts.create') }}" class="btn btn-success shadow-sm">
                            <i class="fas fa-plus mr-1"></i> New Bulk Receipt
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        {{-- Reconciliation over the whole filtered set: received must equal
             allocated + unallocated, so nobody has to total the column by hand. --}}
        <div class="row mb-3">
            <div class="col-lg-3 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Receipts</div>
                        <div class="h4 font-weight-bold mb-0">{{ number_format($totals->receipts_count ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Received</div>
                        <div class="h4 font-weight-bold mb-0">{{ \App\Support\Money::format($receivedTotal) }}</div>
                        @if(((float) $totals->reversed_total) > 0)
                            <div class="small text-danger">{{ \App\Support\Money::format($totals->reversed_total) }} reversed (excluded)</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Allocated to Students</div>
                        <div class="h4 font-weight-bold mb-0 text-success">{{ \App\Support\Money::format($allocatedTotal) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <div class="text-muted small font-weight-bold text-uppercase">Unallocated</div>
                        <div class="h4 font-weight-bold mb-0 {{ $unallocatedTotal > 0 ? 'text-warning' : 'text-muted' }}">{{ \App\Support\Money::format($unallocatedTotal) }}</div>
                        <div class="small text-muted">Received − allocated</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="GET" class="form-inline flex-wrap gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" style="min-width: 220px;"
                           placeholder="Search sponsor or reference..."
                           value="{{ $filters['search'] ?? '' }}">
                    <select name="sponsor_type" class="form-control form-control-sm select2" style="min-width: 180px;">
                        <option value="">All sponsor types</option>
                        @foreach($sponsorTypes as $type)
                            <option value="{{ $type }}" {{ ($filters['sponsor_type'] ?? '') === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Filter</button>
                    @if($filters['search'] ?? $filters['sponsor_type'] ?? false)
                        <a href="{{ route('fees.bulk-receipts.index') }}" class="btn btn-sm btn-default">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light small text-muted uppercase">
                        <tr>
                            <th class="pl-4">Sponsor</th>
                            <th>Reference</th>
                            <th>Received</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Allocated</th>
                            <th class="text-right">Remaining</th>
                            <th>Status</th>
                            <th class="pr-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                            @php $remaining = $receipt->remainingAmount(); @endphp
                            <tr>
                                <td class="pl-4">
                                    <div class="font-weight-bold text-dark">{{ $receipt->sponsor_name }}</div>
                                    <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $receipt->sponsor_type)) }}</div>
                                </td>
                                <td class="small">{{ $receipt->reference_number ?? '—' }}</td>
                                <td class="small">{{ $receipt->received_date->format('d M Y') }}</td>
                                <td class="text-right font-weight-bold">{{ \App\Support\Money::format($receipt->amount) }}</td>
                                <td class="text-right">{{ \App\Support\Money::format($receipt->allocatedAmount()) }}</td>
                                <td class="text-right {{ $remaining > 0 ? 'text-warning font-weight-bold' : 'text-muted' }}">{{ \App\Support\Money::format($remaining) }}</td>
                                <td>
                                    @if($receipt->isReversed())
                                        <span class="badge badge-danger">Reversed</span>
                                    @elseif($remaining <= 0)
                                        <span class="badge badge-success">Fully allocated</span>
                                    @elseif($receipt->allocatedAmount() > 0)
                                        <span class="badge badge-info">Partly allocated</span>
                                    @else
                                        <span class="badge badge-warning">Unallocated</span>
                                    @endif
                                </td>
                                <td class="pr-4 text-right text-nowrap">
                                    <a href="{{ route('fees.bulk-receipts.receipt', $receipt->id) }}" class="btn btn-sm btn-outline-secondary" title="Print the sponsor receipt">
                                        <i class="fas fa-print mr-1"></i> Receipt
                                    </a>
                                    <a href="{{ route('fees.bulk-receipts.show', $receipt->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye mr-1"></i> Open
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-hand-holding-usd fa-2x d-block mb-2 opacity-50"></i>
                                    No bulk receipts yet.
                                    @can('fees.manage')
                                        <a href="{{ route('fees.bulk-receipts.create') }}">Record the first sponsor receipt</a>.
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $receipts->links() }}
            </div>
        </div>
    </div>
@endsection
