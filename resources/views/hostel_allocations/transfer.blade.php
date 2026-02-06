@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Transfer Student: {{ $hostelAllocation->student->first_name }} {{ $hostelAllocation->student->last_name }}</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="row">
            <div class="col-md-4">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Current Allocation</h3>
                    </div>
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <i class="fas fa-user-graduate fa-3x text-primary"></i>
                        </div>
                        <h3 class="profile-username text-center">{{ $hostelAllocation->student->first_name }}</h3>
                        <p class="text-muted text-center">{{ $hostelAllocation->student->student_id }}</p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Hostel</b> <a class="float-right">{{ $hostelAllocation->hostel->name }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Room</b> <a class="float-right">{{ $hostelAllocation->room->room_number }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Allotted On</b> <a class="float-right">{{ $hostelAllocation->allocation_date->format('d M, Y') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">New Room Details</h3>
                    </div>
                    {!! Form::open(['route' => ['hostel-allocations.transfer-store', $hostelAllocation->allocation_id]]) !!}
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group col-sm-12">
                                {!! Form::label('room_id', 'Select New Room:') !!}
                                <select name="room_id" id="room_select" class="form-control select2" required>
                                    <option value="">-- Choose Target Room --</option>
                                    @foreach($rooms as $id => $label)
                                        @if($id != $hostelAllocation->room_id)
                                            <option value="{{ $id }}">{{ $label }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <small class="text-info"><i class="fas fa-info-circle mr-1"></i>Only showing rooms with available beds.</small>
                            </div>

                            <div class="form-group col-sm-12">
                                {!! Form::label('transfer_reason', 'Transfer Reason (Optional):') !!}
                                {!! Form::textarea('transfer_reason', null, ['class' => 'form-control', 'rows' => 3]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('hostel-allocations.index') }}" class="btn btn-default">Cancel</a>
                        {!! Form::submit('Execute Transfer', ['class' => 'btn btn-primary']) !!}
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4'
            });
        });
    </script>
@endpush
