@extends('layouts.app')

@push('page_css')
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-bg: #f8fafc;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            --font-main: 'Outfit', sans-serif;
        }

        body { font-family: var(--font-main); background-color: var(--primary-bg); }

        .dashboard-header { margin-bottom: 2rem; }
        .dashboard-title { font-weight: 700; color: #1e293b; font-size: 1.875rem; margin-bottom: 0.25rem; }
        .dashboard-subtitle { color: #64748b; font-size: 0.95rem; }

        /* Summary Cards */
        .stat-card {
            background: #fff;
            border: none;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            transition: transform 0.2s;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover { transform: translateY(-4px); }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .stat-value { font-size: 1.75rem; font-weight: 700; color: #1e293b; line-height: 1; margin-bottom: 0.5rem; }
        .stat-label { font-size: 0.875rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.025em; }
        .stat-badge {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        /* Table Card */
        .workload-card {
            background: #fff;
            border: none;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            margin-top: 1rem;
        }
        .workload-card .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .workload-card .card-title { font-weight: 700; color: #1e293b; font-size: 1.125rem; margin: 0; }
        
        .custom-table { width: 100%; margin: 0; }
        .custom-table thead th {
            background: #f8fafc;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .custom-table tbody td {
            padding: 0.75rem 1.5rem;
            vertical-align: middle;
            border-top: 1px solid #f1f5f9;
        }

        /* Teacher Info */
        .teacher-info { display: flex; align-items: center; gap: 0.75rem; }
        .teacher-avatar {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            object-fit: cover;
            background: #f1f5f9;
        }
        .teacher-name { font-weight: 600; color: #1e293b; font-size: 0.95rem; display: block; }
        .teacher-title { font-size: 0.75rem; color: #94a3b8; font-weight: 500; }

        /* Progress Bar */
        .capacity-wrapper { min-width: 140px; }
        .custom-progress {
            height: 6px;
            background: #f1f5f9;
            border-radius: 9999px;
            margin-bottom: 0.5rem;
            overflow: hidden;
        }
        .custom-progress-bar { height: 100%; border-radius: 9999px; transition: width 0.4s ease; }
        .capacity-text { font-size: 0.75rem; font-weight: 600; color: #64748b; }

        /* Status Badges */
        .status-pill {
            padding: 0.375rem 0.875rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            display: inline-block;
        }
        .status-optimum { background: #dcfce7; color: #15803d; }
        .status-overloaded { background: #fee2e2; color: #b91c1c; }
        .status-underloaded { background: #f1f5f9; color: #475569; }

        /* Filter Controls */
        .filter-select {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }
        .filter-select:hover { border-color: #cbd5e1; }
        
        /* Pagination */
        .custom-pagination .page-item .page-link {
            border: none;
            margin: 0 0.25rem;
            border-radius: 8px !important;
            font-weight: 600;
            color: #64748b;
            padding: 8px 16px;
        }
        .custom-pagination .page-item.active .page-link {
            background: #0369a1;
            color: #fff;
            box-shadow: 0 4px 6px -1px rgba(3, 105, 161, 0.4);
        }

        .btn-premium {
            border-radius: 10px;
            padding: 0.625rem 1.25rem;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        .btn-assign { background: #0369a1; color: white; }
        .btn-assign:hover { background: #075985; color: white; transform: translateY(-1px); }
        .btn-export { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .btn-export:hover { background: #e2e8f0; }

        @media print {
            .no-print, .main-sidebar, .main-header, .card-header button, .card-footer, .filter-select { display: none !important; }
            .content-wrapper { margin: 0 !important; padding: 0 !important; }
            .stat-card { border: 1px solid #eee !important; box-shadow: none !important; page-break-inside: avoid; }
            .workload-card { box-shadow: none !important; border: 1px solid #eee !important; }
            body { background: white !important; }
        }

    </style>
@endpush

@section('content')
    <div class="container-fluid py-4 px-4">
        
        {{-- Header Section --}}
        <div class="dashboard-header d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <h1 class="dashboard-title">Teacher Workload</h1>
                <p class="dashboard-subtitle m-0">Resource allocation and capacity monitoring across all departments.</p>
            </div>
            <div class="d-flex gap-2 no-print">
                <button onclick="window.print()" class="btn btn-premium btn-export" title="Print this report as PDF">
                    <i class="fas fa-file-pdf mr-2"></i> Print PDF
                </button>
                <a href="{{ route('timetables.create') }}" class="btn btn-premium btn-assign">
                    <i class="fas fa-plus mr-2"></i> Assign Period
                </a>
            </div>
        </div>

        {{-- Summary Widgets --}}
        <div class="row mb-4">
            <div class="col-lg-3 col-sm-6 mb-4 mb-lg-0">
                <div class="stat-card">
                    <div class="stat-badge" style="background: #e0f2fe; color: #0369a1;">{{ date('H:i') }} LIVE</div>
                    <div class="stat-icon" style="background: #eff6ff; color: #1e40af;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value">{{ sprintf('%02d', $stats['total']) }}</div>
                    <div class="stat-label">Total Evaluated</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-4 mb-lg-0">
                <div class="stat-card">
                    @php 
                        $overPercent = $stats['total'] > 0 ? round(($stats['overloaded'] / $stats['total']) * 100) : 0; 
                        $overColor = $stats['overloaded'] > 0 ? '#ef4444' : '#64748b';
                        $overBg = $stats['overloaded'] > 0 ? '#fee2e2' : '#f1f5f9';
                    @endphp
                    <div class="stat-badge" style="background: {{ $overBg }}; color: {{ $overColor }};">
                        {{ $overPercent }}% AREA
                    </div>
                    <div class="stat-icon" style="background: {{ $overBg }}; color: {{ $overColor }};">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value">{{ sprintf('%02d', $stats['overloaded']) }}</div>
                    <div class="stat-label">Overloaded Professionals</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-4 mb-lg-0">
                <div class="stat-card">
                    @php 
                        $optPercent = $stats['total'] > 0 ? round(($stats['optimum'] / $stats['total']) * 100) : 0; 
                    @endphp
                    <div class="stat-badge" style="background: #dcfce7; color: #15803d;">{{ $optPercent }}% OPTIMUM</div>
                    <div class="stat-icon" style="background: #f0fdf4; color: #166534;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">{{ sprintf('%02d', $stats['optimum']) }}</div>
                    <div class="stat-label">Optimum Capacity</div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6 mb-4 mb-lg-0">
                <div class="stat-card">
                    <div class="stat-badge" style="background: #fef9c3; color: #a16207;">AVG LOAD</div>
                    <div class="stat-icon" style="background: #fffbeb; color: #9a3412;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value">{{ round($stats['avg_hours']) }}<small class="h6 ml-1 text-muted">h</small></div>
                    <div class="stat-label">Avg Weekly Hours</div>
                </div>
            </div>
        </div>

        {{-- Main Table Section --}}
        <div class="workload-card">
            <div class="card-header">
                <h3 class="card-title">Teacher Workload Distribution</h3>
                <div class="d-flex align-items-center gap-2">
                    {!! Form::open(['route' => 'teacher-workload.index', 'method' => 'GET', 'class' => 'd-flex gap-2']) !!}
                        {!! Form::select('academic_year_id', $academicYears->pluck('name', 'academic_year_id'), $selectedAcademicYearId, ['class' => 'filter-select', 'onchange' => 'this.form.submit()']) !!}
                        {!! Form::select('department_id', $departments, $selectedDepartmentId, ['class' => 'filter-select', 'onchange' => 'this.form.submit()']) !!}
                    {!! Form::close() !!}
                    <button class="btn btn-icon text-muted p-0 ml-2"><i class="fas fa-sliders-h"></i></button>
                </div>
            </div>
            
            <div class="p-0 table-responsive overflow-hidden" style="border-radius: 0 0 20px 20px;">
                <table class="custom-table table-hover">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Department</th>
                            <th class="text-center">Periods</th>
                            <th>Capacity Load</th>
                            <th class="text-center">Hrs/Week</th>
                            <th class="text-right pr-5">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workloadData as $data)
                            @php
                                $percent = min(($data['total_periods'] / 40) * 100, 100);
                                $statusClass = 'underloaded';
                                if($data['status']['label'] == 'Overloaded') $statusClass = 'overloaded';
                                if($data['status']['label'] == 'Standard') $statusClass = 'optimum';
                                
                                $barColor = '#475569'; // Default
                                if($statusClass == 'overloaded') $barColor = '#ef4444';
                                if($statusClass == 'optimum') $barColor = '#22c55e';
                                if($statusClass == 'underloaded') $barColor = '#0ea5e9';
                            @endphp
                            <tr>
                                <td>
                                    <div class="teacher-info">
                                        <img src="{{ asset('garikon-black.png') }}" class="teacher-avatar" alt="Avatar">
                                        <div>
                                            <span class="teacher-name">{{ $data['teacher']->full_name }}</span>
                                            <span class="teacher-title">{{ $data['teacher']->jobPosition->name ?? 'Faculty Member' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-weight: 500; color: #475569;">{{ $data['teacher']->department->name ?? 'General' }}</span>
                                </td>
                                <td class="text-center">
                                    <span style="font-weight: 600; color: #1e293b; font-size: 1rem;">{{ $data['total_periods'] }}</span>
                                </td>
                                <td>
                                    <div class="capacity-wrapper">
                                        <div class="custom-progress">
                                            <div class="custom-progress-bar" style="width: {{ $percent }}%; background: {{ $barColor }};"></div>
                                        </div>
                                        <span class="capacity-text">{{ round($percent) }}% Utilized</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span style="font-weight: 600; color: #1e293b; font-size: 1rem;">{{ round($data['est_hours']) }}</span>
                                </td>
                                <td class="text-right pr-5">
                                    <span class="status-pill status-{{ $statusClass }}">
                                        {{ $statusClass == 'optimum' ? 'OPTIMUM' : $data['status']['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No teachers found match the filter criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer / Pagination --}}
            <div class="card-footer bg-transparent py-4 px-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <p class="text-muted m-0" style="font-weight: 500; font-size: 0.875rem;">
                        Showing <strong>{{ $paginator->firstItem() }}</strong> to <strong>{{ $paginator->lastItem() }}</strong> of <strong>{{ $paginator->total() }}</strong> teachers
                    </p>
                    <div class="custom-pagination">
                        {{ $paginator->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

