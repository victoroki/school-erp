<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Methods Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #1e293b; font-size: 8.5pt; line-height: 1.4; padding: 10mm; }

        .report-header { text-align: center; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 2px solid #4f46e5; }
        .school-name { font-size: 16pt; font-weight: 900; color: #4f46e5; }
        .report-title { font-size: 10pt; font-weight: 700; color: #475569; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.06em; }
        .report-date { font-size: 7pt; color: #94a3b8; margin-top: 4px; }
        .filter-label { font-size: 7.5pt; color: #475569; margin-top: 4px; font-weight: 600; }

        .totals-strip { display: table; width: 100%; margin-bottom: 12px; border-collapse: separate; border-spacing: 6px 0; }
        .totals-cell { display: table-cell; width: 50%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 8px; text-align: center; }
        .totals-label { font-size: 7pt; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
        .totals-value { font-size: 12pt; font-weight: 800; color: #1e293b; margin-top: 2px; }
        .totals-value.green { color: #10b981; }

        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8fafc; }
        th { padding: 6px 8px; font-size: 7pt; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; text-align: left; border-bottom: 1px solid #e2e8f0; }
        td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; }
        tfoot td { font-weight: 800; border-top: 2px solid #e2e8f0; background: #f8fafc; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .mono { font-family: 'Consolas', monospace; }
        .fw-700 { font-weight: 700; }
        .text-green { color: #10b981; }
        .text-muted { color: #94a3b8; }
        .text-sm { font-size: 8pt; }

        .report-footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 7pt; color: #94a3b8; font-style: italic; }

        @page { margin: 10mm; size: A4 portrait; }
    </style>
</head>
<body>
    <div class="report-header">
        <div class="school-name">{{ config('app.name') }}</div>
        <div class="report-title">{{ $methodFilter ? 'Daily Collections — ' . $methodLabel : 'Collections by Payment Method' }}</div>
        <div class="report-date">Generated on {{ date('d F Y, h:i A') }} &middot; {{ number_format($paymentsCount) }} payment{{ $paymentsCount === 1 ? '' : 's' }}</div>
        @if($filterLabel)
            <div class="filter-label">{{ $filterLabel }}</div>
        @endif
    </div>

    <div class="totals-strip">
        <div class="totals-cell">
            <div class="totals-label">Total Collected</div>
            <div class="totals-value green">KES {{ number_format($grandTotal, 2) }}</div>
        </div>
        <div class="totals-cell">
            <div class="totals-label">Payments</div>
            <div class="totals-value">{{ number_format($paymentsCount) }}</div>
        </div>
    </div>

    @if($methodFilter)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="text-center">Payments</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td class="fw-700">{{ $row->day }}</td>
                        <td class="text-center">{{ number_format($row->count) }}</td>
                        <td class="text-right mono fw-700 text-green">KES {{ number_format($row->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted">No payments found.</td></tr>
                @endforelse
            </tbody>
            @if($rows->count())
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="text-center">{{ number_format($paymentsCount) }}</td>
                        <td class="text-right mono">KES {{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th class="text-center">Payments</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Share</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php
                        $label = ($row->payment_method === '' || $row->payment_method === null)
                            ? 'Unspecified'
                            : ucwords(str_replace('_', ' ', $row->payment_method));
                        $share = $grandTotal > 0 ? round(((float) $row->total / $grandTotal) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td class="fw-700">{{ $label }}</td>
                        <td class="text-center">{{ number_format($row->count) }}</td>
                        <td class="text-right mono fw-700 text-green">KES {{ number_format($row->total, 2) }}</td>
                        <td class="text-right">{{ $share }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">No payments found.</td></tr>
                @endforelse
            </tbody>
            @if($rows->count())
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="text-center">{{ number_format($paymentsCount) }}</td>
                        <td class="text-right mono">KES {{ number_format($grandTotal, 2) }}</td>
                        <td class="text-right">100%</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif

    <div class="report-footer">
        System-generated report from {{ config('app.name') }} School ERP. All figures exclude reversed (voided) payments.
    </div>
</body>
</html>
