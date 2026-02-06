@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-chart-pie text-info mr-2"></i>Teacher Workload Analytics
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="fas fa-print mr-1"></i> Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="content px-3">
        @include('flash::message')

        <!-- Summary Widgets -->
        <div class="row">
            <div class="col-md-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-uppercase small font-weight-bold">Total Evaluated</span>
                        <span class="info-box-number h4">{{ count($workloadData) }} Teachers</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-uppercase small font-weight-bold">Overloaded (>30 p/w)</span>
                        @php $overloaded = collect($workloadData)->where('total_periods', '>', 30)->count(); @endphp
                        <span class="info-box-number h4">{{ $overloaded }} Staff</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-uppercase small font-weight-bold">Optimum Load</span>
                        @php $optimum = collect($workloadData)->whereBetween('total_periods', [15, 30])->count(); @endphp
                        <span class="info-box-number h4">{{ $optimum }} Staff</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-primary"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-uppercase small font-weight-bold">Avg Weekly Load</span>
                        @php $avg = collect($workloadData)->avg('total_periods'); @endphp
                        <span class="info-box-number h4">{{ round($avg, 1) }} periods</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-info elevation-2 mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Workload Distribution Details</h3>
                <div class="card-tools">
                    {!! Form::open(['route' => 'teacher-workload.index', 'method' => 'GET', 'class' => 'form-inline']) !!}
                        {!! Form::select('academic_year_id', $academicYears->pluck('name', 'academic_year_id'), $selectedAcademicYearId, ['class' => 'form-control form-control-sm mr-2', 'onchange' => 'this.form.submit()']) !!}
                    {!! Form::close() !!}
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-valign-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4">Teacher Name</th>
                            <th>Department</th>
                            <th class="text-center">Total Periods</th>
                            <th>Load Capacity</th>
                            <th class="text-center">Hours/Week</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workloadData as $data)
                            <tr>
                                <td class="px-4">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('garikon-black.png') }}" class="img-circle img-sm mr-3 border shadow-sm" style="width: 32px; height: 32px;">
                                        <div>
                                            <div class="font-weight-bold text-dark">{{ $data['teacher']->full_name }}</div>
                                            <div class="x-small text-muted">{{ $data['teacher']->employee_id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light border">{{ $data['teacher']->department->name ?? 'N/A' }}</span>
                                </td>
                                <td class="text-center align-middle font-weight-bold">
                                    {{ $data['total_periods'] }}
                                </td>
                                <td class="align-middle" style="min-width: 150px;">
                                    @php
                                        $percent = min(($data['total_periods'] / 40) * 100, 100);
                                        $barColor = $data['status']['class'] == 'danger' ? 'bg-danger' : ($data['status']['class'] == 'success' ? 'bg-success' : 'bg-warning');
                                    @endphp
                                    <div class="progress progress-xs" style="height: 6px;">
                                        <div class="progress-bar {{ $barColor }}" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <small class="text-muted x-small text-uppercase font-weight-bold">{{ round($percent) }}% capacity</small>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="text-info font-weight-bold">{{ $data['est_hours'] }} hrs</span>
                                </td>
                                <td class="text-center align-middle">
                                    <span class="badge badge-{{ $data['status']['class'] }} px-3">
                                        {{ $data['status']['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No teaching workload data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .table-valign-middle td { vertical-align: middle !important; }
        .x-small { font-size: 0.7rem; }
        .elevation-2 { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important; }
        @media print {
            .main-sidebar, .main-header, .card-tools, .btn { display: none !important; }
            .content-wrapper { margin-left: 0 !important; }
            .card { border: 1px solid #eee !important; box-shadow: none !important; }
        }
    </style>
@endsection
