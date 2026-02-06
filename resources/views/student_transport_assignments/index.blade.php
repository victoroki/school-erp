@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Student Transport Assignments</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-danger float-right" href="{{ route('student-transport-assignments.create') }}">
                        <i class="fas fa-plus mr-1"></i> New Assignment
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card card-outline card-danger mb-3">
            <div class="card-body">
                <form action="{{ route('student-transport-assignments.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Route:</label>
                                {!! Form::select('route_id', ['' => 'All Routes'] + $routes->toArray(), request('route_id'), ['class' => 'form-control select2']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status:</label>
                                {!! Form::select('status', ['' => 'All Status', 'active' => 'Active', 'inactive' => 'Inactive'], request('status'), ['class' => 'form-control']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-danger">Filter</button>
                                <a href="{{ route('student-transport-assignments.index') }}" class="btn btn-default">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Route</th>
                                <th>Pickup Stop</th>
                                <th>Drop Stop</th>
                                <th>Date Assigned</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                                <tr>
                                    <td>
                                        <strong>{{ $assignment->student->first_name }} {{ $assignment->student->last_name }}</strong><br>
                                        <small class="text-muted">{{ $assignment->student->student_id }}</small>
                                    </td>
                                    <td>{{ $assignment->route->name }}</td>
                                    <td>{{ $assignment->pickupStop->stop_name ?? 'N/A' }}</td>
                                    <td>{{ $assignment->dropStop->stop_name ?? 'N/A' }}</td>
                                    <td>{{ $assignment->assigned_date ? $assignment->assigned_date->format('d M, Y') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $assignment->status == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($assignment->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('student-transport-assignments.edit', $assignment->assignment_id) }}" class="btn btn-default btn-xs">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            {!! Form::open(['route' => ['student-transport-assignments.destroy', $assignment->assignment_id], 'method' => 'delete', 'style' => 'display:inline']) !!}
                                            {!! Form::button('<i class="fas fa-trash"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                                            {!! Form::close() !!}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">No assignments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="float-right">
                    {{ $assignments->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
