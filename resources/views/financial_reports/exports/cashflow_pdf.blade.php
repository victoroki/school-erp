<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cashflow Statement</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; margin: 20px; }
        h1 { font-size: 16px; font-weight: 800; margin: 0 0 4px; }
        .subtitle { font-size: 10px; color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #f3f4f6; font-size: 9px; text-transform: uppercase; letter-spacing: .05em; padding: 6px 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        td { padding: 6px 10px; border-bottom: 1px solid #f3f4f6; }
        .section-header td { background: #f9fafb; font-weight: 700; font-size: 10px; text-transform: uppercase; color: #374151; padding: 8px 10px; }
        .total-row td { font-weight: 800; border-top: 2px solid #e5e7eb; font-size: 12px; padding: 10px; }
        .net-row td { font-weight: 800; font-size: 13px; padding: 12px 10px; }
        .net-positive td { background: #eef2ff; color: #4338ca; }
        .net-negative td { background: #fff1f2; color: #e11d48; }
        .text-right { text-align: right; }
        .text-emerald { color: #059669; }
        .text-rose { color: #e11d48; }
        .text-muted { color: #6b7280; }
        .school-header { border-bottom: 2px solid #e5e7eb; margin-bottom: 16px; padding-bottom: 8px; }
        .col-half { display: table-cell; width: 50%; vertical-align: top; padding: 0 6px; }
        .two-col { display: table; width: 100%; margin-bottom: 16px; }
    </style>
</head>
<body>
    @php
        $totalIncome = $income->sum('amount');
        $totalFees = $fees->sum('amount');
        $totalInflows = $totalIncome + $totalFees;
        $totalOutflows = $expenses->sum('amount');
        $netCashflow = $totalInflows - $totalOutflows;
    @endphp

    <div class="school-header">
        <h1>{{ config('app.name') }}</h1>
        <div class="subtitle">Cashflow Statement &mdash; {{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}</div>
    </div>

    <table>
        <tr>
            <td style="width:33%;padding:8px;border:1px solid #e5e7eb;border-radius:4px;">
                <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:700;">Total Inflows</div>
                <div style="font-size:14px;font-weight:800;color:#059669;">KES {{ number_format($totalInflows, 2) }}</div>
            </td>
            <td style="width:33%;padding:8px;border:1px solid #e5e7eb;border-radius:4px;">
                <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:700;">Total Outflows</div>
                <div style="font-size:14px;font-weight:800;color:#e11d48;">KES {{ number_format($totalOutflows, 2) }}</div>
            </td>
            <td style="width:33%;padding:8px;border:1px solid #e5e7eb;border-radius:4px;">
                <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:700;">Net Cashflow</div>
                <div style="font-size:14px;font-weight:800;color:{{ $netCashflow >= 0 ? '#4338ca' : '#e11d48' }};">KES {{ number_format($netCashflow, 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- INFLOWS --}}
    <table>
        <thead>
            <tr>
                <th colspan="2" style="background:#ecfdf5;color:#065f46;">CASH INFLOWS</th>
            </tr>
            <tr>
                <th>Category / Item</th>
                <th class="text-right">Amount (KES)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-header"><td colspan="2">Fee Collections</td></tr>
            <tr>
                <td>Total Student Fees</td>
                <td class="text-right text-emerald">{{ number_format($totalFees, 2) }}</td>
            </tr>

            <tr class="section-header"><td colspan="2">Other Income</td></tr>
            @forelse($income->groupBy('category_id') as $catId => $items)
                <tr>
                    <td>{{ $items->first()->category ? $items->first()->category->name : 'Uncategorized' }}</td>
                    <td class="text-right">{{ number_format($items->sum('amount'), 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-muted" style="text-align:center;">No other income recorded.</td></tr>
            @endforelse
            <tr class="total-row">
                <td>TOTAL INFLOWS</td>
                <td class="text-right text-emerald">{{ number_format($totalInflows, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- OUTFLOWS --}}
    <table>
        <thead>
            <tr>
                <th colspan="2" style="background:#fff1f2;color:#9f1239;">CASH OUTFLOWS</th>
            </tr>
            <tr>
                <th>Category</th>
                <th class="text-right">Amount (KES)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses->groupBy('category_id') as $catId => $items)
                <tr>
                    <td>{{ $items->first()->category ? $items->first()->category->name : 'Uncategorized' }}</td>
                    <td class="text-right text-rose">{{ number_format($items->sum('amount'), 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="text-muted" style="text-align:center;">No expenses recorded.</td></tr>
            @endforelse
            <tr class="total-row">
                <td>TOTAL OUTFLOWS</td>
                <td class="text-right text-rose">{{ number_format($totalOutflows, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <tbody>
            <tr class="{{ $netCashflow >= 0 ? 'net-positive' : 'net-negative' }} net-row">
                <td>NET CASHFLOW</td>
                <td class="text-right">{{ number_format($netCashflow, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:24px;font-size:9px;color:#9ca3af;text-align:center;">
        Generated on {{ now()->format('d M Y H:i') }} &bull; {{ config('app.name') }} &bull; Cash basis
    </div>
</body>
</html>
