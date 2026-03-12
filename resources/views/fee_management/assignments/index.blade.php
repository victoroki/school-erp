@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Student Fee Assignments</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right" href="{{ route('fees.assignments.create') }}">
                        <i class="fas fa-plus mr-1"></i> Assign New Fees
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">All Assignments</h3>
                <div class="card-tools">
                    <form action="{{ route('fees.assignments.index') }}" method="GET" class="form-inline">
                        <div class="input-group input-group-sm mr-2">
                            {!! Form::select('class_id', $classes ?? [], request('class_id'), ['class' => 'form-control', 'placeholder' => 'All Classes']) !!}
                        </div>
                        <div class="input-group input-group-sm" style="width: 150px;">
                            <input type="text" name="student_name" class="form-control float-right" placeholder="Search Student" value="{{ request('student_name') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Fee Category</th>
                            <th>Term / Year</th>
                            <th>Amount</th>
                            <th>Discount</th>
                            <th>Net Payable</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->student->first_name }} {{ $assignment->student->last_name }}</td>
                                <td>{{ $assignment->student->current_enrollment->classSection->schoolClass->name ?? '-' }}</td>
                                <td>{{ $assignment->feeStructure->category->name ?? '-' }}</td>
                                <td>{{ $assignment->term }} ({{ $assignment->academicYear->name ?? '-' }})</td>
                                <td>{{ number_format($assignment->amount, 2) }}</td>
                                <td>
                                    @if($assignment->discount_amount > 0)
                                        <span class="text-danger">- {{ number_format($assignment->discount_amount, 2) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td><strong>{{ number_format($assignment->final_amount, 2) }}</strong></td>
                                <td>
                                    <span class="badge badge-{{ $assignment->status == 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($assignment->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class='btn-group'>
                                        <a href="{{ route('fees.assignments.student-summary', $assignment->student_id) }}" class='btn btn-default btn-xs' title="View Student Summary">
                                            <i class="far fa-eye"></i>
                                        </a>
                                        {!! Form::open(['route' => ['fees.assignments.destroy', $assignment->id], 'method' => 'delete']) !!}
                                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure you want to remove this fee assignment?')"]) !!}
                                        {!! Form::close() !!}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No assignments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer clearfix">
                <div class="float-right">
                    {{ $assignments->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
