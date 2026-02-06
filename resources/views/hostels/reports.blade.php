@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Hostel Reports</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <!-- Vacancy Report -->
            <div class="col-md-6">
                <div class="card card-outline card-info h-100">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-bed mr-2"></i>Vacancy & Capacity Report</h3>
                    </div>
                    <div class="card-body">
                        <p>Generate a detailed list of all available rooms and bed counts across all hostels.</p>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Total Capacity</th>
                                    <th>Currently Occupied</th>
                                    <th>Available Beds</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ \App\Models\HostelRoom::sum('capacity') }}</td>
                                    <td>{{ \App\Models\HostelRoom::sum('occupied') }}</td>
                                    <td class="text-success font-weight-bold">{{ \App\Models\HostelRoom::sum('capacity') - \App\Models\HostelRoom::sum('occupied') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('hostel.vacancy-report') }}" class="btn btn-info btn-block">
                            <i class="fas fa-print mr-1"></i> View Vacancy Report
                        </a>
                    </div>
                </div>
            </div>

            <!-- Student List by Hostel -->
            <div class="col-md-6">
                <div class="card card-outline card-primary h-100">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users mr-2"></i>Hostel Student List</h3>
                    </div>
                    <div class="card-body">
                        <p>Get a complete list of students currently staying in each hostel, filterable by hostel and room.</p>
                        <form action="{{ route('hostel.student-list') }}" method="GET">
                            <div class="form-group">
                                <label>Filter by Hostel:</label>
                                <select name="hostel_id" class="form-control select2">
                                    <option value="">All Hostels</option>
                                    @foreach(\App\Models\Hostel::pluck('name', 'hostel_id') as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-search mr-1"></i> Generate Student List
                        </button>
                    </div>
                        </form>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Summary Stats -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h3 class="card-title">Hostel Performance Summary</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Hostel</th>
                                    <th>Type</th>
                                    <th>Total Rooms</th>
                                    <th>Capacity</th>
                                    <th>Occupied</th>
                                    <th>Availability</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\Hostel::withCount('hostelRooms')->get() as $hostel)
                                    @php
                                        $occ = $hostel->getCurrentOccupancy();
                                        $cap = $hostel->hostelRooms()->sum('capacity');
                                        $perc = $cap > 0 ? round(($occ / $cap) * 100) : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $hostel->name }}</td>
                                        <td><span class="badge badge-secondary">{{ ucfirst($hostel->type) }}</span></td>
                                        <td>{{ $hostel->hostel_rooms_count }}</td>
                                        <td>{{ $cap }}</td>
                                        <td>{{ $occ }}</td>
                                        <td>
                                            <div class="progress progress-xs" style="width: 100px;">
                                                <div class="progress-bar bg-{{ $perc > 90 ? 'danger' : ($perc > 50 ? 'warning' : 'success') }}" 
                                                     style="width: {{ $perc }}%"></div>
                                            </div>
                                            <small>{{ $perc }}% Full</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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
