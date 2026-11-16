@extends('layouts.app')

@section('content')
    <style>
        /* Scoped to .payroll-review. The global stylesheet forces
           .card-header to a transparent background, so the coloured header
           bars this page previously used never rendered — a `bg-success` on a
           card-header is silently discarded. Styling here keeps the page
           self-contained instead of editing a stylesheet other tests assert on. */
        .payroll-review {
            --pr-ink:        oklch(0.206 0.010 264);
            --pr-muted:      oklch(0.551 0.014 264);
            --pr-line:       oklch(0.928 0.008 264);
            --pr-surface:    oklch(1 0 0);
            --pr-canvas:     oklch(0.977 0.003 264);
            --pr-accent:     oklch(0.511 0.230 272);
            --pr-good:       oklch(0.596 0.145 163);
            --pr-good-soft:  oklch(0.955 0.038 163);
            --pr-warn:       oklch(0.666 0.161 58);
            --pr-warn-soft:  oklch(0.973 0.045 92);
            --pr-bad:        oklch(0.577 0.208 27);
            --pr-bad-soft:   oklch(0.966 0.026 22);
            --pr-radius:     12px;
        }

        .payroll-review .pr-page-head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .payroll-review .pr-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--pr-muted);
        }

        .payroll-review .pr-title {
            margin: .2rem 0 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--pr-ink);
            line-height: 1.2;
        }

        .payroll-review .pr-period {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            margin-top: .55rem;
            padding: .3rem .7rem;
            border: 1px solid var(--pr-line);
            border-radius: 999px;
            background: var(--pr-surface);
            font-size: .82rem;
            font-weight: 600;
            color: var(--pr-ink);
        }

        /* Four-up summary. Replaces small-box, whose background colours fight
           the sidebar theme and shrink the figure below legibility on narrow
           screens. */
        .payroll-review .pr-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr));
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .payroll-review .pr-metric {
            padding: 1rem 1.125rem;
            border: 1px solid var(--pr-line);
            border-radius: var(--pr-radius);
            background: var(--pr-surface);
            box-shadow: 0 1px 3px oklch(0 0 0 / .05);
        }

        .payroll-review .pr-metric-label {
            display: flex;
            align-items: center;
            gap: .4rem;
            font-size: .74rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--pr-muted);
        }

        .payroll-review .pr-metric-value {
            margin-top: .35rem;
            font-size: 1.5rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            line-height: 1.15;
            color: var(--pr-ink);
            word-break: break-word;
        }

        .payroll-review .pr-metric-note {
            margin-top: .2rem;
            font-size: .78rem;
            color: var(--pr-muted);
        }

        .payroll-review .pr-metric--gross  .pr-metric-value { color: var(--pr-accent); }
        .payroll-review .pr-metric--net    .pr-metric-value { color: var(--pr-good); }
        .payroll-review .pr-metric--deduct .pr-metric-value { color: var(--pr-bad); }

        /* The statutory basis. Payroll is a place a school gets asked where a
           number came from, so the rates sit next to the figures. */
        .payroll-review .pr-basis {
            margin-bottom: 1.25rem;
            border: 1px solid var(--pr-line);
            border-left: 3px solid var(--pr-accent);
            border-radius: var(--pr-radius);
            background: var(--pr-surface);
        }

        .payroll-review .pr-basis-head {
            padding: .85rem 1.125rem;
            border-bottom: 1px solid var(--pr-line);
            font-size: .74rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--pr-muted);
        }

        .payroll-review .pr-basis-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(17rem, 1fr));
            gap: .85rem 1.5rem;
            margin: 0;
            padding: 1rem 1.125rem;
            list-style: none;
        }

        .payroll-review .pr-basis-term {
            font-weight: 700;
            color: var(--pr-ink);
        }

        .payroll-review .pr-basis-desc {
            font-size: .84rem;
            color: var(--pr-muted);
        }

        .payroll-review .pr-basis-note {
            margin: 0;
            padding: .75rem 1.125rem;
            border-top: 1px dashed var(--pr-line);
            background: var(--pr-canvas);
            font-size: .82rem;
            color: var(--pr-muted);
        }

        .payroll-review .pr-table-card {
            border: 1px solid var(--pr-line);
            border-radius: var(--pr-radius);
            background: var(--pr-surface);
            overflow: hidden;
        }

        .payroll-review .pr-table-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: 1rem 1.125rem;
            border-bottom: 1px solid var(--pr-line);
        }

        .payroll-review .pr-table-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            color: var(--pr-ink);
        }

        .payroll-review .pr-table-sub {
            margin: .15rem 0 0;
            font-size: .8rem;
            color: var(--pr-muted);
        }

        .payroll-review .pr-table {
            margin: 0;
            font-size: .875rem;
        }

        .payroll-review .pr-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: var(--pr-canvas);
            border-bottom: 1px solid var(--pr-line);
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--pr-muted);
            white-space: nowrap;
            vertical-align: bottom;
        }

        /* Deductions are one visual group, so the eye can find them without
           counting columns. */
        .payroll-review .pr-group-start { border-left: 1px solid var(--pr-line); }
        .payroll-review .pr-group-end   { border-right: 1px solid var(--pr-line); }

        .payroll-review .pr-table tbody td {
            vertical-align: middle;
            font-variant-numeric: tabular-nums;
        }

        .payroll-review .pr-name { font-weight: 600; color: var(--pr-ink); }
        .payroll-review .pr-sub  { font-size: .78rem; color: var(--pr-muted); }

        .payroll-review .pr-gross { font-weight: 700; color: var(--pr-ink); }
        .payroll-review .pr-deduct { color: var(--pr-bad); }
        .payroll-review .pr-net { font-weight: 700; color: var(--pr-good); }

        .payroll-review .pr-total-row th,
        .payroll-review .pr-total-row td {
            background: var(--pr-canvas);
            border-top: 2px solid var(--pr-line);
            font-weight: 700;
            color: var(--pr-ink);
            font-variant-numeric: tabular-nums;
        }

        .payroll-review .pr-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .6rem;
            padding: 1rem 1.125rem;
            border-top: 1px solid var(--pr-line);
        }

        .payroll-review .pr-actions-note {
            flex: 1 1 18rem;
            font-size: .82rem;
            color: var(--pr-muted);
        }

        .payroll-review .pr-empty {
            padding: 3.5rem 1.5rem;
            text-align: center;
        }

        .payroll-review .pr-empty-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 999px;
            background: var(--pr-canvas);
            color: var(--pr-muted);
            font-size: 1.4rem;
        }

        @media print {
            .payroll-review .pr-actions,
            .payroll-review .pr-no-print { display: none !important; }
            .payroll-review .pr-table thead th { position: static; }
        }
    </style>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-calculator text-success"></i> Payroll Calculation</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payroll-processing.index') }}">Payroll</a></li>
                        <li class="breadcrumb-item active">Calculation</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('flash::message')

            <div class="payroll-review">
                <div class="pr-page-head pr-no-print">
                    <div>
                        <span class="pr-eyebrow">
                            <i class="fas fa-file-invoice-dollar"></i> Statutory payroll preview
                        </span>
                        <h2 class="pr-title">{{ $period->format('F Y') }}</h2>
                        <span class="pr-period">
                            <i class="fas fa-calendar-alt"></i>
                            {{ $period->format('d M Y') }} &ndash; {{ $period->copy()->endOfMonth()->format('d M Y') }}
                        </span>
                    </div>
                    <div>
                        <a href="{{ route('payroll-processing.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Change period
                        </a>
                    </div>
                </div>

                <div class="pr-metrics">
                    <div class="pr-metric">
                        <div class="pr-metric-label"><i class="fas fa-users"></i> Staff</div>
                        <div class="pr-metric-value">{{ $totals['staff'] }}</div>
                        <div class="pr-metric-note">Active employees on this run</div>
                    </div>

                    <div class="pr-metric pr-metric--gross">
                        <div class="pr-metric-label"><i class="fas fa-coins"></i> Total gross</div>
                        <div class="pr-metric-value">{{ \App\Support\Money::format($totals['gross_salary']) }}</div>
                        <div class="pr-metric-note">Basic plus allowances</div>
                    </div>

                    <div class="pr-metric pr-metric--deduct">
                        <div class="pr-metric-label"><i class="fas fa-minus-circle"></i> Deductions</div>
                        <div class="pr-metric-value">{{ \App\Support\Money::format($totals['total_deductions']) }}</div>
                        <div class="pr-metric-note">
                            {{ \App\Support\Money::format($totals['shif_employee'] + $totals['nssf_employee'] + $totals['paye']) }} statutory
                        </div>
                    </div>

                    <div class="pr-metric pr-metric--net">
                        <div class="pr-metric-label"><i class="fas fa-hand-holding-usd"></i> Net pay</div>
                        <div class="pr-metric-value">{{ \App\Support\Money::format($totals['net_salary']) }}</div>
                        <div class="pr-metric-note">After all deductions</div>
                    </div>
                </div>

                {{-- Employer contributions are the school's cost, not a cut to the
                     employee. The old breakdown had no column for them at all. --}}
                <div class="pr-basis">
                    <div class="pr-basis-head">Statutory basis in force</div>
                    <ul class="pr-basis-list">
                        @foreach($rates as $term => $description)
                            <li>
                                <span class="pr-basis-term">{{ $term }}</span>
                                <div class="pr-basis-desc">{{ $description }}</div>
                            </li>
                        @endforeach
                    </ul>
                    <p class="pr-basis-note">
                        Employer contributions on top of salaries:
                        <strong>{{ \App\Support\Money::format($totals['statutory_employer']) }}</strong> per month
                        (SHIF employer {{ \App\Support\Money::format($totals['shif_employer']) }},
                        NSSF employer {{ \App\Support\Money::format($totals['nssf_employer']) }}).
                        That is the cost of the payroll, on top of gross &mdash; it is not taken from the employee.
                    </p>
                </div>

                <div class="pr-table-card">
                    <div class="pr-table-head">
                        <div>
                            <h3 class="pr-table-title">Payslip breakdown</h3>
                            <p class="pr-table-sub">One row per active employee</p>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>

                    @if(empty($payrollData))
                        <div class="pr-empty">
                            <span class="pr-empty-icon"><i class="fas fa-user-slash"></i></span>
                            <h4 class="mt-3 mb-1">No active staff to pay</h4>
                            <p class="text-muted mb-3">
                                Nothing was calculated because no employee has an active employment status.
                            </p>
                            <a href="{{ route('staff.index') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-users-cog"></i> Review staff
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover pr-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th class="text-right">Basic</th>
                                        <th class="text-right">Allowances</th>
                                        <th class="text-right">Gross</th>
                                        <th class="text-right pr-group-start">PAYE</th>
                                        <th class="text-right">SHIF</th>
                                        <th class="text-right">NSSF</th>
                                        <th class="text-right">Other</th>
                                        <th class="text-right pr-group-end">Total deductions</th>
                                        <th class="text-right">Net pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payrollData as $row)
                                        <tr>
                                            <td>
                                                <div class="pr-name">{{ $row['staff_name'] }}</div>
                                                <div class="pr-sub">{{ $row['employee_number'] ?? 'No ID' }}</div>
                                            </td>
                                            <td class="text-right">{{ \App\Support\Money::number($row['basic_salary']) }}</td>
                                            <td class="text-right">{{ \App\Support\Money::number($row['allowances']) }}</td>
                                            <td class="text-right pr-gross">{{ \App\Support\Money::number($row['gross_salary']) }}</td>
                                            <td class="text-right pr-deduct pr-group-start">{{ \App\Support\Money::number($row['paye']) }}</td>
                                            <td class="text-right pr-deduct">{{ \App\Support\Money::number($row['shif_employee']) }}</td>
                                            <td class="text-right pr-deduct">{{ \App\Support\Money::number($row['nssf_employee']) }}</td>
                                            <td class="text-right pr-deduct">{{ \App\Support\Money::number($row['other_deductions']) }}</td>
                                            <td class="text-right pr-deduct pr-group-end"><strong>{{ \App\Support\Money::number($row['total_deductions']) }}</strong></td>
                                            <td class="text-right pr-net"><strong>{{ \App\Support\Money::number($row['net_salary']) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="pr-total-row">
                                        <th scope="row">Totals ({{ $totals['staff'] }} staff)</th>
                                        <th class="text-right" scope="col">{{ \App\Support\Money::number($totals['basic_salary']) }}</th>
                                        <th class="text-right" scope="col">{{ \App\Support\Money::number($totals['allowances']) }}</th>
                                        <th class="text-right" scope="col">{{ \App\Support\Money::number($totals['gross_salary']) }}</th>
                                        <th class="text-right pr-group-start" scope="col">{{ \App\Support\Money::number($totals['paye']) }}</th>
                                        <th class="text-right" scope="col">{{ \App\Support\Money::number($totals['shif_employee']) }}</th>
                                        <th class="text-right" scope="col">{{ \App\Support\Money::number($totals['nssf_employee']) }}</th>
                                        <th class="text-right" scope="col">{{ \App\Support\Money::number($totals['other_deductions']) }}</th>
                                        <th class="text-right pr-group-end" scope="col">{{ \App\Support\Money::number($totals['total_deductions']) }}</th>
                                        <th class="text-right" scope="col">{{ \App\Support\Money::number($totals['net_salary']) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif

                    <div class="pr-actions">
                        <a href="{{ route('payroll-processing.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-undo"></i> Start over
                        </a>
                        <span class="pr-actions-note">
                            <i class="fas fa-info-circle"></i>
                            This is a calculation only &mdash; nothing is written to the payroll ledger.
                            Approve and process payslips from the
                            <a href="{{ route('payrolls.index') }}">HR payroll screen</a>.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
