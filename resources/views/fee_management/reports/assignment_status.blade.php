@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fee Assignment Status</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-body">
                {!! Form::open(['route' => 'fees.reports.assignment-status', 'method' => 'get', 'class' => 'row']) !!}
                    <div class="form-group col-sm-4">
                        {!! Form::label('academic_year_id', 'Academic Year:') !!}
                        {!! Form::select('academic_year_id', $academicYears, $yearId, ['class' => 'form-control', 'placeholder' => 'Select Year']) !!}
                    </div>
                    <div class="form-group col-sm-4">
                        {!! Form::label('class_id', 'Class:') !!}
                        {!! Form::select('class_id', $classes, $classId, ['class' => 'form-control', 'placeholder' => 'All Classes']) !!}
                    </div>
                    <div class="form-group col-sm-2" style="margin-top: 32px;">
                        {!! Form::submit('Filter', ['class' => 'btn btn-primary']) !!}
                    </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Admission No</th>
                                <th>Fee Type</th>
                                <th>Term</th>
                                <th class="text-right">Amount</th>
                                <th class="text-right">Discount</th>
                                <th class="text-right">Payable</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $assignment)
                                <tr>
                                    <td>{{ optional($assignment->student)->full_name }}</td>
                                    <td>{{ optional($assignment->student)->admission_no }}</td>
                                    <td>{{ optional($assignment->feeStructure->category)->name }}</td>
                                    <td>{{ $assignment->term }}</td>
                                    <td class="text-right">{{ number_format($assignment->amount, 2) }}</td>
                                    <td class="text-right">{{ number_format($assignment->discount_amount, 2) }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($assignment->final_amount, 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $assignment->status == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($assignment->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $assignments->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
