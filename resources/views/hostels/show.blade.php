@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Hostel Details: {{ $hostel->name }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-default" href="{{ route('hostels.index') }}">Back</a>
                    <a class="btn btn-primary" href="{{ route('hostels.edit', $hostel->hostel_id) }}">Edit</a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <!-- Summary Sidebar -->
            <div class="col-md-3">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <i class="fas fa-hotel fa-3x text-primary mb-3"></i>
                        </div>
                        <h3 class="profile-username text-center">{{ $hostel->name }}</h3>
                        <p class="text-muted text-center"><span class="badge badge-info">{{ ucfirst($hostel->type) }}</span></p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Warden</b> <a class="float-right text-dark font-weight-bold">{{ $hostel->warden->first_name ?? 'N/A' }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Rooms</b> <a class="float-right">{{ $hostel->hostelRooms->count() }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Total Capacity</b> <a class="float-right">{{ $hostel->capacity }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>Occupied</b> <a class="float-right">{{ $hostel->getCurrentOccupancy() }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Location</h3>
                    </div>
                    <div class="card-body">
                        <strong><i class="fas fa-map-marker-alt mr-1"></i> Address</strong>
                        <p class="text-muted">{{ $hostel->address }}</p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#rooms" data-toggle="tab">Rooms</a></li>
                            <li class="nav-item"><a class="nav-link" href="#students" data-toggle="tab">Students List</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Rooms Tab -->
                            <div class="tab-pane active" id="rooms">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Room #</th>
                                                <th>Type</th>
                                                <th>Beds</th>
                                                <th>Occupied</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($hostel->hostelRooms as $room)
                                                <tr>
                                                    <td><strong>{{ $room->room_number }}</strong></td>
                                                    <td>{{ ucfirst($room->room_type) }}</td>
                                                    <td>{{ $room->capacity }}</td>
                                                    <td>{{ $room->occupied }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $room->status == 'available' ? 'success' : ($room->status == 'full' ? 'danger' : 'warning') }}">
                                                            {{ ucfirst($room->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('hostel-rooms.show', $room->room_id) }}" class="btn btn-xs btn-default"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Students Tab -->
                            <div class="tab-pane" id="students">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Room</th>
                                                <th>Bed</th>
                                                <th>Since</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($hostel->hostelAllocations->where('status', 'active') as $allocation)
                                                <tr>
                                                    <td>{{ $allocation->student->student_id }}</td>
                                                    <td>{{ $allocation->student->first_name }} {{ $allocation->student->last_name }}</td>
                                                    <td>Room {{ $allocation->room->room_number }}</td>
                                                    <td>{{ $allocation->bed_number }}</td>
                                                    <td>{{ $allocation->allocation_date->format('d M, Y') }}</td>
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
    </div>
@endsection
