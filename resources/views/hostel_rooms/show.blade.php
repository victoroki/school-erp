@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Room Details: {{ $hostelRoom->room_number }} ({{ $hostelRoom->hostel->name }})</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-default" href="{{ route('hostel-rooms.index') }}">Back</a>
                    <a class="btn btn-primary" href="{{ route('hostel-rooms.edit', $hostelRoom->room_id) }}">Edit Room</a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">Room Info</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Hostel</b> <span class="float-right">{{ $hostelRoom->hostel->name }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Floor</b> <span class="float-right">{{ $hostelRoom->floor ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Type</b> <span class="float-right">{{ ucfirst($hostelRoom->room_type) }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Capacity</b> <span class="float-right">{{ $hostelRoom->capacity }} Beds</span>
                            </li>
                            <li class="list-group-item">
                                <b>Occupied</b> <span class="float-right">{{ $hostelRoom->occupied }} Beds</span>
                            </li>
                            <li class="list-group-item border-bottom-0">
                                <b>Status</b> 
                                <span class="float-right badge badge-{{ $hostelRoom->status == 'available' ? 'success' : ($hostelRoom->status == 'full' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($hostelRoom->status) }}
                                </span>
                            </li>
                        </ul>

                        @if($hostelRoom->maintenance_notes)
                            <div class="alert alert-warning mt-2">
                                <h5><i class="icon fas fa-tools"></i> Maintenance Notes</h5>
                                {{ $hostelRoom->maintenance_notes }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Current Occupants</h3>
                        <div class="card-tools">
                            @if($hostelRoom->occupied < $hostelRoom->capacity && $hostelRoom->status !== 'under_maintenance')
                                <a href="{{ route('hostel-allocations.create', ['room_id' => $hostelRoom->room_id]) }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-plus mr-1"></i> Add Occupant
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Bed #</th>
                                    <th>Allotted Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hostelRoom->hostelAllocations->where('status', 'active') as $allocation)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-graduate mr-2 text-muted"></i>
                                                <div>
                                                    <strong>{{ $allocation->student->first_name }} {{ $allocation->student->last_name }}</strong><br>
                                                    <small class="text-muted">{{ $allocation->student->student_id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $allocation->bed_number }}</td>
                                        <td>{{ $allocation->allocation_date->format('d M, Y') }}</td>
                                        <td>
                                            <a href="{{ route('hostel-allocations.show', $allocation->allocation_id) }}" class="btn btn-xs btn-default">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <p class="text-muted mb-0">No active students in this room.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
