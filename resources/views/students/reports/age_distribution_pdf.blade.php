<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Age Distribution Report</title>
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
        .text-center { text-align: center; }
        .footer { margin-top: 15px; font-size: 9px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <h1>Age Distribution Report</h1>
    <div class="subtitle">Generated {{ now()->format('d M Y, h:i A') }}</div>

    <div class="stats">
        <div class="stat-box"><div class="stat-value">{{ $totalStudents }}</div><div class="stat-label">Total Students</div></div>
        <div class="stat-box"><div class="stat-value">{{ $avgAge }} yrs</div><div class="stat-label">Average Age</div></div>
    </div>

    <table>
        <thead><tr><th>Age Group</th><th class="text-center">Count</th><th class="text-center">Percentage</th></tr></thead>
        <tbody>
            @foreach($ageGroups as $label => $count)
                @php $pct = $totalStudents > 0 ? round(($count / $totalStudents) * 100, 1) : 0; @endphp
                <tr><td><strong>{{ $label }}</strong></td><td class="text-center">{{ $count }}</td><td class="text-center">{{ $pct }}%</td></tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">School ERP — Age Distribution Report</div>
</body>
</html>
