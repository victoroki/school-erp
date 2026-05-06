<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Fee Statement - {{ $student->full_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #1e293b; font-size: 11pt; line-height: 1.5;
            padding: 20mm; background: #fff;
        }

        .no-print { text-align: center; margin-top: 30px; }
        .no-print button {
            padding: 10px 24px; border: 1px solid #e2e8f0; border-radius: 6px;
            background: #fff; color: #334155; font-size: 13px; font-weight: 600;
            cursor: pointer; margin: 0 8px;
        }
        .no-print button.primary { background: #4f46e5; color: #fff; border-color: #4f46e5; }
        .no-print button:hover { opacity: 0.9; }

        /* Header */
        .statement-header { text-align: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 2px solid #4f46e5; }
        .school-name { font-size: 22pt; font-weight: 900; color: #4f46e5; letter-spacing: -0.02em; }
        .statement-title { font-size: 13pt; font-weight: 700; color: #475569; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.06em; }
        .statement-date { font-size: 9pt; color: #94a3b8; margin-top: 4px; }

        /* Student Info */
        .student-info { margin-bottom: 24px; }
        .student-info-grid { display: flex; gap: 24px; }
        .student-info-item { flex: 1; }
        .student-info-label { font-size: 8pt; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
        .student-info-value { font-size: 11pt; font-weight: 700; color: #1e293b; margin-top: 2px; }
        .student-info-value.mono { font-family: 'Consolas', 'Monaco', monospace; }

        /* Summary */
        .summary-section { margin-bottom: 24px; }
        .summary-title { font-size: 11pt; font-weight: 800; color: #1e293b; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 16px; }
        .summary-card { padding: 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-align: center; }
        .summary-card-label { font-size: 8pt; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; }
        .summary-card-value { font-size: 14pt; font-weight: 900; font-family: 'Consolas', monospace; margin-top: 4px; }
        .summary-card-value.total { color: #1e293b; }
        .summary-card-value.paid { color: #10b981; }
        .summary-card-value.balance { color: #f43f5e; }
        .summary-card-value.rate { color: #4f46e5; }

        /* Tables */
        .section-title { font-size: 11pt; font-weight: 800; color: #1e293b; margin: 24px 0 12px; padding-bottom: 8px; border-bottom: 1px solid #e2e8f0; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead { background: #f8fafc; }
        th {
            padding: 8px 10px; font-size: 8pt; font-weight: 800; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 0.05em; text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; font-size: 10pt; }
        tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .mono { font-family: 'Consolas', 'Monaco', monospace; }
        .fw-700 { font-weight: 700; }
        .text-green { color: #10b981; }
        .text-red { color: #f43f5e; }
        .text-muted { color: #94a3b8; }
        .text-sm { font-size: 9pt; }

        /* Status Badges */
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 8pt; font-weight: 700; text-transform: capitalize; }
        .badge-paid { background: #ecfdf5; color: #10b981; }
        .badge-partial { background: #fffbeb; color: #d97706; }
        .badge-unpaid { background: #fff1f2; color: #f43f5e; }

        /* Footer */
        .statement-footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e2e8f0; text-align: center; }
        .footer-note { font-size: 8pt; color: #94a3b8; font-style: italic; }
        .signature-section { display: flex; justify-content: space-between; margin-top: 60px; }
        .signature-box { text-align: center; width: 200px; }
        .signature-line { border-top: 1px solid #94a3b8; padding-top: 4px; font-size: 9pt; font-weight: 600; color: #475569; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 15mm; }
            @page { margin: 10mm; size: A4; }
        }
    </style>
</head>
<body>
    <div class="statement-header">
        <div class="school-name">{{ config('app.name') }}</div>
        <div class="statement-title">Fee Balance Statement</div>
        <div class="statement-date">Generated on {{ date('d F Y, h:i A') }}</div>
    </div>

    <div class="student-info">
        <div class="student-info-grid">
            <div class="student-info-item">
                <div class="student-info-label">Student Name</div>
                <div class="student-info-value">{{ $student->full_name }}</div>
            </div>
            <div class="student-info-item">
                <div class="student-info-label">Admission Number</div>
                <div class="student-info-value mono">{{ $student->admission_no }}</div>
            </div>
            <div class="student-info-item">
                <div class="student-info-label">Class</div>
                @php
                    $classInfo = $student->studentClassEnrollments->first();
                    $className = $classInfo ? ($classInfo->classSection->schoolClass->name ?? 'N/A') : 'N/A';
                    $sectionName = $classInfo ? ($classInfo->classSection->section->name ?? '') : '';
                @endphp
                <div class="student-info-value">{{ $className }}{{ $sectionName ? ' - ' . $sectionName : '' }}</div>
            </div>
            <div class="student-info-item">
                <div class="student-info-label">Statement Date</div>
                <div class="student-info-value">{{ date('d M Y') }}</div>
            </div>
        </div>
    </div>

    <div class="summary-section">
        <div class="summary-title">Financial Summary</div>
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-card-label">Total Fee</div>
                <div class="summary-card-value total">{{ number_format($student->total_fee, 2) }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Total Paid</div>
                <div class="summary-card-value paid">{{ number_format($student->paid_fee, 2) }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Balance</div>
                <div class="summary-card-value balance">{{ number_format($student->balance_fee, 2) }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-label">Status</div>
                <div class="summary-card-value" style="font-size: 11pt; margin-top: 8px;">
                    @php
                        $status = $student->payment_status;
                        $badgeClass = match($status) {
                            'Paid' => 'badge-paid',
                            'Partial' => 'badge-partial',
                            'Unpaid' => 'badge-unpaid',
                            default => 'badge-unpaid'
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="section-title">Fee Breakdown</div>
    <table>
        <thead>
            <tr>
                <th>Fee Type</th>
                <th>Due Date</th>
                <th class="text-right">Base Amount</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Final Amount</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Balance</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($student->feeAssignments as $fee)
                <tr>
                    <td class="fw-700">{{ $fee->feeStructure->category->name ?? 'N/A' }}</td>
                    <td class="text-sm text-muted">{{ $fee->assigned_date ? $fee->assigned_date->format('d M Y') : 'N/A' }}</td>
                    <td class="text-right mono">{{ number_format($fee->amount, 2) }}</td>
                    <td class="text-right mono text-green">-{{ number_format($fee->discount_amount, 2) }}</td>
                    <td class="text-right mono fw-700">{{ number_format($fee->final_amount, 2) }}</td>
                    <td class="text-right mono text-green">{{ number_format($fee->paid_amount, 2) }}</td>
                    <td class="text-right mono {{ $fee->balance > 0 ? 'text-red fw-700' : 'text-muted' }}">{{ number_format($fee->balance, 2) }}</td>
                    <td class="text-center">
                        <span class="badge {{ $fee->payment_status == 'paid' ? 'badge-paid' : ($fee->payment_status == 'partial' ? 'badge-partial' : 'badge-unpaid') }}">
                            {{ ucfirst($fee->payment_status) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Payment History</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Receipt No</th>
                <th>Method</th>
                <th>Reference</th>
                <th class="text-right">Amount</th>
                <th>Collected By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($student->feeAssignments->flatMap->payments as $payment)
                <tr>
                    <td class="text-sm">{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</td>
                    <td class="mono fw-700">{{ $payment->receipt_number }}</td>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                    <td class="mono text-sm text-muted">{{ $payment->transaction_id ?? '-' }}</td>
                    <td class="text-right mono text-green fw-700">{{ number_format($payment->amount, 2) }}</td>
                    <td class="text-sm">{{ $payment->collected_by ?? 'System' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted text-sm" style="padding: 24px;">No payments recorded yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Bursar / Finance Officer</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Parent / Guardian</div>
        </div>
    </div>

    <div class="statement-footer">
        <div class="footer-note">This is a computer-generated statement. For inquiries, contact the school finance office.</div>
    </div>

    <div class="no-print">
        <button class="primary" onclick="window.print()"><i class="fas fa-print"></i> Print Statement</button>
        <button onclick="window.history.back()">Back</button>
    </div>
</body>
</html>
