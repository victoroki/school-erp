<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Statement - {{ $student->first_name }} {{ $student->last_name }}</title>
    <style>
        body { font-family: sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .student-info { margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .badge { padding: 3px 7px; border-radius: 3px; color: white; font-size: 0.8em; }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: black; }
        .badge-danger { background-color: #dc3545; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2>School Name</h2>
        <h3>Fee Balance Statement</h3>
        <p>Date: {{ date('d M Y') }}</p>
    </div>

    <div class="student-info">
        <strong>Student Name:</strong> {{ $student->first_name }} {{ $student->last_name }}<br>
        <strong>Admission No:</strong> {{ $student->admission_no }}<br>
        <strong>Class:</strong> {{ $student->studentClassEnrollments->first()->classSection->schoolClass->name ?? 'N/A' }}
    </div>

    <h3>Fee Summary</h3>
    <table class="table">
        <tr>
            <th>Total Fee Assigned</th>
            <td class="text-right">{{ number_format($student->total_fee, 2) }}</td>
        </tr>
        <tr>
            <th>Total Paid</th>
            <td class="text-right">{{ number_format($student->paid_fee, 2) }}</td>
        </tr>
        <tr>
            <th>Balance Remaining</th>
            <td class="text-right" style="color: red; font-weight: bold;">{{ number_format($student->balance_fee, 2) }}</td>
        </tr>
        <tr>
            <th>Payment Status</th>
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
        </tr>
    </table>

    <h3>Fee Breakdown</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Fee Type</th>
                <th>Due Date</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($student->studentFees as $fee)
                <tr>
                    <td>{{ $fee->feeStructure->category->name ?? 'N/A' }}</td>
                    <td>{{ $fee->due_date->format('d M Y') }}</td>
                    <td class="text-right">{{ number_format($fee->final_amount, 2) }}</td>
                    <td class="text-right">{{ number_format($fee->paid_amount, 2) }}</td>
                    <td class="text-right">{{ number_format($fee->balance, 2) }}</td>
                    <td>
                        <span class="badge badge-{{ $fee->status == 'paid' ? 'success' : ($fee->status == 'partially_paid' ? 'warning' : 'danger') }}">
                            {{ ucfirst(str_replace('_', ' ', $fee->status)) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Payment History</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Receipt No</th>
                <th>Method</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($student->payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                    <td>{{ $payment->receipt_number }}</td>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                    <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()">Print Statement</button>
    </div>
</body>
</html>
