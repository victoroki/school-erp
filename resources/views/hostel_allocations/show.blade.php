@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Allocation Record: {{ $hostelAllocation->student->first_name }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-default" href="{{ route('hostel-allocations.index') }}">Back</a>
                    @if($hostelAllocation->status === 'active')
                        <a href="{{ route('hostel-allocations.transfer-form', $hostelAllocation->allocation_id) }}" class="btn btn-info">Transfer</a>
                    @endif
                    <a class="btn btn-primary" href="{{ route('hostel-allocations.edit', $hostelAllocation->allocation_id) }}">Edit Record</a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <div class="col-md-5">
                <!-- Profile Card -->
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <i class="fas fa-id-card-alt fa-3x text-primary shadow-sm p-3 rounded-circle border mb-3"></i>
                        </div>
                        <h3 class="profile-username text-center">{{ $hostelAllocation->student->first_name }} {{ $hostelAllocation->student->last_name }}</h3>
                        <p class="text-muted text-center">{{ $hostelAllocation->student->student_id }}</p>

                        <div class="text-center mb-3">
                            <span class="badge badge-{{ $hostelAllocation->status == 'active' ? 'success' : ($hostelAllocation->status == 'vacated' ? 'danger' : 'warning') }} px-3 py-2">
                                Status: {{ ucfirst($hostelAllocation->status) }}
                            </span>
                        </div>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Hostel</b> <a class="float-right text-dark font-weight-bold">{{ $hostelAllocation->hostel->name }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Room Number</b> <a class="float-right text-dark font-weight-bold">{{ $hostelAllocation->room->room_number }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Bed</b> <a class="float-right">{{ $hostelAllocation->bed_number ?? 'Not Specified' }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Academic Year</b> <a class="float-right">{{ $hostelAllocation->academicYear->name ?? 'N/A' }}</a>
                            </li>
                        </ul>
                        
                        @if($hostelAllocation->status === 'active')
                            <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#checkoutModal">
                                <b><i class="fas fa-sign-out-alt mr-1"></i> Checkout Student</b>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Placement History & Notes</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-sm-6">
                                <strong><i class="fas fa-calendar-check mr-1"></i> Allocation Date</strong>
                                <p class="text-muted">{{ $hostelAllocation->allocation_date->format('d M, Y') }}</p>
                            </div>
                            <div class="col-sm-6">
                                <strong><i class="fas fa-calendar-times mr-1"></i> Vacating Date</strong>
                                <p class="text-muted">{{ $hostelAllocation->vacating_date ? $hostelAllocation->vacating_date->format('d M, Y') : 'Not yet vacated' }}</p>
                            </div>
                        </div>

                        <hr>

                        <strong><i class="fas fa-file-alt mr-1"></i> Checkout / Internal Notes</strong>
                        <div class="p-3 bg-light rounded mt-2" style="min-height: 100px;">
                            {{ $hostelAllocation->checkout_notes ?? 'No checkout notes recorded.' }}
                        </div>
                        
                        <div class="mt-4">
                            <small class="text-muted">Record ID: #{{ $hostelAllocation->allocation_id }} | Created: {{ $hostelAllocation->created_at->format('d M, Y H:i') }}</small>
                        </div>
                    </div>
                </div>
                
                @if($hostelAllocation->status === 'active')
                    <!-- Quick Actions -->
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <h5 class="text-info"><i class="fas fa-info-circle mr-1"></i> Operations</h5>
                            <p class="small text-muted">Use the "Transfer" button to move this student to another available room within the system.</p>
                            <a href="{{ route('hostel-allocations.transfer-form', $hostelAllocation->allocation_id) }}" class="btn btn-outline-info">
                                <i class="fas fa-exchange-alt mr-1"></i> Initiate Room Transfer
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($hostelAllocation->status === 'active')
        <!-- Checkout Modal -->
        <div class="modal fade" id="checkoutModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    {!! Form::open(['route' => ['hostel-allocations.checkout', $hostelAllocation->allocation_id]]) !!}
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Checkout Confirmation</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to checkout <strong>{{ $hostelAllocation->student->first_name }}</strong> from Room <strong>{{ $hostelAllocation->room->room_number }}</strong>?</p>
                        <p class="small text-muted">This will free up the bed and mark the allocation as vacated.</p>
                        <div class="form-group mt-3 text-left">
                            {!! Form::label('checkout_notes', 'Checkout Notes:') !!}
                            {!! Form::textarea('checkout_notes', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Add notes about room condition, keys, etc.']) !!}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger text-white">Confirm Checkout</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    @endif
@endsection
