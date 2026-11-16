<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Medical Records Report</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; margin: 15px; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        .subtitle { color: #666; font-size: 11px; margin-bottom: 12px; }
        .stats { display: flex; gap: 15px; margin-bottom: 12px; }
        .stat-box { border: 1px solid #ddd; border-radius: 6px; padding: 6px 10px; flex: 1; text-align: center; }
        .stat-value { font-size: 14px; font-weight: bold; }
        .stat-label { font-size: 8px; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #f3f4f6; font-size: 8px; text-transform: uppercase; padding: 5px 6px; text-align: left; border-bottom: 2px solid #ddd; }
        td { padding: 4px 6px; border-bottom: 1px solid #eee; font-size: 9px; }
        .text-danger { color: #dc2626; }
        .text-warning { color: #d97706; }
        .text-info { color: #0891b2; }
        .footer { margin-top: 12px; font-size: 8px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <h1>Medical Records Report</h1>
    <div class="subtitle">Students with health conditions — Generated {{ now()->format('d M Y, h:i A') }}</div>

    <div class="stats">
        <div class="stat-box"><div class="stat-value text-danger">{{ $totalWithConditions }}</div><div class="stat-label">Conditions</div></div>
        <div class="stat-box"><div class="stat-value text-warning">{{ $totalWithAllergies }}</div><div class="stat-label">Allergies</div></div>
        <div class="stat-box"><div class="stat-value text-info">{{ $totalOnMedication }}</div><div class="stat-label">On Medication</div></div>
    </div>

    <table>
        <thead>
            <tr><th>Admission No</th><th>Student</th><th>Class</th><th>Blood</th><th>Conditions</th><th>Allergies</th><th>Medications</th><th>Emergency</th></tr>
        </thead>
        <tbody>
            @forelse($students as $s)
                <tr>
                    <td>{{ $s->admission_no }}</td>
                    <td><strong>{{ $s->full_name }}</strong></td>
                    <td>{{ $s->class_info }}</td>
                    <td>{{ $s->blood_group ?? '—' }}</td>
                    <td class="text-danger">{{ \Illuminate\Support\Str::limit($s->medical_conditions ?? '—', 40) }}</td>
                    <td class="text-warning">{{ \Illuminate\Support\Str::limit($s->allergies ?? '—', 40) }}</td>
                    <td class="text-info">{{ \Illuminate\Support\Str::limit($s->medications ?? '—', 40) }}</td>
                    <td>{{ $s->emergency_contact_name ?? '—' }}<br>{{ $s->emergency_contact ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">No medical records found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">School ERP — Medical Records Report</div>
</body>
</html>
