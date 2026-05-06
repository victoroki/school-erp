<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Revenue Forecast Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #1e293b; font-size: 9pt; line-height: 1.4; padding: 15mm; }

        .report-header { text-align: center; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 2px solid #4f46e5; }
        .school-name { font-size: 18pt; font-weight: 900; color: #4f46e5; }
        .report-title { font-size: 11pt; font-weight: 700; color: #475569; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.06em; }
        .report-date { font-size: 8pt; color: #94a3b8; margin-top: 4px; }

        .metrics { margin-bottom: 16px; }
        .metric-row { display: flex; gap: 10px; margin-bottom: 10px; }
        .metric-card { flex: 1; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; text-align: center; }
        .metric-label { font-size: 7pt; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; }
        .metric-value { font-size: 12pt; font-weight: 900; font-family: 'Consolas', monospace; margin-top: 2px; }
        .metric-value.total { color: #1e293b; }
        .metric-value.discount { color: #f59e0b; }
        .metric-value.expected { color: #4f46e5; }
        .metric-value.collected { color: #10b981; }

        .section-title { font-size: 10pt; font-weight: 800; color: #1e293b; margin: 16px 0 10px; padding-bottom: 6px; border-bottom: 1px solid #e2e8f0; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        thead { background: #f8fafc; }
        th { padding: 6px 8px; font-size: 7pt; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; text-align: left; border-bottom: 1px solid #e2e8f0; }
        td { padding: 6px 8px; border-bottom: 1px solid #f1f5f9; font-size: 8.5pt; }
        .text-right { text-align: right; }
        .mono { font-family: 'Consolas', monospace; }
        .fw-700 { font-weight: 700; }
        .text-green { color: #10b981; }
        .text-muted { color: #94a3b8; }

        .report-footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 7pt; color: #94a3b8; font-style: italic; }

        @page { margin: 10mm; size: A4 portrait; }
    </style>
</head>
<body>
    <div class="report-header">
        <div class="school-name">{{ config('app.name') }}</div>
        <div class="report-title">Revenue Forecast Report</div>
        <div class="report-date">Generated on {{ date('d F Y, h:i A') }}</div>
    </div>

    <div class="metrics">
        <div class="metric-row">
            <div class="metric-card">
                <div class="metric-label">Gross Revenue</div>
                <div class="metric-value total">{{ number_format($totalOriginal, 2) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Discounts</div>
                <div class="metric-value discount">{{ number_format($totalDiscounts, 2) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Net Expected</div>
                <div class="metric-value expected">{{ number_format($totalExpected, 2) }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Collected</div>
                <div class="metric-value collected">{{ number_format($totalCollected, 2) }}</div>
            </div>
        </div>
        <div class="metric-row">
            <div class="metric-card" style="background: #f8fafc;">
                <div class="metric-label">Collection Rate</div>
                <div class="metric-value" style="color: #4f46e5; font-size: 14pt;">{{ $collectionRate }}%</div>
            </div>
        </div>
    </div>

    <div class="section-title">Revenue by Class</div>
    <table>
        <thead>
            <tr><th>Class</th><th class="text-right">Expected Revenue</th></tr>
        </thead>
        <tbody>
            @foreach($revenueByClass as $row)
                <tr>
                    <td class="fw-700">{{ $row->class_name }}</td>
                    <td class="text-right mono">{{ number_format($row->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Revenue by Fee Category</div>
    <table>
        <thead>
            <tr><th>Category</th><th class="text-right">Expected Revenue</th></tr>
        </thead>
        <tbody>
            @foreach($revenueByCategory as $row)
                <tr>
                    <td class="fw-700">{{ $row->category_name }}</td>
                    <td class="text-right mono">{{ number_format($row->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="report-footer">
        System-generated report from {{ config('app.name') }} School ERP.
    </div>
</body>
</html>
