<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Cards — {{ $reports->first()['exam']->name ?? '' }}</title>
    <style>
        @page { size: A4; margin: 10mm; }
        body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; color: #1f2937; margin: 0; }

        .report-card {
            page-break-after: always;
            padding: 18px 22px;
            border: 1px solid #cbd5e1;
            border-top: 6px solid #1a3c6e;
        }
        .report-card:last-child { page-break-after: auto; }

        .school-name { font-size: 20px; font-weight: 800; letter-spacing: 1.5px; color: #1a3c6e; text-align: center; }
        .school-sub { font-size: 10.5px; color: #64748b; margin-top: 2px; text-align: center; }
        .school-motto { font-size: 10px; font-style: italic; color: #b45309; margin-top: 2px; text-align: center; }
        .report-title {
            display: inline-block; margin-top: 8px; padding: 3px 16px;
            background: #1a3c6e; color: #fff; font-size: 11.5px; font-weight: 700; letter-spacing: 1px;
        }

        table.w-100 { width: 100%; border-collapse: collapse; }

        .info-table { margin-top: 12px; font-size: 11.5px; }
        .info-table td { padding: 2.5px 6px; vertical-align: top; }
        .info-table .lbl { width: 105px; color: #64748b; white-space: nowrap; }
        .info-table .val { border-bottom: 1px dotted #cbd5e1; width: 200px; }

        .results-table { margin-top: 10px; font-size: 11px; border: 1px solid #94a3b8; }
        .results-table th { background: #1a3c6e; color: #fff; padding: 5px 4px; font-weight: 600; border: 1px solid #1a3c6e; }
        .results-table td { padding: 4px 4px; border: 1px solid #cbd5e1; vertical-align: middle; }
        .level-cell { line-height: 1.05; }
        .level-desc { font-size: 7px; color: #64748b; font-weight: 400; display: block; }
        .remark-cell { font-style: italic; color: #475569; }
        .empty-row { padding: 16px !important; color: #94a3b8; text-align: center; }

        .summary-table { margin-top: 10px; background: #f1f5f9; border: 1px solid #cbd5e1; }
        .summary-table td { padding: 7px 10px; border-right: 1px solid #e2e8f0; width: 25%; }
        .sum-lbl { display: block; font-size: 8.5px; letter-spacing: 0.5px; color: #64748b; font-weight: 700; }
        .sum-val { font-size: 13.5px; font-weight: 800; }

        .level-key { margin-top: 7px; font-size: 9px; color: #64748b; }

        .remarks-table { margin-top: 10px; }
        .remarks-table td { padding: 5px 4px; vertical-align: bottom; }
        .rem-lbl { width: 145px; font-size: 10px; font-weight: 700; color: #334155; white-space: nowrap; }
        .rem-val { border-bottom: 1px solid #94a3b8; font-style: italic; font-size: 11px; padding-bottom: 2px; }

        .sign-table { margin-top: 40px; }
        .sign-line { border-bottom: 1px solid #334155; width: 31%; padding-bottom: 2px; }
        .sign-labels td { font-size: 10px; font-weight: 700; padding-top: 4px; text-align: center; }
        .sign-labels span { font-weight: 400; color: #64748b; font-size: 9px; }

        .card-footer-note {
            margin-top: 14px; text-align: center; font-size: 8.5px; color: #94a3b8;
            border-top: 1px solid #e2e8f0; padding-top: 5px;
        }
    </style>
</head>
<body>
@foreach($reports as $data)
    @include('exam_reports.templates.card', ['data' => $data])
@endforeach
</body>
</html>
