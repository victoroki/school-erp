<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Arrears Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 17px; margin: 0 0 2px; }
        .sub { color: #64748b; margin-bottom: 16px; font-size: 11px; }
        .metrics { margin-bottom: 16px; }
        .metric { display: inline-block; margin-right: 24px; }
        .metric .label { color: #64748b; font-size: 10px; text-transform: uppercase; }
        .metric .value { font-weight: 800; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #f1f5f9; text-align: left; padding: 6px 8px; font-size: 10px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #e2e8f0; }
        td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; }
        .right { text-align: right; }
        .rose { color: #e11d48; font-weight: 700; }
        .em { color: #059669; font-weight: 700; }
    </style>
</head>
<body>
    <h1>Fee Arrears Report &mdash; {{ $scopeLabel }}</h1>
    <div class="sub">Generated {{ now()->format('d M Y H:i') }} &middot; Students with outstanding balances &middot; Scope: {{ $scopeLabel }}</div>

    <div class="metrics">
        <div class="metric"><div class="label">Total Expected</div><div class="value">{{ \App\Support\Money::format($totalExpected) }}</div></div>
        <div class="metric"><div class="label">Total Collected</div><div class="value em">{{ \App\Support\Money::format($totalCollected) }}</div></div>
        <div class="metric"><div class="label">Outstanding</div><div class="value rose">{{ \App\Support\Money::format($totalOutstanding) }}</div></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Admission No</th>
                <th>Student Name</th>
                <th class="right">Expected</th>
                <th class="right">Paid</th>
                <th class="right">Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['admission_no'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td class="right">{{ \App\Support\Money::format($row['expected']) }}</td>
                    <td class="right em">{{ \App\Support\Money::format($row['paid']) }}</td>
                    <td class="right rose">{{ \App\Support\Money::format($row['outstanding']) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No students in arrears.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
