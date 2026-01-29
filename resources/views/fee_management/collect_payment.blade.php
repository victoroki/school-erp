@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Record Student Payment</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('fee-management.index') }}">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row">
            <div class="col-md-4">
                <!-- Student Summary Card -->
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            @if($student->photo_url)
                                <img class="profile-user-img img-fluid img-circle" src="{{ $student->photo_url }}" alt="User profile picture">
                            @else
                                <div class="profile-user-img img-fluid img-circle bg-secondary d-flex justify-content-center align-items-center mx-auto" style="width: 100px; height: 100px;">
                                    <i class="fas fa-user fa-3x"></i>
                                </div>
                            @endif
                        </div>

                        <h3 class="profile-username text-center mt-3">{{ $student->full_name }}</h3>
                        <p class="text-muted text-center">{{ $student->admission_no }}</p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Total Fee</b> <a class="float-right text-dark font-weight-bold">{{ number_format($student->total_fee, 2) }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Amount Paid</b> <a class="float-right text-success">{{ number_format($student->paid_fee, 2) }}</a>
                            </li>
                            <li class="list-group-item border-bottom-0">
                                <b>Current Balance</b> <a class="float-right text-danger font-weight-bold">{{ number_format($student->balance_fee, 2) }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">Payment Details</h3>
                    </div>
                    <form action="{{ route('fee-management.store-payment', $student->student_id) }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="student_fee_id">Fee Category to Pay For <span class="text-danger">*</span></label>
                                        <select name="student_fee_id" id="student_fee_id" class="form-control select2" required>
                                            @foreach($student->studentFees as $fee)
                                                <option value="{{ $fee->student_fee_id }}" data-balance="{{ $fee->balance }}">
                                                    {{ $fee->feeStructure->category->name }} (Balance: {{ number_format($fee->balance, 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Select the specific fee structure this payment belongs to.</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="amount">Payment Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">KSh</span>
                                            </div>
                                            <input type="number" step="0.01" name="amount" id="amount" class="form-control" required placeholder="0.00">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="payment_date">Payment Date <span class="text-danger">*</span></label>
                                        <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="payment_method">Payment Method <span class="text-danger">*</span></label>
                                        <select name="payment_method" id="payment_method" class="form-control" required>
                                            <option value="Cash">Cash</option>
                                            <option value="Bank Transfer">Bank Transfer</option>
                                            <option value="Cheque">Cheque</option>
                                            <option value="Mobile Money">Mobile Money</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="transaction_id">Transaction ID / Reference (Optional)</label>
                                        <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="E.g. TXN-12345">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="remarks">Remarks (Optional)</label>
                                        <textarea name="remarks" id="remarks" class="form-control" rows="2" placeholder="Any additional notes..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="fas fa-save mr-2"></i> Confirm Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_scripts')
<script>
    $(document).ready(function() {
        $('#student_fee_id').on('change', function() {
            var balance = $(this).find(':selected').data('balance');
            $('#amount').val(balance);
            $('#amount').attr('max', balance);
        }).trigger('change');
    });
</script>
@endpush
