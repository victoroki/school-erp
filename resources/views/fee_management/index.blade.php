@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fee Management</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Student Fee List</h3>
                <div class="card-tools">
                    <form action="{{ route('fee-management.index') }}" method="GET" class="form-inline">
                        <div class="input-group input-group-sm" style="width: 150px;">
                            <select name="status" class="form-control float-right" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            </select>
                        </div>
                        <div class="input-group input-group-sm ml-2" style="width: 200px;">
                            <input type="text" name="search" class="form-control float-right" placeholder="Search Student" value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Admission No</th>
                                <th>Student Name</th>
                                <th>Total Fee</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                                <tr>
                                    <td>{{ $student->admission_no }}</td>
                                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                    <td>{{ number_format($student->total_fee, 2) }}</td>
                                    <td>{{ number_format($student->paid_fee, 2) }}</td>
                                    <td>{{ number_format($student->balance_fee, 2) }}</td>
                                    <td>
                                        @php
                                            $status = $student->payment_status;
                                            $badgeClass = match($status) {
                                                'Paid' => 'success',
                                                'Partial' => 'warning',
                                                'Unpaid' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }}">{{ $status }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('fee-management.show', $student->student_id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No students found with fee assignments.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                <div class="float-right">
                    {{ $students->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
