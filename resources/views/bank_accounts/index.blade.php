@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-university text-primary mr-2"></i>Bank Accounts</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('bank-accounts.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Add Account
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row">
            @foreach($bankAccounts as $account)
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden h-100 hvr-float">
                        <div class="card-header pb-0 border-0 bg-white pt-4 px-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge badge-light px-3 py-2 rounded-pill text-muted small font-weight-bold">{{ $account->account_type }}</span>
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" type="button" data-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow-sm border-0">
                                        <a class="dropdown-item" href="{{ route('bank-accounts.show', [$account->account_id]) }}"><i class="fas fa-eye mr-2"></i> Details</a>
                                        <a class="dropdown-item" href="{{ route('bank-accounts.edit', [$account->account_id]) }}"><i class="fas fa-edit mr-2"></i> Edit</a>
                                        <div class="dropdown-divider"></div>
                                        {!! Form::open(['route' => ['bank-accounts.destroy', $account->account_id], 'method' => 'delete']) !!}
                                            {!! Form::button('<i class="fas fa-trash mr-2"></i> Delete', ['type' => 'submit', 'class' => 'dropdown-item text-danger', 'onclick' => "return confirm('Are you sure?')"]) !!}
                                        {!! Form::close() !!}
                                    </div>
                                </div>
                            </div>
                            <h5 class="font-weight-bold text-dark mb-1">{{ $account->account_name }}</h5>
                            <p class="text-muted small mb-0">{{ $account->bank_name }} - {{ $account->account_number }}</p>
                        </div>
                        <div class="card-body px-4 py-4">
                            <div class="mb-4">
                                <p class="text-muted small text-uppercase font-weight-bold mb-1">Available Balance</p>
                                <h3 class="font-weight-bold text-primary">KES {{ number_format($account->current_balance, 2) }}</h3>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center small mb-2">
                                <span class="text-muted">Status</span>
                                <span class="badge badge-{{ $account->status == 'active' ? 'success' : 'danger' }}-light text-{{ $account->status == 'active' ? 'success' : 'danger' }} px-3 py-1 rounded-pill">
                                    {{ ucfirst($account->status ?? 'Active') }}
                                </span>
                            </div>
                            
                            @if($account->minimum_balance > 0)
                                <div class="progress rounded-pill mb-1" style="height: 6px;">
                                    @php
                                        $percent = ($account->current_balance / max($account->opening_balance, $account->current_balance + 1)) * 100;
                                        $color = $account->current_balance < $account->minimum_balance ? 'bg-danger' : 'bg-success';
                                    @endphp
                                    <div class="progress-bar {{ $color }}" role="progressbar" style="width: {{ min($percent, 100) }}%"></div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Min: {{ number_format($account->minimum_balance, 0) }}</span>
                                    <span>{{ round($percent) }}% health</span>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-light border-0 py-3 px-4">
                            <a href="{{ route('bank-transactions.index', ['account_id' => $account->account_id]) }}" class="btn btn-sm btn-link text-primary font-weight-bold p-0">
                                VIEW TRANSACTIONS <i class="fas fa-chevron-right ml-1 small"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($bankAccounts->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-university fa-4x text-muted opacity-20 mb-3"></i>
                <h4 class="text-muted">No bank accounts found</h4>
                <a href="{{ route('bank-accounts.create') }}" class="btn btn-primary rounded-pill px-4 mt-3">Add your first account</a>
            </div>
        @endif
    </div>

    <style>
        .badge-success-light { background-color: #dcfce7; }
        .badge-danger-light { background-color: #fee2e2; }
        .hvr-float {
            transition: transform 0.3s;
        }
        .hvr-float:hover {
            transform: translateY(-8px);
        }
        .opacity-20 { opacity: 0.2; }
    </style>
@endsection
