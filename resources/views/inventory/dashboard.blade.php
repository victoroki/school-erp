@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">Inventory Dashboard</h1>
            <p class="dash-sub">Overview of stock levels, movements, and alerts</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <ol class="breadcrumb float-sm-right m-0 bg-transparent p-0" style="font-size: .813rem; font-weight: 600;">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-muted text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-dark">Inventory</li>
            </ol>
        </div>
    </div>

    {{-- ② QUICK STATS --}}
    <div class="row mb-4">
        <div class="col-12 col-sm-6 col-md-3 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-emerald-light text-emerald"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Total Value</span>
                    <span class="stat-value" style="font-size: 1.25rem;">KES {{ number_format($totalValue, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-blue-light text-blue"><i class="fas fa-boxes"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Items In Stock</span>
                    <span class="stat-value">{{ $itemsInStock }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-amber-light text-amber"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Low Stock Alerts</span>
                    <span class="stat-value">{{ $lowStockItems->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <div class="stat-card">
                <div class="stat-icon bg-rose-light text-rose"><i class="fas fa-times-circle"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Out of Stock</span>
                    <span class="stat-value">{{ $outOfStockItems->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content Column -->
        <div class="col-lg-8 mb-4 mb-lg-0">
            <!-- Recent Transactions -->
            <div class="dash-panel mb-4">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-history text-indigo"></i>
                        <h3 class="dash-panel-title">Recent Stock Movements</h3>
                    </div>
                    <a href="{{ route('inventory.stock-movement-history') }}" class="btn-dash btn-ghost py-1 px-2" style="font-size: .688rem;">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Item</th>
                                <th>Type</th>
                                <th class="text-center">Qty</th>
                                <th>By</th>
                                <th class="text-center">Bal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $transaction)
                                <tr>
                                    <td class="text-muted" style="font-size: .813rem;">{{ $transaction->transaction_date->format('M d, Y') }}</td>
                                    <td><span class="font-weight-bold text-dark" style="font-size: .875rem;">{{ $transaction->item->name ?? 'N/A' }}</span></td>
                                    <td>{!! $transaction->typeBadge !!}</td>
                                    <td class="text-center font-weight-bold text-dark" style="font-size: .875rem;">{{ $transaction->quantity }}</td>
                                    <td><span style="font-size: .813rem;">{{ $transaction->handledBy->name ?? 'N/A' }}</span></td>
                                    <td class="text-center"><span class="badge-soft">{{ $transaction->item->quantity ?? 0 }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-receipt fa-2x mb-2" style="color: #cbd5e1;"></i>
                                        <p class="mb-0" style="font-size: .875rem;">No recent transactions</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Low Stock Items -->
            @if($lowStockItems->count() > 0)
                <div class="dash-panel border-warning-light">
                    <div class="dash-panel-header bg-amber-light border-0">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-exclamation-triangle text-amber"></i>
                            <h3 class="dash-panel-title text-amber-dark">Low Stock Items</h3>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-center">Min</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lowStockItems as $item)
                                    <tr>
                                        <td><span class="font-weight-bold text-dark" style="font-size: .875rem;">{{ $item->name }}</span></td>
                                        <td><span class="text-muted" style="font-size: .813rem;">{{ $item->category->name ?? 'N/A' }}</span></td>
                                        <td class="text-center"><span class="text-rose font-weight-bold" style="font-size: .875rem;">{{ $item->quantity }}</span></td>
                                        <td class="text-center"><span class="text-muted" style="font-size: .813rem;">{{ $item->minimum_quantity }}</span></td>
                                        <td class="text-right p-2">
                                            <a href="{{ route('inventory.add-stock.form') }}?item={{ $item->item_id }}" class="action-btn" title="Add Stock" style="background: var(--blue-light); color: var(--blue);">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-bolt text-blue"></i>
                        <h3 class="dash-panel-title">Quick Actions</h3>
                    </div>
                </div>
                <div class="dash-panel-body p-2">
                    <div class="row m-0">
                        <div class="col-6 p-1">
                            <a href="{{ route('inventory.add-stock.form') }}" class="qa-btn">
                                <i class="fas fa-plus mb-1"></i> Add Stock
                            </a>
                        </div>
                        <div class="col-6 p-1">
                            <a href="{{ route('inventory.issue-stock.form') }}" class="qa-btn">
                                <i class="fas fa-minus mb-1"></i> Issue Stock
                            </a>
                        </div>
                        <div class="col-6 p-1">
                            <a href="{{ route('inventory.adjust-stock.form') }}" class="qa-btn">
                                <i class="fas fa-sync-alt mb-1"></i> Adjust Stock
                            </a>
                        </div>
                        <div class="col-6 p-1">
                            <a href="{{ route('inventory.stock-movement-history') }}" class="qa-btn">
                                <i class="fas fa-history mb-1"></i> History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Emil Kowalski Utility Suite ── */
:root {
    --blue: #3b82f6; --blue-light: #eff6ff;
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --amber-dark: #b45309;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --slate: #64748b;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.bg-blue-light { background: var(--blue-light); } .text-blue { color: var(--blue); }
.bg-indigo-light { background: var(--indigo-light); } .text-indigo { color: var(--indigo); }
.bg-emerald-light { background: var(--emerald-light); } .text-emerald { color: var(--emerald); }
.bg-amber-light { background: var(--amber-light); } .text-amber { color: var(--amber); } .text-amber-dark { color: var(--amber-dark); }
.bg-rose-light { background: var(--rose-light); } .text-rose { color: var(--rose); }

.border-warning-light { border-color: #fcd34d !important; }

.dash-wrap { padding: 1rem; }
.dash-heading { font-size: 1.375rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.813rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; display: flex; flex-direction: column; }
.dash-panel-header { padding: 1rem 1.25rem; background: #fff; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between; }
.dash-panel-title { font-size: .875rem; font-weight: 800; color: var(--text); margin: 0; }
.dash-panel-body { padding: 1.25rem; flex: 1; }

/* Quick Stats */
.stat-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 200ms var(--ease-out); }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.05); border-color: #cbd5e1; }
.stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.stat-info { display: flex; flex-direction: column; }
.stat-label { font-size: .7rem; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
.stat-value { font-size: 1.5rem; font-weight: 800; color: var(--text); line-height: 1.1; margin-top: .25rem; }

/* Table Styling */
.table { margin-bottom: 0; }
.table thead th { background: #f8fafc; border-bottom: 1px solid var(--border); font-size: .688rem; font-weight: 800; text-transform: uppercase; color: var(--slate); letter-spacing: 0.05em; padding: .625rem 1.25rem; }
.table tbody td { padding: .75rem 1.25rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; border-top: 0; }
.table tbody tr:last-child td { border-bottom: 0; }
.table-hover tbody tr:hover { background-color: #f8fafc; }

.badge-soft { background: #f1f5f9; color: #475569; font-size: .688rem; font-weight: 700; padding: .2rem .5rem; border-radius: 6px; }

/* Quick Actions */
.qa-btn { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem .5rem; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px; color: var(--slate); text-decoration: none !important; transition: all 150ms var(--ease-out); font-size: .75rem; font-weight: 600; text-align: center; }
.qa-btn i { font-size: 1.125rem; color: var(--text); transition: all 150ms ease; }
.qa-btn:hover { background: #fff; border-color: var(--blue); color: var(--blue); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1); }
.qa-btn:hover i { color: var(--blue); transform: translateY(-2px); }

/* Buttons */
.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .5rem .875rem; border-radius: 8px; font-size: .813rem; font-weight: 600; transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer; }
.btn-ghost { background: transparent; color: var(--muted); border-color: transparent; }
.btn-ghost:hover { background: #f1f5f9; color: var(--text); }
.action-btn { width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; color: var(--slate); transition: all 150ms ease; border: 1px solid transparent; background: transparent; font-size: .75rem; }
.action-btn:hover { background: #e2e8f0; color: var(--text); border-color: #cbd5e1; }
</style>
@endsection