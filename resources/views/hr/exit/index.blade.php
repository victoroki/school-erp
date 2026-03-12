@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-user-slash text-danger"></i> Exit Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item active">Exit Management</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-danger">
                    <h3 class="card-title">Exiting Staff</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Staff</th>
                                    <th>Department</th>
                                    <th>Exit Type</th>
                                    <th>Exit Date</th>
                                    <th>Status</th>
                                    <th>Clearance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exitingStaff as $staff)
                                    <tr>
                                        <td>{{ $staff->full_name }}</td>
                                        <td>{{ $staff->department->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($staff->employment_status == 'resigned')
                                                <span class="badge badge-warning">Resignation</span>
                                            @elseif($staff->employment_status == 'terminated')
                                                <span class="badge badge-danger">Termination</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($staff->employment_status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $staff->exit_date ? \Carbon\Carbon::parse($staff->exit_date)->format('M d, Y') : 'N/A' }}</td>
                                        <td>{{ ucfirst($staff->employment_status) }}</td>
                                        <td>
                                            @if($staff->exitClearance)
                                                <span class="badge badge-{{ $staff->exitClearance->clearance_status == 'completed' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($staff->exitClearance->clearance_status) }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">Not Started</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No exiting staff found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
