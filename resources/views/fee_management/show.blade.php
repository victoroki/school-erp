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
            <!-- Left Column: Student Profile & Quick Actions -->
            <div class="col-md-4">
                <div class="card card-primary card-outline shadow-sm">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            @if($student->photo_url)
                                <img class="profile-user-img img-fluid img-circle" src="{{ $student->photo_url }}" alt="Student Picture">
                            @else
                                <div class="profile-user-img img-fluid img-circle bg-secondary d-flex justify-content-center align-items-center mx-auto" style="width: 100px; height: 100px;">
                                    <i class="fas fa-user fa-3x"></i>
                                </div>
                            @endif
                        </div>

                        <h3 class="profile-username text-center mt-3">{{ $student->full_name }}</h3>
                        <p class="text-muted text-center">{{ $student->admission_no }}</p>

                        <div class="text-center mb-3">
                            <a href="{{ route('fee-management.collect-payment', $student->student_id) }}" class="btn btn-success btn-block">
                                <i class="fas fa-cash-register mr-2"></i> Collect Payment
                            </a>
                        </div>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Class</b> <span class="float-right badge badge-info">{{ $student->studentClassEnrollments->first()->classSection->schoolClass->name ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Admission Date</b> <span class="float-right">{{ $student->admission_date ? $student->admission_date->format('d M Y') : 'N/A' }}</span>
                            </li>
                            <li class="list-group-item pt-3">
                                <h6 class="font-weight-bold">Financial Summary</h6>
                                <div class="d-flex justify-content-between mt-2">
                                    <span>Total Assigned:</span>
                                    <span class="font-weight-bold">KSh {{ number_format($student->total_fee, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <span>Total Paid:</span>
                                    <span class="text-success font-weight-bold">KSh {{ number_format($student->paid_fee, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mt-1 border-top pt-1">
                                    <span>Outstanding:</span>
                                    <span class="text-danger font-weight-bold">KSh {{ number_format($student->balance_fee, 2) }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Parents Contact Card -->
                <div class="card shadow-sm">
                    <div class="card-header border-0">
                        <h3 class="card-title"><i class="fas fa-users mr-2"></i> Parents</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-pills flex-column">
                            @forelse($student->parents as $parent)
                                <li class="nav-item p-3 border-bottom">
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-bold text-dark">{{ $parent->first_name }} {{ $parent->last_name }}</span>
                                        <span class="text-muted small"><i class="fas fa-phone mr-1"></i> {{ $parent->phone ?? 'No Phone' }}</span>
                                        <span class="text-muted small"><i class="fas fa-envelope mr-1"></i> {{ $parent->user->email ?? 'No Email' }}</span>
                                    </div>
                                </li>
                            @empty
                                <li class="nav-item p-3 text-center text-muted italic">No parent info found.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Details Tabs -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#fees" data-toggle="tab"><i class="fas fa-file-invoice-dollar mr-1"></i> Assigned Fees</a></li>
                            <li class="nav-item"><a class="nav-link" href="#payments" data-toggle="tab"><i class="fas fa-history mr-1"></i> Payment History</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Fees Tab -->
                            <div class="active tab-pane" id="fees">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fee Type</th>
                                                <th>Due Date</th>
                                                <th class="text-right">Base</th>
                                                <th class="text-right">Discount</th>
                                                <th class="text-right">Final</th>
                                                <th class="text-right">Balance</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($student->studentFees as $fee)
                                                <tr>
                                                    <td class="font-weight-bold text-dark">{{ $fee->feeStructure->category->name ?? 'N/A' }}</td>
                                                    <td>{{ $fee->due_date ? $fee->due_date->format('d M Y') : 'N/A' }}</td>
                                                    <td class="text-right">KSh {{ number_format($fee->amount, 2) }}</td>
                                                    <td class="text-right text-success">-KSh {{ number_format($fee->discount_amount, 2) }}</td>
                                                    <td class="text-right font-weight-bold">KSh {{ number_format($fee->final_amount, 2) }}</td>
                                                    <td class="text-right text-danger font-weight-bold">KSh {{ number_format($fee->balance, 2) }}</td>
                                                    <td class="text-center">
                                                        @php
                                                            $statusClass = match($fee->status) {
                                                                'paid' => 'success',
                                                                'partially_paid' => 'warning',
                                                                default => 'danger'
                                                            };
                                                        @endphp
                                                        <span class="badge badge-{{ $statusClass }} px-2 py-1">
                                                            {{ ucfirst(str_replace('_', ' ', $fee->status)) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Payment History Tab -->
                            <div class="tab-pane" id="payments">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Receipt No</th>
                                                <th>Method</th>
                                                <th class="text-right">Amount Paid</th>
                                                <th>Fee Applied To</th>
                                                <th>Collected By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($student->payments as $payment)
                                                <tr>
                                                    <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</td>
                                                    <td><span class="badge badge-light p-2 border">{{ $payment->receipt_number }}</span></td>
                                                    <td>
                                                        <span class="text-muted"><i class="fas fa-credit-card mr-1"></i> {{ $payment->payment_method }}</span>
                                                    </td>
                                                    <td class="text-right font-weight-bold text-success">KSh {{ number_format($payment->amount, 2) }}</td>
                                                    <td>{{ $payment->studentFee->feeStructure->category->name ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="small text-muted">{{ $payment->collectedBy->name ?? 'System' }}</span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-4 text-muted">No payments recorded yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
