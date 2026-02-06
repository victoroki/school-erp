@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><i class="fas fa-hotel mr-2"></i>Hostel Dashboard</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <!-- Top Stats -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_hostels'] }}</h3>
                        <p>Total Hostels</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <a href="{{ route('hostels.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['total_students'] }}</h3>
                        <p>Students in Hostel</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <a href="{{ route('hostel-allocations.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['total_capacity'] - $stats['total_occupied'] }}</h3>
                        <p>Available Beds</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-bed"></i>
                    </div>
                    <a href="{{ route('hostel.reports') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['maintenance_rooms'] }}</h3>
                        <p>Rooms under Maintenance</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <a href="{{ route('hostel-rooms.index') }}?status=under_maintenance" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Occupancy Chart -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header border-transparent">
                        <h3 class="card-title">Hostel Occupancy Rates</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table m-0">
                                <thead>
                                <tr>
                                    <th>Hostel Name</th>
                                    <th>Total Beds</th>
                                    <th>Occupied</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($occupancyByHostel as $hostel)
                                    <tr>
                                        <td>{{ $hostel->name }}</td>
                                        <td>{{ $hostel->total_capacity }}</td>
                                        <td>{{ $hostel->occupied }}</td>
                                        <td>
                                            <div class="progress progress-xs">
                                                <div class="progress-bar {{ $hostel->occupancy_rate > 90 ? 'bg-danger' : ($hostel->occupancy_rate > 70 ? 'bg-warning' : 'bg-success') }}" 
                                                     style="width: {{ $hostel->occupancy_rate }}%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $hostel->occupancy_rate >= 100 ? 'badge-danger' : ($hostel->occupancy_rate > 0 ? 'badge-warning' : 'badge-success') }}">
                                                {{ $hostel->occupancy_rate }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Allocations -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Allocations</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="products-list product-list-in-card pl-2 pr-2">
                            @foreach($recentAllocations as $allocation)
                                <li class="item">
                                    <div class="product-info">
                                        <a href="{{ route('hostel-allocations.show', $allocation->allocation_id) }}" class="product-title">
                                            {{ $allocation->student->first_name }} {{ $allocation->student->last_name }}
                                            <span class="badge badge-info float-right">{{ $allocation->allocation_date->format('M d') }}</span>
                                        </a>
                                        <span class="product-description">
                                            {{ $allocation->hostel->name }} - Room {{ $allocation->room->room_number }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-footer text-center">
                        <a href="{{ route('hostel-allocations.index') }}" class="uppercase">View All Allocations</a>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('hostel-allocations.create') }}" class="btn btn-app bg-success">
                            <i class="fas fa-plus"></i> New Allocation
                        </a>
                        <a href="{{ route('hostel-allocations.bulk-form') }}" class="btn btn-app bg-info">
                            <i class="fas fa-users"></i> Bulk Allocation
                        </a>
                        <a href="{{ route('hostel-rooms.create') }}" class="btn btn-app bg-warning">
                            <i class="fas fa-door-open"></i> Add Room
                        </a>
                        <a href="{{ route('hostel.reports') }}" class="btn btn-app bg-danger">
                            <i class="fas fa-file-pdf"></i> Vacancy Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
