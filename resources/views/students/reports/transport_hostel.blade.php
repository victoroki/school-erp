@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-bus-alt text-secondary mr-2"></i>Transport &amp; Hostel Report
                </h1>
                <p class="text-muted small mb-0">Students using school transport and hostel facilities</p>
            </div>
            <div class="col-sm-6 text-right">
                <div class="btn-group mr-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fas fa-download mr-1"></i> Export</button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('student-reports.transport-hostel.csv') }}"><i class="fas fa-file-csv text-success mr-2"></i>Download CSV</a>
                        <a class="dropdown-item" href="{{ route('student-reports.transport-hostel.pdf') }}"><i class="fas fa-file-pdf text-danger mr-2"></i>Download PDF</a>
                    </div>
                </div>
                <button onclick="window.print()" class="btn btn-outline-info btn-sm shadow-sm">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
                <a href="{{ route('student-reports.index') }}" class="btn btn-default btn-sm shadow-sm border ml-2">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card border-left-danger">
                <div class="stat-icon"><i class="fas fa-bus text-danger"></i></div>
                <div>
                    <div class="stat-value text-danger">{{ $totalTransport }}</div>
                    <div class="stat-label">Transport Students</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card border-left-warning">
                <div class="stat-icon"><i class="fas fa-bed text-warning"></i></div>
                <div>
                    <div class="stat-value text-warning">{{ $totalHostel }}</div>
                    <div class="stat-label">Hostel Residents</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card border-left-primary">
                <div class="stat-icon"><i class="fas fa-users text-primary"></i></div>
                <div>
                    <div class="stat-value text-primary">{{ $totalTransport + $totalHostel }}</div>
                    <div class="stat-label">Total Service Users</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Transport --}}
        <div class="col-md-6">
            <div class="card card-outline card-danger elevation-2 border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-bus text-danger mr-2"></i>Transport Users</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="sticky-top bg-white">
                                <tr class="text-muted small text-uppercase border-bottom">
                                    <th class="pl-4">Student</th>
                                    <th>Class</th>
                                    <th>Route</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transportStudents as $s)
                                    <tr>
                                        <td class="pl-4">
                                            <span class="font-weight-bold">{{ $s->full_name }}</span>
                                            <br><small class="text-muted">{{ $s->admission_no }}</small>
                                        </td>
                                        <td><small>{{ $s->class_info }}</small></td>
                                        <td>
                                            @if($s->route_id)
                                                <span class="badge badge-danger">Route #{{ $s->route_id }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                            @if($s->pickup_point)
                                                <br><small class="text-muted">{{ $s->pickup_point }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            No students using school transport.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hostel --}}
        <div class="col-md-6">
            <div class="card card-outline card-warning elevation-2 border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-bed text-warning mr-2"></i>Hostel Residents</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="sticky-top bg-white">
                                <tr class="text-muted small text-uppercase border-bottom">
                                    <th class="pl-4">Student</th>
                                    <th>Class</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hostelStudents as $s)
                                    <tr>
                                        <td class="pl-4">
                                            <span class="font-weight-bold">{{ $s->full_name }}</span>
                                            <br><small class="text-muted">{{ $s->admission_no }}</small>
                                        </td>
                                        <td><small>{{ $s->class_info }}</small></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted">
                                            No hostel residents.
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
</div>

<style>
    .stat-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
        padding: 1rem 1.25rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid;
    }
    .stat-icon { font-size: 1.5rem; }
    .stat-value { font-size: 1.15rem; font-weight: 800; color: #1e293b; }
    .stat-label { font-size: 0.72rem; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; }
    @media print {
        .btn, .main-header, .main-sidebar { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
    }
</style>
@endsection
