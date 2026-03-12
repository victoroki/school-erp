@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Revenue Forecast Report</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-body">
                {!! Form::open(['route' => 'fees.reports.expected-revenue', 'method' => 'get', 'class' => 'row']) !!}
                    <div class="form-group col-sm-4">
                        {!! Form::label('academic_year_id', 'Academic Year:') !!}
                        {!! Form::select('academic_year_id', $academicYears, $yearId, ['class' => 'form-control', 'placeholder' => 'Select Year']) !!}
                    </div>
                    <div class="form-group col-sm-2" style="margin-top: 32px;">
                        {!! Form::submit('Filter', ['class' => 'btn btn-primary']) !!}
                    </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Gross Revenue (Billable)</span>
                        <span class="info-box-number">KSh {{ number_format($totalOriginal, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-percent"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Discounts</span>
                        <span class="info-box-number">KSh {{ number_format($totalDiscounts, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-check-double"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Net Expected Revenue</span>
                        <span class="info-box-number">KSh {{ number_format($totalExpected, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header border-transparent">
                        <h3 class="card-title">Revenue by Class</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table m-0">
                                <thead>
                                <tr>
                                    <th>Class Name</th>
                                    <th class="text-right">Expected Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($revenueByClass as $row)
                                    <tr>
                                        <td>{{ $row->class_name }}</td>
                                        <td class="text-right font-weight-bold">{{ number_format($row->total, 2) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                 <div class="card">
                    <div class="card-header border-transparent">
                        <h3 class="card-title">Revenue by Fee Category</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table m-0">
                                <thead>
                                <tr>
                                    <th>Category</th>
                                    <th class="text-right">Expected Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($revenueByCategory as $row)
                                    <tr>
                                        <td>{{ $row->category_name }}</td>
                                        <td class="text-right font-weight-bold">{{ number_format($row->total, 2) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
