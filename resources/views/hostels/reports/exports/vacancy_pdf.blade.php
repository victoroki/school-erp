<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hostel Vacancy Report</title>
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
    <h1>Hostel Vacancy Report</h1>
    <div class="sub">
        {{ config('app.name') }} &middot; Generated {{ now()->format('d M Y H:i') }} &middot; {{ $hostel->name ?? 'All hostels' }}
    </div>

    <div class="metrics">
        <div class="metric"><div class="label">Rooms with space</div><div class="value">{{ $summary['rooms'] }}</div></div>
        <div class="metric"><div class="label">Beds in those rooms</div><div class="value">{{ $summary['capacity'] }}</div></div>
        <div class="metric"><div class="label">Beds taken</div><div class="value">{{ $summary['occupied'] }}</div></div>
        <div class="metric"><div class="label">Beds free</div><div class="value em">{{ $summary['free_beds'] }}</div></div>
        <div class="metric"><div class="label">Rooms in maintenance</div><div class="value">{{ $summary['maintenance_rooms'] }}</div></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Hostel</th>
                <th>Room</th>
                <th>Type</th>
                <th>Floor</th>
                <th class="center">Capacity</th>
                <th class="center">Occupied</th>
                <th class="center">Free</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rooms as $room)
                <tr>
                    <td>{{ optional($room->hostel)->name ?? '—' }}</td>
                    <td>{{ $room->room_number }}</td>
                    <td>{{ ucfirst((string) $room->room_type) }}</td>
                    <td>{{ $room->floor ?: '—' }}</td>
                    <td class="center">{{ $room->capacity }}</td>
                    <td class="center">{{ $room->occupied }}</td>
                    <td class="center"><strong>{{ $room->getAvailableBeds() }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">Every room is full or under maintenance.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
