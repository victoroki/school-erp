@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Student Discounts Summary</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-body">
                {!! Form::open(['route' => 'fees.reports.discount-summary', 'method' => 'get', 'class' => 'row']) !!}
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

        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-hand-holding-usd"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Revenue Forgone (Discounts)</span>
                <span class="info-box-number">KSh {{ number_format($totalDiscounts, 2) }}</span>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Scheme Name</th>
                                <th>Reason / Note</th>
                                <th class="text-right">Discount Amount</th>
                                <th>Date Granted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($discounts as $discount)
                                <tr>
                                    <td>{{ optional($discount->student)->full_name }}</td>
                                    <td>{{ optional($discount->discount)->name ?? 'Manual Adjustment' }}</td>
                                    <td>{{ $discount->notes }}</td>
                                    <td class="text-right font-weight-bold text-danger">- {{ number_format($discount->discount_amount, 2) }}</td>
                                    <td>{{ $discount->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No discounts found for the selected criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $discounts->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
