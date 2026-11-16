<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Receipt Register</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #1e293b; font-size: 8pt; line-height: 1.4; padding: 10mm; }

        .report-header { text-align: center; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 2px solid #4f46e5; }
        .school-name { font-size: 16pt; font-weight: 900; color: #4f46e5; }
        .report-title { font-size: 10pt; font-weight: 700; color: #475569; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.06em; }
        .report-date { font-size: 7pt; color: #94a3b8; margin-top: 4px; }
        .filter-label { font-size: 7.5pt; color: #475569; margin-top: 4px; font-weight: 600; }

        .totals-strip { display: table; width: 100%; margin-bottom: 12px; border-collapse: separate; border-spacing: 6px 0; }
        .totals-cell { display: table-cell; width: 25%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 8px; text-align: center; }
        .totals-label { font-size: 6.5pt; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
        .totals-value { font-size: 10pt; font-weight: 800; color: #1e293b; margin-top: 2px; }
        .totals-value.green { color: #10b981; }
        .totals-value.red { color: #f43f5e; }

        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8fafc; }
        th { padding: 5px 6px; font-size: 6.5pt; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; text-align: left; border-bottom: 1px solid #e2e8f0; }
        td { padding: 4px 6px; border-bottom: 1px solid #f1f5f9; }
        .text-right { text-align: right; } .text-center { text-align: center; }
        .mono { font-family: 'Consolas', monospace; font-size: 7.5pt; }
        .fw-700 { font-weight: 700; }
        .text-green { color: #10b981; } .text-red { color: #f43f5e; } .text-muted { color: #94a3b8; }
        .text-sm { font-size: 7.5pt; }
        .strike { text-decoration: line-through; }

        .day-head td {
            background: #eef2ff; color: #4f46e5; font-weight: 800; font-size: 7.5pt;
            padding: 4px 6px; border-bottom: 1px solid #e0e7ff; text-transform: uppercase; letter-spacing: 0.04em;
        }

        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 6.5pt; font-weight: 700; text-transform: uppercase; }
        .badge-ok { background: #ecfdf5; color: #10b981; }
        .badge-void { background: #fff1f2; color: #f43f5e; }

        .report-footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 7pt; color: #94a3b8; font-style: italic; }

        @page { margin: 8mm; size: A4 portrait; }
    </style>
</head>
<body>
    <div class="report-header">
        <div class="school-name">{{ config('app.name') }}</div>
        <div class="report-title">Receipt Register</div>
        <div class="report-date">Generated on {{ date('d F Y, h:i A') }} &middot; {{ number_format($count) }} receipt{{ $count === 1 ? '' : 's' }}</div>
        @if($filterLabel)
            <div class="filter-label">{{ $filterLabel }}</div>
        @endif
    </div>

    <div class="totals-strip">
        <div class="totals-cell">
            <div class="totals-label">Collected</div>
            <div class="totals-value green">KES {{ number_format($validTotal, 2) }}</div>
        </div>
        <div class="totals-cell">
            <div class="totals-label">Valid Receipts</div>
            <div class="totals-value">{{ number_format($validCount) }}</div>
        </div>
        <div class="totals-cell">
            <div class="totals-label">Voided</div>
            <div class="totals-value red">KES {{ number_format($voidedTotal, 2) }}</div>
        </div>
        <div class="totals-cell">
            <div class="totals-label">Voided Receipts</div>
            <div class="totals-value">{{ number_format($voidedCount) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Receipt No</th>
                <th>Date</th>
                <th>Student</th>
                <th>Charge</th>
                <th>Method</th>
                <th>Collected By</th>
                <th class="text-right">Amount</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @if(!$chunked)
                {{-- Day-grouped print for regular-sized registers --}}
                @foreach($receipts as $day => $dayReceipts)
                    @php
                        $dayValid = $dayReceipts->whereNull('reversed_at')->sum('amount');
                    @endphp
                    @if($day !== 'undated')
                        <tr class="day-head">
                            <td colspan="8">{{ \Carbon\Carbon::parse($day)->format('l, d F Y') }} &mdash; KES {{ number_format($dayValid, 2) }}</td>
                        </tr>
                    @endif
                    @foreach($dayReceipts as $p)
                        @php $voided = $p->isReversed(); @endphp
                        <tr>
                            <td class="mono fw-700">{{ $p->receipt_number ?? 'RCP-'.$p->payment_id }}</td>
                            <td class="text-sm">{{ $p->payment_date ? $p->payment_date->format('d M Y') : '—' }}</td>
                            <td>{{ $p->studentFeeAssignment->student->full_name ?? 'N/A' }}
                                <span class="text-sm text-muted">{{ $p->studentFeeAssignment->student->admission_no ?? '' }}</span></td>
                            <td class="text-sm text-muted">{{ $p->studentFeeAssignment->feeStructure->category->name ?? '—' }}</td>
                            <td class="text-sm">{{ $p->payment_method ? ucwords(str_replace('_', ' ', $p->payment_method)) : 'Unspecified' }}</td>
                            <td class="text-sm">{{ $p->collectedBy->full_name ?? '—' }}</td>
                            <td class="text-right mono fw-700 {{ $voided ? 'text-muted strike' : 'text-green' }}">KES {{ number_format($p->amount, 2) }}</td>
                            <td class="text-center"><span class="badge {{ $voided ? 'badge-void' : 'badge-ok' }}">{{ $voided ? 'Void' : 'Valid' }}</span></td>
                        </tr>
                    @endforeach
                @endforeach
                @if($receipts->isEmpty())
                    <tr><td colspan="8" class="text-center text-muted">No receipts match the selected filters.</td></tr>
                @endif
            @else
                {{-- Flat chronological print for very large registers --}}
                @foreach($receipts as $p)
                    @php $voided = $p->isReversed(); @endphp
                    <tr>
                        <td class="mono fw-700">{{ $p->receipt_number ?? 'RCP-'.$p->payment_id }}</td>
                        <td class="text-sm">{{ $p->payment_date ? $p->payment_date->format('d M Y') : '—' }}</td>
                        <td>{{ $p->studentFeeAssignment->student->full_name ?? 'N/A' }}
                            <span class="text-sm text-muted">{{ $p->studentFeeAssignment->student->admission_no ?? '' }}</span></td>
                        <td class="text-sm text-muted">{{ $p->studentFeeAssignment->feeStructure->category->name ?? '—' }}</td>
                        <td class="text-sm">{{ $p->payment_method ? ucwords(str_replace('_', ' ', $p->payment_method)) : 'Unspecified' }}</td>
                        <td class="text-sm">{{ $p->collectedBy->full_name ?? '—' }}</td>
                        <td class="text-right mono fw-700 {{ $voided ? 'text-muted strike' : 'text-green' }}">KES {{ number_format($p->amount, 2) }}</td>
                        <td class="text-center"><span class="badge {{ $voided ? 'badge-void' : 'badge-ok' }}">{{ $voided ? 'Void' : 'Valid' }}</span></td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="report-footer">
        System-generated report from {{ config('app.name') }} School ERP. Voided receipts are listed for audit but excluded from collected totals.
    </div>
</body>
</html>
