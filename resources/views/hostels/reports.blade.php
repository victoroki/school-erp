@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="mb-1">Hostel Reports</h1>
                    <p class="text-muted mb-0">Vacancy, capacity and the allocation register by class and stream.</p>
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
                        <h3 class="card-title"><i class="fas fa-bed mr-2"></i>Vacancy &amp; Capacity Report</h3>
                    </div>
                    <div class="card-body">
                        <p>Every room with a free bed, with bed counts, and the rooms taken out of service.</p>
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Total Beds</th>
                                    <th>Occupied</th>
                                    <th>Free Beds</th>
                                    <th>Rooms in Maintenance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $totals['beds'] }}</td>
                                    <td>{{ $totals['residents'] }}</td>
                                    <td class="text-success font-weight-bold">{{ $totals['free_beds'] }}</td>
                                    <td>{{ $totals['maintenance_rooms'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('hostel.vacancy-report') }}" class="btn btn-info btn-block">
                            <i class="fas fa-chart-bar mr-1"></i> View Vacancy Report
                        </a>
                    </div>
                </div>
            </div>

            <!-- Student List by Hostel -->
            <div class="col-md-6">
                <div class="card card-outline card-primary h-100">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-users mr-2"></i>Allocation Register</h3>
                    </div>
                    <div class="card-body">
                        <p>Current residents filtered by hostel, year, class and stream, then exported as a PDF for the hostel office.</p>
                        <form id="hostel-register-form" action="{{ route('hostel.student-list') }}" method="GET">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Hostel</label>
                                    <select name="hostel_id" id="report_hostel_select" class="form-control select2" style="width: 100%">
                                        <option value="">All Hostels</option>
                                        @foreach($hostels as $hostel)
                                            <option value="{{ $hostel->hostel_id }}">{{ $hostel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Class</label>
                                    <select name="class_id" id="report_class_select" class="form-control select2" style="width: 100%">
                                        <option value="">All Classes</option>
                                        @foreach(\App\Models\SchoolClass::orderBy('numeric_value')->pluck('name', 'class_id') as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Stream</label>
                                    <select name="section_id" class="form-control select2" style="width: 100%">
                                        <option value="">All Streams</option>
                                        @foreach(\App\Models\Section::orderBy('name')->pluck('name', 'section_id') as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Academic Year</label>
                                    <select name="academic_year_id" class="form-control select2" style="width: 100%">
                                        <option value="">All Years</option>
                                        @foreach(\App\Models\AcademicYear::orderByDesc('start_date')->pluck('name', 'academic_year_id') as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <button type="submit" form="hostel-register-form" class="btn btn-primary btn-block">
                            <i class="fas fa-search mr-1"></i> Generate Allocation Register
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Summary Stats -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h3 class="card-title">Hostel Performance Summary</h3>
                        <small class="card-text">Occupancy is counted from the active bed allocations; capacity from the rooms on the ground.</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                <tr>
                                    <th>Hostel</th>
                                    <th>Type</th>
                                    <th>Rooms</th>
                                    <th>Beds</th>
                                    <th>Residents</th>
                                    <th>Free Beds</th>
                                    <th>Occupancy</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @forelse($hostels as $hostel)
                                        @php $perc = $hostel->getOccupancyPercentage(); @endphp
                                        <tr>
                                            <td>
                                                {{ $hostel->name }}
                                                @if($hostel->capacityMismatch() !== 0)
                                                    <br>
                                                    <small class="text-warning" title="The declared capacity in the hostel record does not match its rooms. Room totals are used everywhere in this report.">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                                        Declared capacity is {{ $hostel->capacity }}, rooms hold {{ $hostel->beds }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td><span class="badge badge-secondary">{{ ucfirst($hostel->type) }}</span></td>
                                            <td>{{ $hostel->hostel_rooms_count }}</td>
                                            <td>{{ $hostel->beds }}</td>
                                            <td>{{ $hostel->residents }}</td>
                                            <td class="{{ $hostel->getAvailableCapacity() > 0 ? 'text-success' : 'text-muted' }} font-weight-bold">
                                                {{ $hostel->getAvailableCapacity() }}
                                            </td>
                                            <td>
                                                <div class="progress progress-xs" style="width: 100px;">
                                                    <div class="progress-bar bg-{{ $perc >= 90 ? 'danger' : ($perc >= 50 ? 'warning' : 'success') }}"
                                                         style="width: {{ min($perc, 100) }}%"></div>
                                                </div>
                                                <small>{{ $perc }}%</small>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">No hostels have been created yet.</td>
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
@endsection
