@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Unassigned Students ({{ $currentYear->name ?? 'N/A' }})</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title">Students without Fee Assignments</h3>
            </div>
            <div class="card-body">
                <p>The following students have <strong>0 active fee assignments</strong> for the current academic year.</p>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Parent</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            <td>{{ $student->admission_no }}</td>
                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td>{{ $student->current_enrollment->classSection->schoolClass->name ?? '-' }}</td>
                            <td>{{ $student->parents->first()->first_name ?? '-' }} {{ $student->parents->first()->last_name ?? '' }}</td>
                            <td>
                                <a href="{{ route('fees.assignments.create', ['student_id' => $student->student_id, 'assignment_type' => 'individual']) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Assign Fee
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-success">Great! All active students have fees assigned.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $students->links() }}
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('fees.assignments.create') }}" class="btn btn-warning">
                    <i class="fas fa-users-cog"></i> Bulk Assign to Class
                </a>
            </div>
        </div>
    </div>
@endsection
