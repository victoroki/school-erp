<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Enrollment Trends Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; margin: 20px; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        .subtitle { color: #666; font-size: 12px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f3f4f6; font-size: 9px; text-transform: uppercase; padding: 6px 8px; text-align: left; border-bottom: 2px solid #ddd; }
        td { padding: 5px 8px; border-bottom: 1px solid #eee; font-size: 10px; }
        .text-center { text-align: center; }
        .text-success { color: #059669; }
        .text-danger { color: #dc2626; }
        .badge { padding: 2px 6px; border-radius: 10px; font-size: 9px; color: #fff; }
        .badge-primary { background: #4f46e5; }
        .badge-secondary { background: #6b7280; }
        .footer { margin-top: 15px; font-size: 9px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <h1>Enrollment Trends Report</h1>
    <div class="subtitle">Year-over-year enrollment across academic years — Generated {{ now()->format('d M Y, h:i A') }}</div>

    <table>
        <thead>
            <tr><th>Academic Year</th><th class="text-center">Total</th><th class="text-center">Male</th><th class="text-center">Female</th><th class="text-center">Status</th></tr>
        </thead>
        <tbody>
            @foreach($trends as $t)
                <tr>
                    <td><strong>{{ $t->year_name }}</strong></td>
                    <td class="text-center">{{ $t->total }}</td>
                    <td class="text-center">{{ $t->male }}</td>
                    <td class="text-center">{{ $t->female }}</td>
                    <td class="text-center">
                        @if($t->is_current)<span class="badge badge-primary">Current</span>
                        @else<span class="badge badge-secondary">Completed</span>@endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">School ERP — Enrollment Trends Report</div>
</body>
</html>
