@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-plus-circle text-primary mr-2"></i>Add Bank Account</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
            <div class="card-header bg-primary py-3">
                <h5 class="card-title text-white font-weight-bold mb-0">Account Information</h5>
            </div>

            {!! Form::open(['route' => 'bank-accounts.store']) !!}
            <div class="card-body">
                <div class="row">
                    <!-- Account Name Field -->
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold dark-text">Account Name <span class="text-danger">*</span></label>
                        {!! Form::text('account_name', null, ['class' => 'form-control border-0 bg-light rounded-lg', 'required', 'placeholder' => 'e.g. Main Operations Account']) !!}
                    </div>

                    <!-- Account Number Field -->
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold dark-text">Account Number <span class="text-danger">*</span></label>
                        {!! Form::text('account_number', null, ['class' => 'form-control border-0 bg-light rounded-lg', 'required', 'placeholder' => 'Enter account number']) !!}
                    </div>

                    <!-- Bank Name Field -->
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold dark-text">Bank Name <span class="text-danger">*</span></label>
                        {!! Form::text('bank_name', null, ['class' => 'form-control border-0 bg-light rounded-lg', 'required', 'placeholder' => 'e.g. Equity Bank, KCB, etc.']) !!}
                    </div>

                    <!-- Branch Name Field -->
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold dark-text">Branch Name</label>
                        {!! Form::text('branch_name', null, ['class' => 'form-control border-0 bg-light rounded-lg', 'placeholder' => 'Enter branch name']) !!}
                    </div>

                    <!-- Account Type Field -->
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold dark-text">Account Type</label>
                        {!! Form::select('account_type', [
                            'Savings' => 'Savings',
                            'Current' => 'Current',
                            'Business' => 'Business',
                            'Mobile Money' => 'Mobile Money (M-Pesa)',
                            'Petty Cash' => 'Petty Cash'
                        ], 'Current', ['class' => 'form-control border-0 bg-light rounded-lg']) !!}
                    </div>

                    <!-- Account Holder Field -->
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold dark-text">Account Holder Name</label>
                        {!! Form::text('account_holder', null, ['class' => 'form-control border-0 bg-light rounded-lg', 'placeholder' => 'Name as it appears in bank']) !!}
                    </div>

                    <!-- Opening Balance Field -->
                    <div class="form-group col-sm-4">
                        <label class="font-weight-bold dark-text">Opening Balance <span class="text-danger">*</span></label>
                        {!! Form::number('opening_balance', 0, ['class' => 'form-control border-0 bg-light rounded-lg', 'step' => '0.01', 'required']) !!}
                    </div>

                    <!-- Minimum Balance Field -->
                    <div class="form-group col-sm-4">
                        <label class="font-weight-bold dark-text">Minimum Balance Threshold</label>
                        {!! Form::number('minimum_balance', 0, ['class' => 'form-control border-0 bg-light rounded-lg', 'step' => '0.01']) !!}
                    </div>
                    
                    <!-- Currency Field -->
                    <div class="form-group col-sm-4">
                        <label class="font-weight-bold dark-text">Currency</label>
                        {!! Form::select('currency', ['KES' => 'KES (Kenyan Shilling)', 'USD' => 'USD (US Dollar)'], 'KES', ['class' => 'form-control border-0 bg-light rounded-lg']) !!}
                    </div>

                    <!-- Hidden Current Balance (Init with Opening) -->
                    {!! Form::hidden('current_balance', 0, ['id' => 'current_balance']) !!}
                </div>
            </div>

            <div class="card-footer bg-light py-4 text-center">
                {!! Form::submit('Save Bank Account', ['class' => 'btn btn-primary rounded-pill px-5 py-2 font-weight-bold shadow-sm']) !!}
                <a href="{{ route('bank-accounts.index') }}" class="btn btn-outline-secondary rounded-pill px-5 py-2 ml-2">Cancel</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    <style>
        .dark-text { color: #1e293b; }
    </style>
@endsection

@push('page_scripts')
    <script>
        $(document).ready(function() {
            $('input[name="opening_balance"]').on('input', function() {
                $('#current_balance').val($(this).val());
            });
        });
    </script>
@endpush
