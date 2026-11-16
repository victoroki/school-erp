@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-chart-line text-primary"></i> Attendance Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.reports.headcount') }}">Reports</a></li>
                        <li class="breadcrumb-item active">Attendance</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Month Filter</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('hr.reports.attendance') }}" class="form-inline">
                        <div class="form-group mr-2">
                            <label class="mr-2">Month</label>
                            <select name="month" class="form-control">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label class="mr-2">Year</label>
                            <select name="year" class="form-control">
                                @for($y = date('Y') - 2; $y <= date('Y'); $y++)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Apply
                        </button>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Status Summary — {{ date('F', mktime(0, 0, 0, $month, 1)) }} {{ $year }}</h3>
                        </div>
                        <div class="card-body p-0">
                            @if($summary->isEmpty())
                                <p class="text-center text-muted py-4 mb-0">No attendance records for this month.</p>
                            @else
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th class="px-4">Status</th>
                                            <th class="text-right pr-4">Count</th>
                                            <th class="text-right pr-4">Share</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $total = $summary->sum(); @endphp
                                        @foreach($summary as $status => $count)
                                            <tr>
                                                <td class="px-4">
                                                    @php
                                                        $badges = [
                                                            'present' => 'badge-success',
                                                            'absent' => 'badge-danger',
                                                            'late' => 'badge-warning',
                                                            'half_day' => 'badge-info',
                                                            'on_leave' => 'badge-secondary',
                                                        ];
                                                    @endphp
                                                    <span class="badge {{ $badges[$status] ?? 'badge-secondary' }}">
                                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                    </span>
                                                </td>
                                                <td class="text-right pr-4">{{ number_format($count) }}</td>
                                                <td class="text-right pr-4">{{ $total > 0 ? round(($count / $total) * 100, 1) : 0 }}%</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Daily Trend</h3>
                        </div>
                        <div class="card-body p-0">
                            @if($dailyTrend->isEmpty())
                                <p class="text-center text-muted py-4 mb-0">No daily data for this month.</p>
                            @else
                                @php
                                    $days = $dailyTrend->groupBy('day');
                                    $maxCount = $dailyTrend->max('count') ?: 1;
                                    $colors = [
                                        'present' => '#28a745',
                                        'absent' => '#dc3545',
                                        'late' => '#ffc107',
                                        'half_day' => '#17a2b8',
                                        'on_leave' => '#6c757d',
                                    ];
                                @endphp
                                <div class="p-4">
                                    @foreach($days as $day => $rows)
                                        <div class="d-flex align-items-center mb-2">
                                            <div style="width: 34px;" class="text-muted font-weight-bold">{{ $day }}</div>
                                            <div class="flex-grow-1">
                                                @foreach($rows as $row)
                                                    @php
                                                        $color = $colors[$row->status] ?? '#6c757d';
                                                        $pct = round(($row->count / $maxCount) * 100);
                                                    @endphp
                                                    <div class="d-flex align-items-center mb-1">
                                                        <div class="flex-grow-1 mr-2" style="height: 12px; background: #f1f5f9; border-radius: 6px; overflow: hidden;">
                                                            <div style="height: 100%; width: {{ $pct }}%; background: {{ $color }};"></div>
                                                        </div>
                                                        <small class="text-muted" style="width: 110px;">{{ ucfirst(str_replace('_', ' ', $row->status)) }}: {{ $row->count }}</small>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
