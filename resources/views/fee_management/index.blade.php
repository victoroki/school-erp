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

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($metrics['total_receivable'], 0) }}</h3>
                        <p>Total Receivable</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($metrics['total_collected'], 0) }}</h3>
                        <p>Total Collected</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($metrics['total_pending'], 0) }}</h3>
                        <p>Total Pending</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $metrics['collection_rate'] }}<sup style="font-size: 20px">%</sup></h3>
                        <p>Collection Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title">Advanced Filtering</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('fee-management.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control select2">
                                    <option value="">All Status</option>
                                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Class</label>
                                <select name="class_id" class="form-control select2">
                                    <option value="">All Classes</option>
                                    @foreach($classes as $id => $name)
                                        <option value="{{ $id }}" {{ request('class_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Search Student</label>
                                <input type="text" name="search" class="form-control" placeholder="Name or Admission No" value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header border-0">
                <h3 class="card-title">Student Fee Records</h3>
                <div class="card-tools">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="#" class="dropdown-item">Excel</a>
                            <a href="#" class="dropdown-item">PDF</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-valign-middle">
                    <thead>
                        <tr>
                            <th>Admission No</th>
                            <th>Student Name</th>
                            <th>Class</th>
                            <th class="text-right">Total Fee</th>
                            <th class="text-right">Paid</th>
                            <th class="text-right">Balance</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td>{{ $student->admission_no }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($student->photo_url)
                                            <img src="{{ $student->photo_url }}" class="img-circle img-size-32 mr-2">
                                        @else
                                            <div class="img-circle bg-secondary d-flex justify-content-center align-items-center mr-2" style="width: 32px; height: 32px;">
                                                <i class="fas fa-user text-xs"></i>
                                            </div>
                                        @endif
                                        <span>{{ $student->full_name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @foreach($student->studentClassEnrollments as $enrollment)
                                        <span class="badge badge-info">{{ $enrollment->classSection->schoolClass->name ?? '' }} - {{ $enrollment->classSection->section->name ?? '' }}</span>
                                    @endforeach
                                </td>
                                <td class="text-right font-weight-bold text-dark">KSh {{ number_format($student->total_fee, 2) }}</td>
                                <td class="text-right text-success">KSh {{ number_format($student->paid_fee, 2) }}</td>
                                <td class="text-right text-danger">KSh {{ number_format($student->balance_fee, 2) }}</td>
                                <td class="text-center">
                                    @php
                                        $status = $student->payment_status;
                                        $badgeClass = match($status) {
                                            'Paid' => 'success',
                                            'Partial' => 'warning',
                                            'Unpaid' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }} px-2 py-1">{{ $status }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('fee-management.show', $student->student_id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('fee-management.collect-payment', $student->student_id) }}" class="btn btn-sm btn-outline-success" title="Collect Payment">
                                            <i class="fas fa-cash-register"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">No records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0">
                <div class="float-right">
                    {{ $students->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
