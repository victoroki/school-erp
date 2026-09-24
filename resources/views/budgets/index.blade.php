@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-chart-pie text-primary mr-2"></i>Budgets</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('budgets.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Add New Budget
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
                <form action="{{ route('budgets.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="small text-uppercase font-weight-bold text-muted">Financial Year</label>
                            {!! Form::select('financial_year_id', ['' => 'All Financial Years'] + $financialYearOptions->toArray(), request('financial_year_id', $selectedYear), ['class' => 'form-control border-0 bg-light rounded-pill']) !!}
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm mr-2">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('budgets.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-lg">
            @include('budgets.table')
        </div>
    </div>

    <style>
        .badge-danger-light { background-color: #fee2e2; }
        .badge-success-light { background-color: #d1fae5; }
        .bg-light { background-color: #f8fafc !important; }
        .rounded-pill { border-radius: 50rem !important; }
        .opacity-20 { opacity: 0.2; }
        .table-hover tbody tr:hover { background-color: #f1f5f9; cursor: pointer; }
    </style>

@endsection