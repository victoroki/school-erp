<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hostel Allocations</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        .sub { color: #64748b; margin-bottom: 14px; font-size: 10px; }
        .metrics { margin-bottom: 14px; }
        .metric { display: inline-block; margin-right: 22px; }
        .metric .label { color: #64748b; font-size: 9px; text-transform: uppercase; }
        .metric .value { font-weight: 800; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th { background: #f1f5f9; text-align: left; padding: 5px 7px; font-size: 9px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #e2e8f0; }
        td { padding: 5px 7px; border-bottom: 1px solid #f1f5f9; }
        .center { text-align: center; }
        .muted { color: #94a3b8; }
        .em { color: #059669; font-weight: 700; }
    </style>
</head>
<body>
    <h1>Hostel Allocations</h1>
    <div class="sub">
        {{ config('app.name') }} &middot; Generated {{ now()->format('d M Y H:i') }} &middot; {{ $scopeLabel }}
    </div>

    <div class="metrics">
        <div class="metric"><div class="label">Allocations</div><div class="value">{{ $allocations->count() }}</div></div>
        <div class="metric"><div class="label">Active</div><div class="value em">{{ $allocations->where('status', 'active')->count() }}</div></div>
        <div class="metric"><div class="label">Vacated</div><div class="value">{{ $allocations->where('status', 'vacated')->count() }}</div></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Adm. No</th>
                <th>Student</th>
                <th>Class / Stream</th>
                <th>Hostel</th>
                <th>Room</th>
                <th class="center">Bed</th>
                <th>Allocated</th>
                <th>Vacated</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($allocations as $allocation)
                <tr>
                    <td>{{ optional($allocation->student)->admission_no ?? '—' }}</td>
                    <td>{{ optional($allocation->student)->first_name ?? 'Unknown' }} {{ optional($allocation->student)->last_name ?? '' }}</td>
                    <td>{{ $allocation->class_info }}</td>
                    <td>{{ optional($allocation->hostel)->name ?? '—' }}</td>
                    <td>{{ optional($allocation->room)->room_number ?? '—' }}</td>
                    <td class="center">{{ $allocation->bed_number ?? '—' }}</td>
                    <td>{{ optional($allocation->allocation_date)->format('d M Y') }}</td>
                    <td>{{ $allocation->vacating_date ? $allocation->vacating_date->format('d M Y') : '—' }}</td>
                    <td class="center">{{ ucfirst($allocation->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="muted">No allocations match the selected scope.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
