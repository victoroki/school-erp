@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-receipt text-danger mr-2"></i>Expense Transactions</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('expenses.create') }}" class="btn btn-danger rounded-pill px-4 shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Record New Expense
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
                <form action="{{ route('expenses.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="small text-uppercase font-weight-bold text-muted">Category</label>
                            {!! Form::select('category_id', ['' => 'All Categories'] + $categories->toArray(), request('category_id'), ['class' => 'form-control border-0 bg-light rounded-pill']) !!}
                        </div>
                        <div class="col-md-3">
                            <label class="small text-uppercase font-weight-bold text-muted">Status</label>
                            {!! Form::select('status', [
                                '' => 'All Statuses',
                                'pending' => 'Pending Approval',
                                'approved' => 'Approved (Unpaid)',
                                'paid' => 'Paid',
                                'rejected' => 'Rejected'
                            ], request('status'), ['class' => 'form-control border-0 bg-light rounded-pill']) !!}
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm mr-2">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Reset</a>
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
                                <th class="border-0">Description / Payee</th>
                                <th class="border-0">Category</th>
                                <th class="border-0 text-right">Amount</th>
                                <th class="border-0 text-center">Status</th>
                                <th class="border-0 text-center pr-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td class="pl-4 py-3 align-middle font-weight-bold">{{ $expense->expense_date->format('d M, Y') }}</td>
                                    <td class="py-3 align-middle">
                                        <span class="d-block font-weight-bold text-truncate" style="max-width: 250px;">{{ $expense->description ?: 'No description' }}</span>
                                        <small class="text-muted">Payee: {{ $expense->supplier ? $expense->supplier->name : 'N/A' }} • {{ ucfirst($expense->payment_method) }}</small>
                                    </td>
                                    <td class="py-3 align-middle">
                                        <span class="badge badge-danger-light text-danger px-3 py-2 rounded-pill font-weight-bold">
                                            {{ $expense->category ? $expense->category->name : 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="py-3 align-middle text-right text-danger font-weight-bold">
                                        KES {{ number_format($expense->amount, 2) }}
                                    </td>
                                    <td class="py-3 align-middle text-center">
                                        @php
                                            $statusClass = [
                                                'draft' => 'secondary',
                                                'pending' => 'warning',
                                                'approved' => 'info',
                                                'paid' => 'success',
                                                'rejected' => 'danger'
                                            ][$expense->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge badge-{{ $statusClass }} px-3 py-2 text-uppercase small font-weight-bold">{{ $expense->status }}</span>
                                    </td>
                                    <td class="py-3 align-middle text-center pr-4">
                                        <div class="btn-group">
                                            <a href="{{ route('expenses.show', [$expense->expense_id]) }}" class="btn btn-sm btn-outline-info rounded-circle mr-1" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($expense->status == 'pending' && Auth::user()->hasRole('admin'))
                                                {!! Form::open(['route' => ['expenses.approve', $expense->expense_id], 'method' => 'post', 'class' => 'd-inline']) !!}
                                                {!! Form::button('<i class="fas fa-check"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-outline-success rounded-circle mr-1', 'title' => 'Approve']) !!}
                                                {!! Form::close() !!}
                                            @endif
                                            
                                            @if($expense->status == 'approved' && Auth::user()->hasRole('admin'))
                                                {!! Form::open(['route' => ['expenses.pay', $expense->expense_id], 'method' => 'post', 'class' => 'd-inline']) !!}
                                                {!! Form::button('<i class="fas fa-hand-holding-usd"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-outline-primary rounded-circle mr-1', 'title' => 'Mark as Paid']) !!}
                                                {!! Form::close() !!}
                                            @endif

                                            {!! Form::open(['route' => ['expenses.destroy', $expense->expense_id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                            {!! Form::button('<i class="fas fa-trash"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-outline-danger rounded-circle', 'onclick' => "return confirm('Are you sure?')"]) !!}
                                            {!! Form::close() !!}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-receipt fa-3x mb-3 opacity-20"></i><br>
                                        No expense records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <p class="mb-0 text-muted small">Showing {{ $expenses->firstItem() }} to {{ $expenses->lastItem() }} of {{ $expenses->total() }} records</p>
                    {{ $expenses->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .badge-danger-light { background-color: #fee2e2; }
        .bg-light { background-color: #f8fafc !important; }
        .rounded-pill { border-radius: 50rem !important; }
        .opacity-20 { opacity: 0.2; }
        .table-hover tbody tr:hover { background-color: #f1f5f9; cursor: pointer; }
    </style>
@endsection
