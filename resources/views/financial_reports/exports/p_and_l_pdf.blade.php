<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profit &amp; Loss Statement</title>
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
        .school-header { border-bottom: 2px solid #e5e7eb; margin-bottom: 16px; padding-bottom: 8px; }
        .summary-boxes { display: table; width: 100%; margin-bottom: 16px; }
        .summary-box { display: table-cell; width: 33%; padding: 10px; border: 1px solid #e5e7eb; border-radius: 6px; text-align: center; }
        .box-label { font-size: 9px; color: #6b7280; text-transform: uppercase; font-weight: 700; }
        .box-value { font-size: 14px; font-weight: 800; margin-top: 2px; }
    </style>
</head>
<body>
    <div class="school-header">
        <h1>{{ config('app.name') }}</h1>
        <div class="subtitle">Profit &amp; Loss Statement &mdash; {{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}</div>
    </div>

    @php $netProfit = $totalIncome - $totalExpenses; @endphp

    <table>
        <tr>
            <td style="width:33%;padding:8px;border:1px solid #e5e7eb;border-radius:4px;">
                <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:700;">Total Revenue</div>
                <div style="font-size:14px;font-weight:800;color:#059669;">KES {{ number_format($totalIncome, 2) }}</div>
            </td>
            <td style="width:33%;padding:8px;border:1px solid #e5e7eb;border-radius:4px;">
                <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:700;">Total Expenses</div>
                <div style="font-size:14px;font-weight:800;color:#e11d48;">KES {{ number_format($totalExpenses, 2) }}</div>
            </td>
            <td style="width:33%;padding:8px;border:1px solid #e5e7eb;border-radius:4px;">
                <div style="font-size:9px;color:#6b7280;text-transform:uppercase;font-weight:700;">Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</div>
                <div style="font-size:14px;font-weight:800;color:{{ $netProfit >= 0 ? '#4338ca' : '#e11d48' }};">KES {{ number_format($netProfit, 2) }}</div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount (KES)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="section-header"><td colspan="2">REVENUE</td></tr>
            <tr>
                <td>Total Operating Revenue (Fees &amp; Income)</td>
                <td class="text-right text-emerald">{{ number_format($totalIncome, 2) }}</td>
            </tr>

            <tr class="section-header"><td colspan="2">OPERATING EXPENSES</td></tr>
            @forelse($expenseBreakdown as $expense)
                <tr>
                    <td>{{ $expense->category ? $expense->category->name : 'Uncategorized Expenses' }}</td>
                    <td class="text-right text-rose">{{ number_format($expense->total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" style="text-align:center;color:#9ca3af;">No expenses recorded for this period.</td></tr>
            @endforelse
            <tr class="total-row">
                <td>Total Operating Expenses</td>
                <td class="text-right text-rose">{{ number_format($totalExpenses, 2) }}</td>
            </tr>

            <tr class="{{ $netProfit >= 0 ? 'net-positive' : 'net-negative' }} net-row">
                <td>NET {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }}</td>
                <td class="text-right">{{ number_format($netProfit, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:24px;font-size:9px;color:#9ca3af;text-align:center;">
        Generated on {{ now()->format('d M Y H:i') }} &bull; {{ config('app.name') }} &bull; Accrual basis
    </div>
</body>
</html>
