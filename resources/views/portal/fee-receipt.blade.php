<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Receipt — {{ $student->full_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 22px; color: #007bff; }
        .header p { margin: 2px 0; font-size: 11px; color: #666; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .info-label { font-weight: bold; width: 140px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; font-size: 11px; }
        th { background: #f4f4f4; }
        .totals { margin-top: 15px; text-align: right; }
        .totals .row { margin-bottom: 3px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name', 'School ERP') }}</h1>
        <p>Fee Payment Receipt</p>
    </div>

    <div class="info-row">
        <div><span class="info-label">Student:</span> {{ $student->full_name }}</div>
        <div><span class="info-label">Admission No:</span> {{ $student->admission_no }}</div>
    </div>
    <div class="info-row">
        <div><span class="info-label">Fee Category:</span> {{ $assignment->feeStructure->category->name ?? 'N/A' }}</div>
        <div><span class="info-label">Academic Year:</span> {{ $assignment->feeStructure->academicYear->name ?? 'N/A' }}</div>
    </div>
    <div class="info-row">
        <div><span class="info-label">Term:</span> {{ $assignment->term ?? $assignment->termModel?->name ?? 'N/A' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right;">Amount (KES)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Fee</td>
                <td style="text-align:right;">{{ number_format($assignment->final_amount, 2) }}</td>
            </tr>
            @if($assignment->discount_amount > 0)
            <tr>
                <td>Discount</td>
                <td style="text-align:right;">({{ number_format($assignment->discount_amount, 2) }})</td>
            </tr>
            @endif
            <tr>
                <td>Amount Paid</td>
                <td style="text-align:right;">{{ number_format($assignment->paid_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="totals">
        <div><strong>Balance: KES {{ number_format($assignment->balance, 2) }}</strong></div>
    </div>

    @if($assignment->payments->count())
    <h4 style="margin-top:20px;">Payment History</h4>
    <table>
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

    <div class="footer">
        <p>Generated on {{ now()->format('d/m/Y H:i') }}</p>
        <p>This is a computer-generated receipt.</p>
    </div>
</body>
</html>
