@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-chart-bar text-info mr-2"></i>Budget vs Actual ({{ $activeYear->name }})</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-print mr-1"></i> Print Report
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm rounded-lg">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr class="bg-light text-muted small text-uppercase">
                                        <th class="pl-4 border-0">Category</th>
                                        <th class="border-0">Type</th>
                                        <th class="border-0 text-right">Budgeted</th>
                                        <th class="border-0 text-right">Actual Spent/Recv</th>
                                        <th class="border-0 text-right">Variance</th>
                                        <th class="border-0 text-center pr-4">Utilization</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalBudgeted = 0;
                                        $totalActual = 0;
                                    @endphp
                                    @foreach($comparison as $row)
                                        @php
                                            $totalBudgeted += ($row->type == 'expense' ? $row->budgeted : 0);
                                            $totalActual += ($row->type == 'expense' ? $row->actual : 0);
                                        @endphp
                                        <tr>
                                            <td class="pl-4 py-3 font-weight-bold">{{ $row->category }}</td>
                                            <td class="py-3">
                                                <span class="badge badge-{{ $row->type == 'income' ? 'success' : 'danger' }}-light text-{{ $row->type == 'income' ? 'success' : 'danger' }} px-3 py-1 rounded-pill">
                                                    {{ ucfirst($row->type) }}
                                                </span>
                                            </td>
                                            <td class="py-3 text-right">KES {{ number_format($row->budgeted, 0) }}</td>
                                            <td class="py-3 text-right">KES {{ number_format($row->actual, 0) }}</td>
                                            <td class="py-3 text-right font-weight-bold {{ $row->variance < 0 && $row->type == 'expense' ? 'text-danger' : 'text-muted' }}">
                                                KES {{ number_format($row->variance, 0) }}
                                            </td>
                                            <td class="py-3 text-center pr-4" style="width: 200px;">
                                                <div class="progress rounded-pill" style="height: 10px;">
                                                    @php
                                                        $color = 'bg-info';
                                                        if ($row->type == 'expense') {
                                                            if ($row->percentage >= 100) $color = 'bg-danger';
                                                            elseif ($row->percentage >= $row->threshold) $color = 'bg-warning';
                                                        }
                                                    @endphp
                                                    <div class="progress-bar {{ $color }}" role="progressbar" style="width: {{ min($row->percentage, 100) }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ round($row->percentage, 1) }}%</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="2" class="pl-4">TOTAL EXPENSES</td>
                                        <td class="text-right">KES {{ number_format($totalBudgeted, 0) }}</td>
                                        <td class="text-right">KES {{ number_format($totalActual, 0) }}</td>
                                        <td class="text-right">KES {{ number_format($totalBudgeted - $totalActual, 0) }}</td>
                                        <td class="text-center pr-4">
                                            {{ $totalBudgeted > 0 ? round(($totalActual / $totalBudgeted) * 100, 1) : 0 }}% Overall
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .badge-success-light { background-color: #dcfce7; }
        .badge-danger-light { background-color: #fee2e2; }
    </style>
@endsection
