<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Progress Report — {{ $data['student']->full_name }}</title>
    <style>
        @page { size: A4; margin: 10mm; }
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            color: #1f2937;
            background: #eef2f7;
            margin: 0;
            padding: 24px;
        }
        .print-bar { max-width: 800px; margin: 0 auto 16px; text-align: right; }
        .print-bar button {
            background: #dc2626; border: none; color: #fff; font-size: 15px;
            padding: 10px 22px; border-radius: 8px; cursor: pointer;
        }
        @media print { .print-bar { display: none; } body { background: none; padding: 0; } }

        .report-card {
            background: #fff; max-width: 800px; margin: 0 auto;
            padding: 28px 32px; border: 1px solid #cbd5e1; border-top: 6px solid #1a3c6e;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.08);
        }

        .school-name { font-size: 22px; font-weight: 800; letter-spacing: 1.5px; color: #1a3c6e; }
        .school-sub { font-size: 11.5px; color: #64748b; margin-top: 2px; }
        .school-motto { font-size: 11px; font-style: italic; color: #b45309; margin-top: 2px; }
        .report-title {
            display: inline-block; margin-top: 10px; padding: 4px 18px;
            background: #1a3c6e; color: #fff; font-size: 12.5px; font-weight: 700; letter-spacing: 1px;
            border-radius: 3px;
        }

        table.w-100 { width: 100%; border-collapse: collapse; }

        .info-table { margin-top: 14px; font-size: 12.5px; }
        .info-table td { padding: 3px 6px; vertical-align: top; }
        .info-table .lbl { width: 110px; color: #64748b; white-space: nowrap; }
        .info-table .val { border-bottom: 1px dotted #cbd5e1; width: 200px; }

        .results-table { margin-top: 12px; font-size: 12px; border: 1px solid #94a3b8; }
        .results-table th {
            background: #1a3c6e; color: #fff; padding: 6px 4px; font-weight: 600;
            border: 1px solid #1a3c6e;
        }
        .results-table td { padding: 5px 4px; border: 1px solid #cbd5e1; vertical-align: middle; }
        .level-cell { line-height: 1.05; }
        .level-desc { font-size: 7.5px; color: #64748b; font-weight: 400; }
        .remark-cell { font-style: italic; color: #475569; }
        .empty-row { padding: 18px !important; color: #94a3b8; }

        .summary-table { margin-top: 12px; background: #f1f5f9; border: 1px solid #cbd5e1; }
        .summary-table td { padding: 8px 12px; border-right: 1px solid #e2e8f0; width: 25%; }
        .sum-lbl { display: block; font-size: 9px; letter-spacing: 0.5px; color: #64748b; font-weight: 700; }
        .sum-val { font-size: 14.5px; font-weight: 800; }

        .level-key { margin-top: 8px; font-size: 9.5px; color: #64748b; }

        .remarks-table { margin-top: 12px; }
        .remarks-table td { padding: 6px 4px; vertical-align: bottom; }
        .rem-lbl { width: 150px; font-size: 10.5px; font-weight: 700; color: #334155; white-space: nowrap; }
        .rem-val { border-bottom: 1px solid #94a3b8; font-style: italic; font-size: 12px; padding-bottom: 2px; }

        .sign-table { margin-top: 46px; }
        .sign-line { border-bottom: 1px solid #334155; width: 31%; padding-bottom: 2px; }
        .sign-labels td { font-size: 10.5px; font-weight: 700; padding-top: 4px; text-align: center; }
        .sign-labels span { font-weight: 400; color: #64748b; font-size: 9.5px; }

        .card-footer-note {
            margin-top: 18px; text-align: center; font-size: 9px; color: #94a3b8;
            border-top: 1px solid #e2e8f0; padding-top: 6px;
        }
    </style>
</head>
<body>
    <div class="print-bar">
        <button onclick="window.print()">Print this report card</button>
    </div>

    @include('exam_reports.templates.card', ['data' => $data])
</body>
</html>
