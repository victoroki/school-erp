@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-exchange-alt text-primary mr-2"></i>Record Bank Transaction</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('bank-transactions.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Ledger
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-lg">
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">
                            Record a deposit, withdrawal or transfer between accounts. The account balance
                            and the bank ledger are updated together.
                        </p>

                        {!! Form::open(['route' => 'bank-transactions.store']) !!}

                        <div class="form-group">
                            <label class="small text-uppercase font-weight-bold text-muted">Transaction Type</label>
                            <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                <label class="btn btn-outline-success rounded-left active">
                                    <input type="radio" name="transaction_type" value="deposit" checked>
                                    <i class="fas fa-arrow-down mr-1"></i> Deposit
                                </label>
                                <label class="btn btn-outline-danger">
                                    <input type="radio" name="transaction_type" value="withdrawal">
                                    <i class="fas fa-arrow-up mr-1"></i> Withdrawal
                                </label>
                                <label class="btn btn-outline-primary rounded-right">
                                    <input type="radio" name="transaction_type" value="transfer">
                                    <i class="fas fa-exchange-alt mr-1"></i> Transfer
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="small text-uppercase font-weight-bold text-muted">{{ __('Account') }}</label>
                            {!! Form::select('account_id', $bankAccounts, null, ['class' => 'form-control', 'required' => true, 'placeholder' => 'Select account']) !!}
                        </div>

                        <div class="form-group d-none" id="target-account-group">
                            <label class="small text-uppercase font-weight-bold text-muted">{{ __('Transfer To') }}</label>
                            {!! Form::select('target_account_id', $bankAccounts, null, ['class' => 'form-control', 'placeholder' => 'Select destination account']) !!}
                            <small class="form-text text-muted">Money moves from the account above into this one.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small text-uppercase font-weight-bold text-muted">Amount (KES)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">KES</span>
                                        </div>
                                        {!! Form::number('amount', null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0.01', 'required' => true, 'placeholder' => '0.00']) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small text-uppercase font-weight-bold text-muted">Date</label>
                                    {!! Form::date('transaction_date', now()->toDateString(), ['class' => 'form-control', 'required' => true]) !!}
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="small text-uppercase font-weight-bold text-muted">Description</label>
                            {!! Form::text('description', null, ['class' => 'form-control', 'placeholder' => 'e.g. Term 2 banked fees, wages payment']) !!}
                        </div>

                        <div class="form-group">
                            <label class="small text-uppercase font-weight-bold text-muted">Reference Number</label>
                            {!! Form::text('reference_number', null, ['class' => 'form-control', 'placeholder' => 'e.g. bank slip or cheque number']) !!}
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="fas fa-check mr-1"></i> Record Transaction
                            </button>
                        </div>

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show the destination picker only for transfers.
        document.querySelectorAll('input[name="transaction_type"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                document.getElementById('target-account-group')
                    .classList.toggle('d-none', this.value !== 'transfer');
            });
        });
    </script>
@endsection
