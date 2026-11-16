<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Fee Assignment Status Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #1e293b; font-size: 8pt; line-height: 1.4; padding: 10mm; }

        .report-header { text-align: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid #4f46e5; }
        .school-name { font-size: 16pt; font-weight: 900; color: #4f46e5; }
        .report-title { font-size: 10pt; font-weight: 700; color: #475569; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.06em; }
        .report-date { font-size: 7pt; color: #94a3b8; margin-top: 4px; }

        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8fafc; }
        th { padding: 5px 6px; font-size: 6.5pt; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; text-align: left; border-bottom: 1px solid #e2e8f0; }
        td { padding: 5px 6px; border-bottom: 1px solid #f1f5f9; }
        .text-right { text-align: right; } .text-center { text-align: center; }
        .mono { font-family: 'Consolas', monospace; font-size: 7.5pt; }
        .fw-700 { font-weight: 700; }
        .text-green { color: #10b981; } .text-red { color: #f43f5e; } .text-muted { color: #94a3b8; }
        .text-sm { font-size: 7.5pt; }

        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 6.5pt; font-weight: 700; text-transform: capitalize; }
        .badge-paid { background: #ecfdf5; color: #10b981; }
        .badge-partial { background: #fffbeb; color: #d97706; }
        .badge-unpaid { background: #fff1f2; color: #f43f5e; }

        .report-footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 7pt; color: #94a3b8; font-style: italic; }

        @page { margin: 8mm; size: A4 landscape; }
    </style>
</head>
<body>
    <div class="report-header">
        <div class="school-name">{{ config('app.name') }}</div>
        <div class="report-title">Fee Assignment Status Report</div>
        <div class="report-date">Generated on {{ date('d F Y, h:i A') }} &middot; {{ $assignments->count() }} assignments</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Admission No</th>
                <th>Fee Type</th>
                <th>Term</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Payable</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Balance</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assignments as $a)
                @php
                    $pStatus = $a->payment_status;
                    $badgeClass = $pStatus === 'paid' ? 'badge-paid' : ($pStatus === 'partial' ? 'badge-partial' : 'badge-unpaid');
                @endphp
                <tr>
                    <td class="fw-700">{{ $a->student->full_name ?? 'N/A' }}</td>
                    <td class="text-sm text-muted mono">{{ $a->student->admission_no ?? 'N/A' }}</td>
                    <td>{{ $a->feeStructure->category->name ?? 'N/A' }}</td>
                    <td class="text-sm">{{ ucfirst($a->term ?? 'N/A') }}</td>
                    <td class="text-right mono">{{ number_format($a->amount, 2) }}</td>
                    <td class="text-right mono text-green">-{{ number_format($a->discount_amount, 2) }}</td>
                    <td class="text-right mono fw-700">{{ number_format($a->final_amount, 2) }}</td>
                    <td class="text-right mono text-green">{{ number_format($a->paid_amount, 2) }}</td>
                    <td class="text-right mono {{ $a->balance > 0 ? 'text-red fw-700' : 'text-muted' }}">{{ number_format($a->balance, 2) }}</td>
                    <td class="text-center"><span class="badge {{ $badgeClass }}">{{ ucfirst($pStatus) }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="report-footer">
        System-generated report from {{ config('app.name') }} School ERP.
    </div>
</body>
</html>
