@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1><i class="fas fa-plus-circle text-success mr-2"></i>New Bulk Receipt</h1>
                    <p class="text-muted mb-0">Record money received from a sponsor. Students are allocated from this receipt afterwards — allocation never posts the money again.</p>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ route('fees.bulk-receipts.index') }}" class="btn btn-default border shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Receipts
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card border-0 shadow-sm">
            {!! Form::open(['route' => 'fees.bulk-receipts.store']) !!}
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        {!! Form::label('sponsor_name', 'Sponsor Name *') !!}
                        {!! Form::text('sponsor_name', old('sponsor_name'), ['class' => 'form-control', 'placeholder' => 'e.g. NG-CDF Constituency Office', 'required']) !!}
                        <small class="form-text text-muted">The organization or office that gave the money.</small>
                    </div>

                    <div class="form-group col-md-3">
                        {!! Form::label('sponsor_type', 'Sponsor Type *') !!}
                        <select name="sponsor_type" class="form-control select2" required>
                            @foreach($sponsorTypes as $type)
                                <option value="{{ $type }}" {{ old('sponsor_type') === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-3">
                        {!! Form::label('reference_number', 'Reference Number') !!}
                        {!! Form::text('reference_number', old('reference_number'), ['class' => 'form-control', 'placeholder' => 'Cheque / slip / letter ref']) !!}
                    </div>

                    <div class="form-group col-md-3">
                        {!! Form::label('amount', 'Amount Received (KES) *') !!}
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">KES</span></div>
                            {!! Form::number('amount', old('amount'), ['class' => 'form-control', 'step' => '0.01', 'min' => '0.01', 'placeholder' => 'e.g. 5000000', 'required']) !!}
                        </div>
                        <small class="form-text text-muted">The full amount received, in one receipt.</small>
                    </div>

                    <div class="form-group col-md-3">
                        {!! Form::label('payment_method', 'Payment Method *') !!}
                        <select name="payment_method" class="form-control" required>
                            @foreach(['bank_transfer' => 'Bank Transfer', 'cash' => 'Cash', 'check' => 'Cheque', 'card' => 'Card', 'online' => 'Online / M-Pesa'] as $value => $label)
                                <option value="{{ $value }}" {{ old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-3">
                        {!! Form::label('received_date', 'Date Received *') !!}
                        {!! Form::date('received_date', old('received_date', \Carbon\Carbon::today()), ['class' => 'form-control', 'required']) !!}
                    </div>

                    <div class="form-group col-md-3">
                        {!! Form::label('bank_account_id', 'Bank Account (if banked)') !!}
                        <select name="bank_account_id" class="form-control select2">
                            <option value="">— Not banked yet —</option>
                            @foreach($bankAccounts as $accountId => $accountName)
                                <option value="{{ $accountId }}" {{ old('bank_account_id') == $accountId ? 'selected' : '' }}>{{ $accountName }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Selecting one records a single deposit for the whole amount. Student allocations never post money again.</small>
                    </div>

                    <div class="form-group col-md-4">
                        {!! Form::label('academic_year_id', 'Academic Year') !!}
                        <select name="academic_year_id" class="form-control select2">
                            <option value="">— Not year specific —</option>
                            @foreach($academicYears as $yearId => $yearName)
                                <option value="{{ $yearId }}" {{ old('academic_year_id') == $yearId ? 'selected' : '' }}>{{ $yearName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        {!! Form::label('term_id', 'Term') !!}
                        <select name="term_id" class="form-control select2">
                            <option value="">— Not term specific —</option>
                            @foreach($terms as $termId => $termName)
                                <option value="{{ $termId }}" {{ old('term_id') == $termId ? 'selected' : '' }}>{{ $termName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        {!! Form::label('transaction_id', 'Transaction Reference') !!}
                        {!! Form::text('transaction_id', old('transaction_id'), ['class' => 'form-control', 'placeholder' => 'Bank / M-Pesa code']) !!}
                    </div>

                    <div class="form-group col-12">
                        {!! Form::label('remarks', 'Remarks') !!}
                        {!! Form::textarea('remarks', old('remarks'), ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'e.g. Bursary award letter dated..., disbursement conditions...']) !!}
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end">
                <button type="submit" class="btn btn-success px-4">
                    <i class="fas fa-save mr-1"></i> Record Receipt
                </button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
