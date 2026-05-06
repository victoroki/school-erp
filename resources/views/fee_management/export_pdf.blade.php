<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Fee Management Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: #1e293b; font-size: 9pt; line-height: 1.4; padding: 15mm; }

        .report-header { text-align: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #4f46e5; }
        .school-name { font-size: 18pt; font-weight: 900; color: #4f46e5; }
        .report-title { font-size: 11pt; font-weight: 700; color: #475569; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.06em; }
        .report-date { font-size: 8pt; color: #94a3b8; margin-top: 4px; }

        .metrics { margin-bottom: 20px; }
        .metric-row { display: flex; gap: 12px; margin-bottom: 12px; }
        .metric-card { flex: 1; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; text-align: center; }
        .metric-label { font-size: 7pt; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; }
        .metric-value { font-size: 12pt; font-weight: 900; font-family: 'Consolas', monospace; margin-top: 2px; }
        .metric-value.total { color: #1e293b; }
        .metric-value.collected { color: #10b981; }
        .metric-value.pending { color: #f59e0b; }
        .metric-value.rate { color: #4f46e5; }

        .section-title { font-size: 10pt; font-weight: 800; color: #1e293b; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead { background: #f8fafc; }
        th { padding: 6px 8px; font-size: 7pt; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; text-align: left; border-bottom: 1px solid #e2e8f0; }
        td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; font-size: 8.5pt; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .mono { font-family: 'Consolas', 'Monaco', monospace; }
        .fw-700 { font-weight: 700; }
        .text-green { color: #10b981; }
        .text-red { color: #f43f5e; }
        .text-muted { color: #94a3b8; }

        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 7pt; font-weight: 700; text-transform: capitalize; }
        .badge-paid { background: #ecfdf5; color: #10b981; }
        .badge-partial { background: #fffbeb; color: #d97706; }
        .badge-unpaid { background: #fff1f2; color: #f43f5e; }

        .report-footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 7pt; color: #94a3b8; font-style: italic; }

        @page { margin: 10mm; size: A4 landscape; }
    </style>
</head>
<body>
    <div class="report-header">
        <div class="school-name">{{ config('app.name') }}</div>
        <div class="report-title">Fee Management Report</div>
        <div class="report-date">Generated on {{ date('d F Y, h:i A') }}</div>
    </div>

    <div class="metrics">
        <div class="metric-row">
            <div class="metric-card">
                <div class="metric-label">Total Receivable</div>
                <div class="metric-value total">{{ number_format($metrics['total_receivable'], 2) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Collected</div>
                <div class="metric-value collected">{{ number_format($metrics['total_collected'], 2) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Pending</div>
                <div class="metric-value pending">{{ number_format($metrics['total_pending'], 2) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Collection Rate</div>
                <div class="metric-value rate">{{ $metrics['collection_rate'] }}%</div>
            </div>
        </div>
    </div>

    <div class="section-title">Student Fee Records ({{ $students->count() }} students)</div>
    <table>
        <thead>
            <tr>
                <th>Admission No</th>
                <th>Student Name</th>
                <th>Class</th>
                <th class="text-right">Total Fee</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Balance</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                <tr>
                    <td class="mono text-muted">{{ $student->admission_no }}</td>
                    <td class="fw-700">{{ $student->full_name }}</td>
                    <td>
                        @foreach($student->studentClassEnrollments as $enrollment)
                            {{ $enrollment->classSection->schoolClass->name ?? '' }}{{ $enrollment->classSection->section->name ? ' - ' . $enrollment->classSection->section->name : '' }}
                        @endforeach
                    </td>
                    <td class="text-right mono fw-700">{{ number_format($student->total_fee, 2) }}</td>
                    <td class="text-right mono text-green">{{ number_format($student->paid_fee, 2) }}</td>
                    <td class="text-right mono {{ $student->balance_fee > 0 ? 'text-red fw-700' : 'text-muted' }}">{{ number_format($student->balance_fee, 2) }}</td>
                    <td class="text-center">
                        <span class="badge {{ $student->payment_status == 'Paid' ? 'badge-paid' : ($student->payment_status == 'Partial' ? 'badge-partial' : 'badge-unpaid') }}">
                            {{ $student->payment_status }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="report-footer">
        This is a system-generated report from {{ config('app.name') }} School ERP.
    </div>
</body>
</html>
