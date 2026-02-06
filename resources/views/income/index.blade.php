@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-list text-success mr-2"></i>Income Transactions</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('income.create') }}" class="btn btn-success rounded-pill px-4 shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Record New Income
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="clearfix"></div>

        <!-- Filters -->
        <div class="card border-0 shadow-sm rounded-lg mb-4">
            <div class="card-body">
                <form action="{{ route('income.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="small text-uppercase font-weight-bold text-muted">Category</label>
                            {!! Form::select('category_id', ['' => 'All Categories'] + $categories->toArray(), request('category_id'), ['class' => 'form-control border-0 bg-light rounded-pill']) !!}
                        </div>
                        <div class="col-md-2">
                            <label class="small text-uppercase font-weight-bold text-muted">Start Date</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control border-0 bg-light rounded-pill">
                        </div>
                        <div class="col-md-2">
                            <label class="small text-uppercase font-weight-bold text-muted">End Date</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control border-0 bg-light rounded-pill">
                        </div>
                        <div class="col-md-3">
                            <label class="small text-uppercase font-weight-bold text-muted">Payment Method</label>
                            {!! Form::select('payment_method', ['' => 'All Methods', 'cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'check' => 'Check', 'online' => 'Online/M-Pesa'], request('payment_method'), ['class' => 'form-control border-0 bg-light rounded-pill']) !!}
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block rounded-pill shadow-sm">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-lg">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="bg-light text-muted small text-uppercase">
                                <th class="pl-4 border-0">Date</th>
                                <th class="border-0">Payer / Source</th>
                                <th class="border-0">Category</th>
                                <th class="border-0">Bank Account</th>
                                <th class="border-0 text-right">Amount</th>
                                <th class="border-0 text-center pr-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incomes as $income)
                                <tr>
                                    <td class="pl-4 py-3 align-middle font-weight-bold">{{ $income->income_date->format('d M, Y') }}</td>
                                    <td class="py-3 align-middle">
                                        <span class="d-block font-weight-bold">{{ $income->payer_name ?: 'General Source' }}</span>
                                        <small class="text-muted">{{ $income->description ?: 'No details' }}</small>
                                    </td>
                                    <td class="py-3 align-middle">
                                        <span class="badge badge-success-light text-success px-3 py-2 rounded-pill font-weight-bold">
                                            {{ $income->category ? $income->category->name : 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 align-middle text-muted small">
                                        {{ $income->bankAccount ? $income->bankAccount->account_name : 'Petty Cash / Other' }}<br>
                                        <span class="text-muted">{{ ucfirst($income->payment_method) }}</span>
                                    </td>
                                    <td class="py-3 align-middle text-right text-success font-weight-bold">
                                        KES {{ number_format($income->amount, 2) }}
                                    </td>
                                    <td class="py-3 align-middle text-center pr-4">
                                        <div class="btn-group">
                                            <a href="{{ route('income.show', [$income->income_id]) }}" class="btn btn-sm btn-outline-info rounded-circle mr-1" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            {!! Form::open(['route' => ['income.destroy', $income->income_id], 'method' => 'delete']) !!}
                                            {!! Form::button('<i class="fas fa-trash"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-outline-danger rounded-circle', 'onclick' => "return confirm('Are you sure you want to delete this record?')"]) !!}
                                            {!! Form::close() !!}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-search fa-3x mb-3 opacity-20"></i><br>
                                        No income records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <p class="mb-0 text-muted small">Showing {{ $incomes->firstItem() }} to {{ $incomes->lastItem() }} of {{ $incomes->total() }} records</p>
                    {{ $incomes->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .badge-success-light { background-color: #dcfce7; }
        .bg-light { background-color: #f8fafc !important; }
        .rounded-pill { border-radius: 50rem !important; }
        .opacity-20 { opacity: 0.2; }
        .table-hover tbody tr:hover { background-color: #f1f5f9; cursor: pointer; }
    </style>
@endsection
