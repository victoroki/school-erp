@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fee Details: {{ $student->first_name }} {{ $student->last_name }}</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('fee-management.index') }}">
                        Back
                    </a>
                    <a class="btn btn-primary float-right mr-2" href="{{ route('fee-management.print', $student->student_id) }}" target="_blank">
                        <i class="fas fa-print"></i> Print Statement
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <h3 class="profile-username text-center">{{ $student->first_name }} {{ $student->last_name }}</h3>
                        <p class="text-muted text-center">{{ $student->admission_no }}</p>
                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Class</b> <a class="float-right">{{ $student->studentClassEnrollments->first()->classSection->schoolClass->name ?? 'N/A' }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Total Fee</b> <a class="float-right">{{ number_format($student->total_fee, 2) }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Paid</b> <a class="float-right">{{ number_format($student->paid_fee, 2) }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Balance</b> <a class="float-right text-danger">{{ number_format($student->balance_fee, 2) }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Status</b> 
                                <a class="float-right">
                                    @php
                                        $status = $student->payment_status;
                                        $badgeClass = match($status) {
                                            'Paid' => 'success',
                                            'Partial' => 'warning',
                                            'Unpaid' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }}">{{ $status }}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#fees" data-toggle="tab">Assigned Fees</a></li>
                            <li class="nav-item"><a class="nav-link" href="#payments" data-toggle="tab">Payment History</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="active tab-pane" id="fees">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Fee Type</th>
                                            <th>Due Date</th>
                                            <th>Amount</th>
                                            <th>Discount</th>
                                            <th>Final Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($student->studentFees as $fee)
                                            <tr>
                                                <td>{{ $fee->feeStructure->category->name ?? 'N/A' }}</td>
                                                <td>{{ $fee->due_date->format('d M Y') }}</td>
                                                <td>{{ number_format($fee->amount, 2) }}</td>
                                                <td>{{ number_format($fee->discount_amount, 2) }}</td>
                                                <td>{{ number_format($fee->final_amount, 2) }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $fee->status == 'paid' ? 'success' : ($fee->status == 'partially_paid' ? 'warning' : 'danger') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $fee->status)) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="tab-pane" id="payments">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Receipt No</th>
                                            <th>Method</th>
                                            <th>Amount</th>
                                            <th>Fee Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($student->payments as $payment)
                                            <tr>
                                                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                                <td>{{ $payment->receipt_number }}</td>
                                                <td>{{ ucfirst($payment->payment_method) }}</td>
                                                <td>{{ number_format($payment->amount, 2) }}</td>
                                                <td>{{ $payment->studentFee->feeStructure->category->name ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
