<div class="row">
    <!-- Fee Summary -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-money-bill-wave mr-2"></i> Fee Summary</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="border-right">
                            <h3 class="font-weight-bold text-primary">KES {{ number_format($student->total_fee, 2) }}</h3>
                            <p class="text-muted mb-0">Total Fee</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border-right">
                            <h3 class="font-weight-bold text-success">KES {{ number_format($student->paid_fee, 2) }}</h3>
                            <p class="text-muted mb-0">Paid Amount</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h3 class="font-weight-bold text-danger">KES {{ number_format($student->balance_fee, 2) }}</h3>
                        <p class="text-muted mb-0">Balance Due</p>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <p class="mb-2">Payment Status: {!! $student->payment_status_badge !!}</p>
                    <a href="{{ route('fee-management.show', $student->student_id) }}" class="btn btn-primary">
                        <i class="fas fa-eye mr-1"></i> View Detailed Fee Statement
                    </a>
                    <a href="{{ route('fee-management.collect-payment', $student->student_id) }}" class="btn btn-success">
                        <i class="fas fa-money-check-alt mr-1"></i> Collect Payment
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-history mr-2 text-info"></i> Recent Payments</h6>
            </div>
            <div class="card-body">
                @if($student->payments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Receipt No</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Collected By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($student->payments->take(10) as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date->format('d M, Y') }}</td>
                                        <td class="font-weight-bold">{{ $payment->receipt_number }}</td>
                                        <td class="font-weight-bold text-success">KES {{ number_format($payment->amount, 2) }}</td>
                                        <td><span class="badge badge-info">{{ $payment->payment_method }}</span></td>
                                        <td>{{ $payment->collectedBy->name ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No payment history available.</p>
                @endif
            </div>
        </div>
    </div>
</div>
