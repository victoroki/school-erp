@extends('layouts.portal')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Fee Detail</h3>
                    <div class="card-tools">
                        <a href="{{ route('portal.fees') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Fees
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Fee Category:</strong> {{ $assignment->feeStructure->category->name ?? 'N/A' }}<br>
                            <strong>Academic Year:</strong> {{ $assignment->feeStructure->academicYear->name ?? 'N/A' }}<br>
                            <strong>Term:</strong> {{ $assignment->term ?? $assignment->termModel?->name ?? 'N/A' }}<br>
                        </div>
                        <div class="col-md-6">
                            <strong>Total Amount:</strong> KES {{ number_format($assignment->final_amount, 2) }}<br>
                            <strong>Paid:</strong> KES {{ number_format($assignment->paid_amount, 2) }}<br>
                            <strong>Balance:</strong> KES {{ number_format($assignment->balance, 2) }}<br>
                        </div>
                    </div>

                    @if($assignment->discount)
                    <div class="alert alert-info mt-3">
                        <strong>Discount Applied:</strong> {{ $assignment->discount->name ?? 'N/A' }} — KES {{ number_format($assignment->discount_amount, 2) }}
                    </div>
                    @endif

                    @if($assignment->feeAdjustments->count())
                    <h5 class="mt-4">Adjustments</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignment->feeAdjustments as $adj)
                            <tr>
                                <td>{{ $adj->created_at->format('d/m/Y') }}</td>
                                <td>{{ ucfirst($adj->type ?? 'adjustment') }}</td>
                                <td>{{ number_format($adj->amount, 2) }}</td>
                                <td>{{ $adj->reason ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif

                    @if($assignment->payments->count())
                    <h5 class="mt-4">Payment History</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignment->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date?->format('d/m/Y') ?? 'N/A' }}</td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ ucfirst($payment->payment_method ?? 'N/A') }}</td>
                                <td>{{ $payment->reference_number ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
