<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Discount Summary Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #1e293b; font-size: 9pt; line-height: 1.4; padding: 15mm; }

        .report-header { text-align: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #4f46e5; }
        .school-name { font-size: 18pt; font-weight: 900; color: #4f46e5; }
        .report-title { font-size: 11pt; font-weight: 700; color: #475569; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.06em; }
        .report-date { font-size: 8pt; color: #94a3b8; margin-top: 4px; }

        .summary-box { padding: 12px; border: 1px solid #e2e8f0; border-radius: 4px; text-align: center; margin-bottom: 16px; background: #fffbeb; }
        .summary-label { font-size: 7pt; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; }
        .summary-value { font-size: 14pt; font-weight: 900; color: #f59e0b; font-family: 'Consolas', monospace; margin-top: 2px; }

        .section-title { font-size: 10pt; font-weight: 800; color: #1e293b; margin: 16px 0 10px; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        thead { background: #f8fafc; }
        th { padding: 6px 8px; font-size: 7pt; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; text-align: left; border-bottom: 1px solid #e2e8f0; }
        td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; font-size: 8.5pt; }
        .text-right { text-align: right; } .text-center { text-align: center; }
        .mono { font-family: 'Consolas', monospace; }
        .fw-700 { font-weight: 700; }
        .text-green { color: #10b981; } .text-red { color: #f43f5e; } .text-muted { color: #94a3b8; }
        .text-sm { font-size: 7.5pt; }

        .scheme-table th, .scheme-table td { font-size: 8pt; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 7pt; font-weight: 700; text-transform: capitalize; }
        .badge-amber { background: #fffbeb; color: #d97706; }

        .report-footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 7pt; color: #94a3b8; font-style: italic; }

        @page { margin: 10mm; size: A4 portrait; }
    </style>
</head>
<body>
    <div class="report-header">
        <div class="school-name">{{ config('app.name') }}</div>
        <div class="report-title">Discount Summary Report</div>
        <div class="report-date">Generated on {{ date('d F Y, h:i A') }}</div>
    </div>

    <div class="summary-box">
        <div class="summary-label">Total Revenue Forgone (Discounts)</div>
        <div class="summary-value">KSh {{ number_format($totalDiscounts, 2) }}</div>
    </div>

    @if($discountSchemes->count() > 0)
    <div class="section-title">Breakdown by Scheme</div>
    <table class="scheme-table">
        <thead>
            <tr>
                <th>Scheme</th>
                <th>Eligibility</th>
                <th class="text-center">Students</th>
                <th class="text-right">Total Discount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($discountSchemes as $scheme)
                <tr>
                    <td class="fw-700">{{ $scheme->scheme_name }}</td>
                    <td><span class="badge badge-amber">{{ ucfirst(str_replace('_', ' ', $scheme->criteria)) }}</span></td>
                    <td class="text-center">{{ $scheme->student_count }}</td>
                    <td class="text-right mono text-red fw-700">{{ number_format($scheme->total_discount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="section-title">Student Discount Details</div>
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Fee Type</th>
                <th>Scheme</th>
                <th class="text-right">Original</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Final</th>
            </tr>
        </thead>
        <tbody>
            @foreach($discounts as $discount)
                <tr>
                    <td class="fw-700">{{ $discount->student->full_name ?? 'N/A' }}</td>
                    <td>{{ $discount->feeStructure->category->name ?? 'N/A' }}</td>
                    <td>{{ $discount->discount->name ?? 'Manual' }}</td>
                    <td class="text-right mono">{{ number_format($discount->amount, 2) }}</td>
                    <td class="text-right mono text-red fw-700">-{{ number_format($discount->discount_amount, 2) }}</td>
                    <td class="text-right mono fw-700">{{ number_format($discount->final_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="report-footer">
        System-generated report from {{ config('app.name') }} School ERP.
    </div>
</body>
</html>
