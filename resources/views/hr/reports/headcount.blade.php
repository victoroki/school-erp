@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-chart-bar text-secondary"></i> Headcount Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item active">Headcount Report</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- By Department -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h3 class="card-title">Staff by Department</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th class="text-right">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byDepartment as $dept)
                                        <tr>
                                            <td>{{ $dept->name }}</td>
                                            <td class="text-right"><span class="badge badge-secondary">{{ $dept->staff_count }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- By Employment Type -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h3 class="card-title">By Employment Type</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th class="text-right">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byEmploymentType as $type)
                                        <tr>
                                            <td>{{ ucfirst(str_replace('_', ' ', $type->employment_type)) }}</td>
                                            <td class="text-right"><span class="badge badge-info">{{ $type->count }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- By Gender -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-success">
                            <h3 class="card-title">Gender Distribution</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Gender</th>
                                        <th class="text-right">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byGender as $gender)
                                        <tr>
                                            <td>{{ ucfirst($gender->gender) }}</td>
                                            <td class="text-right"><span class="badge badge-success">{{ $gender->count }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- By Staff Type -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h3 class="card-title">Teaching vs Non-Teaching</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th class="text-right">Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byStaffType as $type)
                                        <tr>
                                            <td>{{ ucfirst(str_replace('_', ' ', $type->staff_type)) }}</td>
                                            <td class="text-right"><span class="badge badge-warning">{{ $type->count }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
