<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transport &amp; Hostel Report</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; margin: 15px; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        h2 { font-size: 13px; margin: 15px 0 5px; color: #333; border-bottom: 2px solid #ddd; padding-bottom: 3px; }
        .subtitle { color: #666; font-size: 11px; margin-bottom: 12px; }
        .stats { display: flex; gap: 15px; margin-bottom: 12px; }
        .stat-box { border: 1px solid #ddd; border-radius: 6px; padding: 6px 10px; flex: 1; text-align: center; }
        .stat-value { font-size: 14px; font-weight: bold; }
        .stat-label { font-size: 8px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th { background: #f3f4f6; font-size: 8px; text-transform: uppercase; padding: 5px 6px; text-align: left; border-bottom: 2px solid #ddd; }
        td { padding: 4px 6px; border-bottom: 1px solid #eee; font-size: 9px; }
        .footer { margin-top: 12px; font-size: 8px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <h1>Transport &amp; Hostel Report</h1>
    <div class="subtitle">Generated {{ now()->format('d M Y, h:i A') }}</div>

    <div class="stats">
        <div class="stat-box"><div class="stat-value">{{ $totalTransport }}</div><div class="stat-label">Transport Users</div></div>
        <div class="stat-box"><div class="stat-value">{{ $totalHostel }}</div><div class="stat-label">Hostel Residents</div></div>
        <div class="stat-box"><div class="stat-value">{{ $totalTransport + $totalHostel }}</div><div class="stat-label">Total</div></div>
    </div>

    <h2>Transport Users ({{ $totalTransport }})</h2>
    <table>
        <thead><tr><th>Admission No</th><th>Student</th><th>Class</th><th>Route</th><th>Pickup Point</th></tr></thead>
        <tbody>
            @forelse($transportStudents as $s)
                <tr>
                    <td>{{ $s->admission_no }}</td>
                    <td><strong>{{ $s->full_name }}</strong></td>
                    <td>{{ $s->class_info }}</td>
                    <td>{{ $s->route_id ? 'Route #' . $s->route_id : '—' }}</td>
                    <td>{{ $s->pickup_point ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">No transport users.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Hostel Residents ({{ $totalHostel }})</h2>
    <table>
        <thead><tr><th>Admission No</th><th>Student</th><th>Class</th></tr></thead>
        <tbody>
            @forelse($hostelStudents as $s)
                <tr>
                    <td>{{ $s->admission_no }}</td>
                    <td><strong>{{ $s->full_name }}</strong></td>
                    <td>{{ $s->class_info }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center">No hostel residents.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">School ERP — Transport &amp; Hostel Report</div>
</body>
</html>
