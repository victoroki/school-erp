@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fee Management Dashboard</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @if(!$currentYear)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> No Active Academic Year Found. Please configure one.
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-calendar-check"></i> Current Academic Year: <strong>{{ $currentYear->name }}</strong>
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($expectedRevenue) }}</h3>
                        <p>Expected Revenue (Assigned)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <a href="{{ route('fees.reports.expected-revenue') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $totalFeeStructures }}</h3>
                        <p>Active Fee Structures</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <a href="{{ route('fee-structures.index') }}" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <!-- Data for collection progress could come from Finance module later -->
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($totalDiscounts) }}</h3>
                        <p>Discounts Given</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-percent"></i>
                    </div>
                    <a href="{{ route('fees.reports.discount-summary') }}" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $notAssignedCount }}</h3>
                        <p>Students w/o Fees</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <a href="{{ route('fees.assignments.unassigned') }}" class="small-box-footer">Fix Now <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Fee Assignment Progress -->
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Fee Assignment Status</h3>
                    </div>
                    <div class="card-body">
                         <div class="d-flex justify-content-between mb-1">
                            <span>Assigned Students</span>
                            <span>{{ $studentsWithFees }} / {{ $studentsWithFees + $notAssignedCount }}</span>
                        </div>
                        <div class="progress mb-3">
                            @php
                                $totalStd = $studentsWithFees + $notAssignedCount;
                                $percent = $totalStd > 0 ? ($studentsWithFees / $totalStd) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%">
                                {{ round($percent) }}%
                            </div>
                        </div>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Fully Assigned</b> <a class="float-right text-success">-</a>
                            </li>
                            <li class="list-group-item">
                                <b>Partially Assigned</b> <a class="float-right text-warning">-</a>
                            </li>
                            <li class="list-group-item">
                                <b>No Assignments</b> <a class="float-right text-danger">{{ $notAssignedCount }}</a>
                            </li>
                        </ul>
                        <a href="{{ route('fees.assignments.create') }}" class="btn btn-primary btn-block"><b>Assign Fees to Students</b></a>
                    </div>
                </div>
            </div>

            <!-- Revenue by Class -->
            <div class="col-md-6">
                 <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">Expected Revenue by Class (Top 5)</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Expected Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($revenueByClass as $row)
                                    <tr>
                                        <td>{{ $row->class_name }}</td>
                                        <td>{{ number_format($row->total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <h5 class="mb-2">Quick Actions</h5>
        <div class="row">
             <div class="col-md-3 col-sm-6 col-12">
                <a href="{{ route('fees.assignments.create') }}" class="info-box shadow-sm">
                    <span class="info-box-icon bg-info"><i class="fas fa-plus-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Bulk Assign Fees</span>
                        <span class="info-box-number">Assign by Class</span>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <a href="{{ route('fee-structures.create') }}" class="info-box shadow-sm">
                    <span class="info-box-icon bg-success"><i class="fas fa-file-invoice-dollar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Create Fee Structure</span>
                        <span class="info-box-number">Setup Fees</span>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <a href="{{ route('fees.discounts.create') }}" class="info-box shadow-sm">
                    <span class="info-box-icon bg-warning"><i class="fas fa-percentage"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Add Discount</span>
                        <span class="info-box-number">Create Scheme</span>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <a href="{{ route('fee-management.index') }}" class="info-box shadow-sm">
                    <span class="info-box-icon bg-danger"><i class="fas fa-cash-register"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Collect Fees</span>
                        <span class="info-box-number">Record Payment</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection
