<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Parent Relationships{{ $className ? ' — ' . $className : '' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #1e293b; margin: 24px; font-size: 12px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .meta { color: #64748b; font-size: 11px; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.04em; }
        .primary { font-weight: bold; }
        @media print {
            body { margin: 8mm; }
            th { background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <h1>Student Parent Relationships</h1>
    <div class="meta">
        {{ $className ? 'Class: ' . $className . ' · ' : '' }}
        {{ $relationships->count() }} record(s) · Printed {{ now()->format('d M Y, H:i') }}
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Admission No</th>
                <th>Parent / Guardian</th>
                <th>Relationship</th>
                <th>Phone</th>
                <th>Primary</th>
            </tr>
        </thead>
        <tbody>
            @forelse($relationships as $i => $rel)
                @php
                    $student = $rel->student;
                    $parent = $rel->parent;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ trim(($student?->first_name ?? '') . ' ' . ($student?->last_name ?? '')) ?: '—' }}</td>
                    <td>{{ $student?->admission_no ?? '—' }}</td>
                    <td class="{{ $rel->is_primary_contact ? 'primary' : '' }}">{{ trim(($parent?->first_name ?? '') . ' ' . ($parent?->last_name ?? '')) ?: '—' }}</td>
                    <td>{{ $parent?->relationship ? ucfirst($parent->relationship) : '—' }}</td>
                    <td>{{ $parent?->phone ?? '—' }}</td>
                    <td>{{ $rel->is_primary_contact ? 'Yes' : 'No' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding: 24px;">No relationships found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <script>window.onload = function () { window.print(); };</script>
</body>
</html>
