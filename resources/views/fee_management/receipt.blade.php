<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fee Receipt {{ $payment->receipt_number }} — {{ $student->full_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #1e293b; font-size: 11pt; line-height: 1.5;
            padding: 16mm; background: #fff;
        }

        .no-print { text-align: center; margin: 20px 0; }
        .no-print button {
            padding: 10px 24px; border: 1px solid #e2e8f0; border-radius: 6px;
            background: #fff; color: #334155; font-size: 13px; font-weight: 600;
            cursor: pointer; margin: 0 8px;
        }
        .no-print button.primary { background: #10b981; color: #fff; border-color: #10b981; }
        .no-print button:hover { opacity: 0.9; }

        .receipt { max-width: 800px; margin: 0 auto; }

        /* Header */
        .receipt-header { text-align: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #10b981; }
        .school-name { font-size: 20pt; font-weight: 900; color: #10b981; letter-spacing: -0.02em; }
        .receipt-title { font-size: 12pt; font-weight: 700; color: #475569; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.08em; }
        .receipt-no { font-size: 10pt; font-weight: 800; color: #1e293b; margin-top: 8px; font-family: 'Consolas', 'Monaco', monospace; }

        /* Info grid */
        .receipt-meta { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 32px; margin-bottom: 20px; }
        .meta-item {}
        .meta-label { font-size: 8pt; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
        .meta-value { font-size: 11pt; font-weight: 700; color: #1e293b; margin-top: 2px; }
        .meta-value.mono { font-family: 'Consolas', 'Monaco', monospace; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th {
            padding: 8px 10px; font-size: 8pt; font-weight: 800; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 0.05em; text-align: left;
            background: #f8fafc; border-bottom: 1px solid #e2e8f0;
        }
        td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; font-size: 10pt; }
        .text-right { text-align: right; }
        .mono { font-family: 'Consolas', 'Monaco', monospace; }
        .fw-700 { font-weight: 700; }
        .text-muted { color: #94a3b8; }

        /* Amount highlight */
        .amount-section { margin-top: 16px; padding: 14px; border: 2px solid #10b981; border-radius: 8px; }
        .amount-row { display: flex; justify-content: space-between; align-items: baseline; }
        .amount-label { font-size: 9pt; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; }
        .amount-value { font-size: 18pt; font-weight: 900; color: #10b981; font-family: 'Consolas', monospace; }
        .amount-words { font-size: 10pt; font-weight: 600; color: #475569; margin-top: 6px; font-style: italic; }

        /* Balance summary */
        .balance-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 16px; }
        .balance-card { padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; text-align: center; }
        .balance-label { font-size: 8pt; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; }
        .balance-value { font-size: 12pt; font-weight: 900; font-family: 'Consolas', monospace; margin-top: 2px; }
        .balance-value.paid { color: #10b981; }
        .balance-value.due { color: #f43f5e; }

        /* Signatures */
        .signature-section { display: flex; justify-content: space-between; margin-top: 56px; }
        .signature-box { text-align: center; width: 200px; }
        .signature-line { border-top: 1px solid #94a3b8; padding-top: 4px; font-size: 9pt; font-weight: 600; color: #475569; }

        .receipt-footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #e2e8f0; text-align: center; }
        .footer-note { font-size: 8pt; color: #94a3b8; font-style: italic; }

        /* VOID watermark — matches the receipt register / fees tab treatment */
        .void-watermark {
            display: none;
            position: fixed; top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 72pt; font-weight: 900; color: rgba(220, 38, 38, 0.18);
            border: 6px solid rgba(220, 38, 38, 0.18); border-radius: 12px;
            padding: 8px 32px; pointer-events: none; z-index: 10;
            text-transform: uppercase; letter-spacing: 0.1em;
        }
        .void-banner {
            display: none; margin: 12px 0; padding: 10px 14px; border-radius: 6px;
            background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c;
            font-weight: 800; text-align: center; text-transform: uppercase; letter-spacing: 0.05em;
        }
        .is-void .void-watermark { display: block; }
        .is-void .void-banner { display: block; }
        .is-void .amount-value { color: #f43f5e; }
        .is-void .receipt-header { border-bottom-color: #f43f5e; }
        .is-void .school-name { color: #f43f5e; }
        .is-void .amount-section { border-color: #f43f5e; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 10mm; }
            @page { margin: 10mm; size: A4; }
        }
    </style>
</head>
<body class="{{ $payment->isReversed() ? 'is-void' : '' }}">
    <div class="void-watermark">Void</div>

    <div class="receipt">
        <div class="receipt-header">
            <div class="school-name">{{ config('app.name') }}</div>
            <div class="receipt-title">Official Fee Receipt</div>
            <div class="receipt-no">Receipt No: {{ $payment->receipt_number ?? 'RCP-' . $payment->payment_id }}</div>
        </div>

        @if($payment->isReversed())
            <div class="void-banner">
                Void — reversed {{ $payment->reversed_at?->format('d M Y') ?? '' }}
            </div>
        @endif

        <div class="receipt-meta">
            <div class="meta-item">
                <div class="meta-label">Student Name</div>
                <div class="meta-value">{{ $student->full_name }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Admission No</div>
                <div class="meta-value mono">{{ $student->admission_no }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Class</div>
                @php
                    $enrollment = $student->studentClassEnrollments->first();
                    $className = $enrollment?->classSection?->schoolClass?->name ?? 'N/A';
                    $sectionName = $enrollment?->classSection?->section?->name ?? '';
                @endphp
                <div class="meta-value">{{ $className }}{{ $sectionName ? ' - ' . $sectionName : '' }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Payment Date</div>
                <div class="meta-value">{{ $payment->payment_date?->format('d M Y') ?? 'N/A' }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Payment Method</div>
                <div class="meta-value">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $payment->payment_method ?: 'Unspecified')) }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Transaction Ref</div>
                <div class="meta-value mono">{{ $payment->transaction_id ?? '—' }}</div>
            </div>
        </div>

        {{-- Allocation breakdown: one row per fee the payment was applied to.
             Reversed allocations are shown struck-through with the ledger's
             void treatment. --}}
        <table>
            <thead>
                <tr>
                    <th>Fee Item</th>
                    <th>Academic Year</th>
                    <th>Term</th>
                    <th class="text-right">Amount Applied</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payment->allocations as $allocation)
                    @php $assignment = $allocation->studentFeeAssignment; @endphp
                    <tr>
                        <td class="fw-700">{{ $assignment?->feeStructure?->category?->name ?? 'Fee' }}</td>
                        <td class="text-muted">{{ $assignment?->feeStructure?->academicYear?->name ?? '—' }}</td>
                        <td class="text-muted">{{ $assignment?->term ?? $assignment?->termModel?->name ?? '—' }}</td>
                        <td class="text-right mono fw-700 {{ $allocation->payment->isReversed() ? 'text-muted text-decoration-line-through' : '' }}">
                            {{ number_format((float) $allocation->amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="fw-700">Fee Payment</td>
                        <td class="text-muted">—</td>
                        <td class="text-muted">—</td>
                        <td class="text-right mono fw-700">{{ number_format((float) $payment->amount, 2) }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="amount-section">
            <div class="amount-row">
                <span class="amount-label">Amount Received</span>
                <span class="amount-value">KES {{ number_format((float) $payment->amount, 2) }}</span>
            </div>
            <div class="amount-words">{{ $amountInWords }}</div>
        </div>

        @php
            // Show the balance of the primary fee after this payment (only meaningful
            // for a valid, non-reversed receipt).
            $primaryAssignment = $payment->studentFeeAssignment;
        @endphp
        @if($primaryAssignment && ! $payment->isReversed())
            <div class="balance-grid">
                <div class="balance-card">
                    <div class="balance-label">This Fee Total</div>
                    <div class="balance-value">{{ number_format((float) $primaryAssignment->final_amount, 2) }}</div>
                </div>
                <div class="balance-card">
                    <div class="balance-label">Paid To Date</div>
                    <div class="balance-value paid">{{ number_format((float) $primaryAssignment->paid_amount, 2) }}</div>
                </div>
                <div class="balance-card">
                    <div class="balance-label">Remaining Balance</div>
                    <div class="balance-value due">{{ number_format(max(0, (float) $primaryAssignment->final_amount - (float) $primaryAssignment->paid_amount), 2) }}</div>
                </div>
            </div>
        @endif

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Received By: {{ $payment->collectedBy?->full_name ?? 'Finance Office' }}</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Parent / Guardian</div>
            </div>
        </div>

        <div class="receipt-footer">
            <div class="footer-note">
                Printed on {{ now()->format('d M Y, h:i A') }} — This is a computer-generated receipt.
                For inquiries, contact the school finance office.
            </div>
        </div>
    </div>

    <div class="no-print">
        <button class="primary" onclick="window.print()">🖨️ Print Receipt</button>
        <button onclick="window.location.href='{{ route('fee-management.show', $student->student_id) }}'">View Student Fees</button>
    </div>

    <script>
        // Auto-open the print dialog once the receipt renders — the bursar came
        // here straight from recording the payment.
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 400);
        });
    </script>
</body>
</html>
