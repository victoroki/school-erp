@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Student Fee Summary</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('fees.assignments.index') }}">
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row">
            <div class="col-md-3">
                <!-- Profile Image -->
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            @if($student->image)
                                <img class="profile-user-img img-fluid img-circle" src="{{ asset($student->image) }}" alt="User profile picture">
                            @else
                                <img class="profile-user-img img-fluid img-circle" src="https://ui-avatars.com/api/?name={{ urlencode($student->first_name . ' ' . $student->last_name) }}" alt="User profile picture">
                            @endif
                        </div>

                        <h3 class="profile-username text-center">{{ $student->first_name }} {{ $student->last_name }}</h3>

                        <p class="text-muted text-center">{{ $student->admission_number }}</p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Class</b> <a class="float-right">{{ $student->schoolClass->name ?? '-' }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Status</b> <a class="float-right">{{ $student->status ? 'Active' : 'Inactive' }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="card card-info">
                     <div class="card-header">
                        <h3 class="card-title">Balance</h3>
                    </div>
                    <div class="card-body">
                        <h5>Net Payable <span class="float-right text-primary">{{ number_format($netPayable) }}</span></h5>
                        <h5>Paid <span class="float-right text-success">{{ number_format($totalPaid) }}</span></h5>
                        <hr>
                        <h4>Due <span class="float-right {{ $balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($balance) }}</span></h4>
                        
                        <a href="{{ route('fee-management.collect-payment', $student->student_id) }}" class="btn btn-block btn-success mt-3"><b>Collect Payment</b></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#assignments" data-toggle="tab">Assigned Fees</a></li>
                            <li class="nav-item"><a class="nav-link" href="#history" data-toggle="tab">Payment History</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="active tab-pane" id="assignments">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Term</th>
                                            <th>Base Amount</th>
                                            <th>Discount</th>
                                            <th>Final</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assignments as $assignment)
                                        <tr>
                                            <td>{{ $assignment->feeStructure->category->name }}</td>
                                            <td>{{ $assignment->term }}</td>
                                            <td>{{ number_format($assignment->amount) }}</td>
                                            <td>
                                                @if($assignment->discount_amount > 0)
                                                    <span class="text-danger">-{{ number_format($assignment->discount_amount) }}</span>
                                                    @if($assignment->discount) <small>({{ $assignment->discount->name }})</small> @endif
                                                @else
                                                  0
                                                @endif
                                            </td>
                                            <td><b>{{ number_format($assignment->final_amount) }}</b></td>
                                            <td>
                                                 {!! Form::open(['route' => ['fees.assignments.destroy', $assignment->id], 'method' => 'delete', 'style' => 'display:inline']) !!}
                                                     <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Remove?')">X</button>
                                                 {!! Form::close() !!}
                                            </td>
                                        </tr>
                                        @endforeach
                                        <tr class="bg-light">
                                            <td colspan="2"><strong>TOTALS</strong></td>
                                            <td><strong>{{ number_format($totalAmount) }}</strong></td>
                                            <td class="text-danger"><strong>-{{ number_format($totalDiscount) }}</strong></td>
                                            <td><strong class="text-primary">{{ number_format($netPayable) }}</strong></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="tab-pane" id="history">
                                <!-- Placeholder for payment history from Finance module -->
                                <p class="text-muted text-center pt-3">No payments recorded yet for this period.</p>
                                <div class="text-center">
                                     <a href="{{ route('fee-management.collect-payment', $student->student_id) }}" class="btn btn-primary btn-sm">Record New Payment</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
