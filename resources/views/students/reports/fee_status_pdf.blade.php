<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Status Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 20px; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        .subtitle { color: #666; font-size: 12px; margin-bottom: 15px; }
        .stats { display: flex; gap: 20px; margin-bottom: 15px; }
        .stat-box { border: 1px solid #ddd; border-radius: 6px; padding: 8px 12px; flex: 1; text-align: center; }
        .stat-value { font-size: 16px; font-weight: bold; }
        .stat-label { font-size: 9px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f3f4f6; font-size: 9px; text-transform: uppercase; padding: 6px 8px; text-align: left; border-bottom: 2px solid #ddd; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-danger { color: #dc2626; }
        .text-success { color: #059669; }
        .badge { padding: 2px 6px; border-radius: 10px; font-size: 9px; color: #fff; }
        .badge-success { background: #059669; }
        .badge-warning { background: #d97706; }
        .badge-danger { background: #dc2626; }
        .footer { margin-top: 15px; font-size: 9px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <h1>Fee Status Report</h1>
    <div class="subtitle">{{ $currentYear->name ?? 'All Years' }} — Generated {{ now()->format('d M Y, h:i A') }}</div>

    <div class="stats">
        <div class="stat-box"><div class="stat-value text-danger">{{ $studentsWithArrears }}</div><div class="stat-label">Students with Arrears</div></div>
        <div class="stat-box"><div class="stat-value">KES {{ number_format($totalAssigned, 0) }}</div><div class="stat-label">Total Assigned</div></div>
        <div class="stat-box"><div class="stat-value text-success">KES {{ number_format($totalPaid, 0) }}</div><div class="stat-label">Total Collected</div></div>
        <div class="stat-box"><div class="stat-value text-danger">KES {{ number_format($totalBalance, 0) }}</div><div class="stat-label">Outstanding</div></div>
    </div>

    <table>
        <thead>
            <tr><th>Admission No</th><th>Student Name</th><th>Class</th><th class="text-right">Assigned</th><th class="text-right">Paid</th><th class="text-right">Balance</th><th class="text-center">Status</th></tr>
        </thead>
        <tbody>
            @forelse($students as $s)
                <tr>
                    <td>{{ $s->admission_no }}</td>
                    <td><strong>{{ $s->full_name }}</strong></td>
                    <td>{{ $s->class_info }}</td>
                    <td class="text-right">KES {{ number_format($s->total_assigned, 2) }}</td>
                    <td class="text-right text-success">KES {{ number_format($s->total_paid, 2) }}</td>
                    <td class="text-right text-danger"><strong>KES {{ number_format($s->balance, 2) }}</strong></td>
                    <td class="text-center">
                        @if($s->balance <= 0)<span class="badge badge-success">Paid</span>
                        @elseif($s->balance < $s->total_assigned * 0.5)<span class="badge badge-warning">Partial</span>
                        @else<span class="badge badge-danger">Unpaid</span>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">No data found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">School ERP — Fee Status Report</div>
</body>
</html>
