@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="mb-1">Transfer Student</h1>
                    <p class="text-muted mb-0">The old bed is released and the new one taken in a single step — if it fails, nothing changes.</p>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ route('hostel-allocations.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Allocations
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="row">
            <div class="col-md-4">
                <div class="card card-outline card-secondary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-circle mr-1"></i> Current Placement</h3>
                    </div>
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <i class="fas fa-user-graduate fa-3x text-secondary"></i>
                        </div>
                        <h3 class="profile-username text-center">{{ optional($hostelAllocation->student)->first_name ?? 'N/A' }}</h3>
                        <p class="text-muted text-center">{{ optional($hostelAllocation->student)->student_id ?? 'No ID' }}</p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Hostel</b> <span class="float-right">{{ optional($hostelAllocation->hostel)->name ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Room</b> <span class="float-right">{{ optional($hostelAllocation->room)->room_number ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Bed</b> <span class="float-right">{{ $hostelAllocation->bed_number ?? 'Not assigned' }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Allocated On</b> <span class="float-right">{{ $hostelAllocation->allocation_date?->format('d M, Y') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-exchange-alt mr-1"></i> New Room</h3>
                    </div>
                    {!! Form::open(['route' => ['hostel-allocations.transfer-store', $hostelAllocation->allocation_id]]) !!}
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-sm-12">
                                {!! Form::label('room_id', 'Move to:') !!}
                                <select name="room_id" id="room_select" class="form-control select2" required style="width: 100%">
                                    <option value="">-- Choose Target Room --</option>
                                    @foreach($rooms as $roomId => $roomLabel)
                                        <option value="{{ $roomId }}" @selected((string) old('room_id') === (string) $roomId)>
                                            {{ $roomLabel }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>Only rooms with at least one free bed are listed. The student's gender is re-checked against the new hostel.
                                </small>
                            </div>

                            <div class="form-group col-sm-12">
                                {!! Form::label('transfer_reason', 'Reason for transfer (optional):') !!}
                                {!! Form::textarea('transfer_reason', old('transfer_reason'), ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Room maintenance, better fit, guardian request...']) !!}
                                <small class="form-text text-muted">Saved on the vacated record so the bed history stays readable.</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <a href="{{ route('hostel-allocations.show', $hostelAllocation->allocation_id) }}" class="btn btn-link px-0">View full record</a>
                        <div>
                            <a href="{{ route('hostel-allocations.index') }}" class="btn btn-default mr-2">Cancel</a>
                            {!! Form::submit('Execute Transfer', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
