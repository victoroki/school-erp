@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-clock text-warning mr-2"></i>Pending Approvals</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card border-0 shadow-sm rounded-lg">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="bg-light text-muted small text-uppercase">
                                <th class="pl-4 border-0">Date</th>
                                <th class="border-0">Description / Payee</th>
                                <th class="border-0">Requested By</th>
                                <th class="border-0 text-right">Amount</th>
                                <th class="border-0 text-center pr-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td class="pl-4 py-3 align-middle font-weight-bold">{{ $expense->expense_date->format('d M, Y') }}</td>
                                    <td class="py-3 align-middle">
                                        <span class="d-block font-weight-bold">{{ $expense->description }}</span>
                                        <small class="text-muted">Category: {{ $expense->category ? $expense->category->name : 'N/A' }}</small>
                                    </td>
                                    <td class="py-3 align-middle">
                                        {{ $expense->requestedBy ? $expense->requestedBy->full_name : 'Staff' }}
                                    </td>
                                    <td class="py-3 align-middle text-right text-danger font-weight-bold">
                                        KES {{ number_format($expense->amount, 2) }}
                                    </td>
                                    <td class="py-3 align-middle text-center pr-4">
                                        <div class="btn-group">
                                            <a href="{{ route('expenses.show', [$expense->expense_id]) }}" class="btn btn-sm btn-outline-info rounded-circle mr-1" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            {!! Form::open(['route' => ['expenses.approve', $expense->expense_id], 'method' => 'post', 'class' => 'd-inline']) !!}
                                            {!! Form::button('<i class="fas fa-check"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-outline-success rounded-circle mr-1', 'title' => 'Approve']) !!}
                                            {!! Form::close() !!}
                                            
                                            <button class="btn btn-sm btn-outline-danger rounded-circle reject-btn" data-id="{{ $expense->expense_id }}" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-20"></i><br>
                                        All caught up! No pending approvals.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-3">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
@endsection
