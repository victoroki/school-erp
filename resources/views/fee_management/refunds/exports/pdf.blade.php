<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Refund Register</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .sub { color: #64748b; margin-bottom: 12px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th { background:#f1f5f9; text-align:left; padding:5px 7px; font-size:9px; text-transform:uppercase; color:#475569; border-bottom:1px solid #e2e8f0; }
        td { padding:5px 7px; border-bottom:1px solid #f1f5f9; }
        .right { text-align:right; }
    </style>
</head>
<body>
    <h1>Refund Register</h1>
    <div class="sub">Generated {{ now()->format('d M Y H:i') }}</div>
    <table>
        <thead>
            <tr><th>ID</th><th>Student</th><th class="right">Amount</th><th>Reason</th><th>Status</th><th>Requested By</th><th>Requested At</th></tr>
        </thead>
        <tbody>
            @forelse($refunds as $r)
                <tr>
                    <td>#{{ $r->id }}</td>
                    <td>{{ $r->student->full_name ?? 'N/A' }}</td>
                    <td class="right">KSh {{ number_format($r->amount, 2) }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($r->reason, 70) }}</td>
                    <td>{{ ucfirst($r->status) }}</td>
                    <td>{{ $r->requestedBy->name ?? '' }}</td>
                    <td>{{ $r->requested_at ? $r->requested_at->format('d M Y H:i') : '' }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No refunds found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
