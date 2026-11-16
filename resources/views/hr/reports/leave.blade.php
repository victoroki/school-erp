@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-chart-pie text-info"></i> Leave Analytics</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item active">Leave Analytics</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Filters -->
            <div class="card">
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="year" class="form-select">
                                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Leave by Type -->
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">Leave Applications by Type ({{ $year }})</h3>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Leave Type</th>
                                <th class="text-right">Total Applications</th>
                                <th class="text-right">Approved</th>
                                <th class="text-right">Rejected</th>
                                <th class="text-right">Pending</th>
                                <th class="text-right">Total Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byLeaveType as $type)
                                <tr>
                                    <td>{{ $type['leave_type'] }}</td>
                                    <td class="text-right">{{ $type['total_applications'] }}</td>
                                    <td class="text-right"><span class="badge badge-success">{{ $type['approved'] }}</span></td>
                                    <td class="text-right"><span class="badge badge-danger">{{ $type['rejected'] }}</span></td>
                                    <td class="text-right"><span class="badge badge-warning">{{ $type['pending'] }}</span></td>
                                    <td class="text-right"><strong>{{ $type['total_days'] }} days</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
