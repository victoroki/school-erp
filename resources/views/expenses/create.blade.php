@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-file-invoice-dollar text-danger mr-2"></i>Record New Expense</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
            <div class="card-header bg-danger py-3">
                <h5 class="card-title text-white font-weight-bold mb-0">Expense Request Details</h5>
            </div>
            {!! Form::open(['route' => 'expenses.store', 'files' => true]) !!}
            <div class="card-body">
                <div class="row">
                    <!-- Expense Date Field -->
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold">Expense Date <span class="text-danger">*</span></label>
                        {!! Form::date('expense_date', \Carbon\Carbon::now()->format('Y-m-d'), ['class' => 'form-control border-0 bg-light rounded-lg', 'required']) !!}
                    </div>

                    <!-- Category Id Field -->
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold">Expense Category <span class="text-danger">*</span></label>
                        {!! Form::select('category_id', ['' => 'Select Category'] + $categories->toArray(), null, ['class' => 'form-control border-0 bg-light rounded-lg', 'required']) !!}
                    </div>

                    <!-- Amount Field -->
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold">Amount (KES) <span class="text-danger">*</span></label>
                        {!! Form::number('amount', null, ['class' => 'form-control border-0 bg-light rounded-lg', 'step' => '0.01', 'required', 'placeholder' => 'Enter amount']) !!}
                    </div>

                    <!-- Supplier/Payee Field -->
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold">Supplier / Payee</label>
                        {!! Form::select('supplier_id', ['' => 'Select Supplier (Optional)'] + $suppliers->toArray(), null, ['class' => 'form-control border-0 bg-light rounded-lg']) !!}
                    </div>

                    <!-- Requested By Field -->
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold">Requested By</label>
                        {!! Form::select('requested_by', ['' => 'Select Staff'] + $staff->toArray(), auth()->id(), ['class' => 'form-control border-0 bg-light rounded-lg']) !!}
                    </div>

                    <!-- Payment Method Field -->
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold">Preferred Payment Method</label>
                        {!! Form::select('payment_method', [
                            'cash' => 'Cash',
                            'check' => 'Check',
                            'bank_transfer' => 'Bank Transfer',
                            'online' => 'Online/M-Pesa'
                        ], 'cash', ['class' => 'form-control border-0 bg-light rounded-lg', 'id' => 'payment_method']) !!}
                    </div>

                    <!-- Bank Account Id Field -->
                    <div class="form-group col-sm-6 d-none" id="bank_account_div">
                        <label class="font-weight-bold">Bank Account (for non-cash payments)</label>
                        {!! Form::select('bank_account_id', ['' => 'Select Account'] + $bankAccounts->toArray(), null, ['class' => 'form-control border-0 bg-light rounded-lg']) !!}
                    </div>

                    <!-- Reference Number Field -->
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold">Reference Number (e.g. Invoice #)</label>
                        {!! Form::text('reference_number', null, ['class' => 'form-control border-0 bg-light rounded-lg', 'placeholder' => 'Invoice or bill number']) !!}
                    </div>

                    <!-- Description Field -->
                    <div class="form-group col-sm-12">
                        <label class="font-weight-bold">Description / Purpose of Expense <span class="text-danger">*</span></label>
                        {!! Form::textarea('description', null, ['class' => 'form-control border-0 bg-light rounded-lg', 'rows' => 3, 'required', 'placeholder' => 'What is this expense for?']) !!}
                    </div>

                    <!-- Attachment Field -->
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold">Attachment (Invoice / Quotation Scan)</label>
                        <div class="custom-file">
                            {!! Form::file('attachment', ['class' => 'custom-file-input', 'id' => 'attachment']) !!}
                            <label class="custom-file-label border-0 bg-light rounded-lg" for="attachment">Choose file</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-light py-4 text-center">
                {!! Form::submit('Submit Expense Request', ['class' => 'btn btn-danger rounded-pill px-5 py-2 font-weight-bold shadow-sm']) !!}
                <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary rounded-pill px-5 py-2 ml-2">Cancel</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            function toggleBank() {
                var method = $('#payment_method').val();
                if (method === 'cash') {
                    $('#bank_account_div').addClass('d-none');
                } else {
                    $('#bank_account_div').removeClass('d-none');
                }
            }

            $('#payment_method').change(toggleBank);
            toggleBank();

            $('.custom-file-input').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        });
    </script>
@endsection
