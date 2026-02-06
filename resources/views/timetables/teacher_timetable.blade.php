@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-user-clock text-info mr-2"></i>
                        @if($isAdmin && $viewingStaff->staff_id != ($staff->staff_id ?? 0))
                            <span class="text-muted">Viewing:</span> {{ $viewingStaff->full_name }}'s Timetable
                        @else
                            My Teaching Schedule
                        @endif
                    </h1>
                    <p class="text-muted mb-0">{{ $viewingStaff->designation }} | {{ $viewingStaff->department->name ?? 'Academic Dept' }}</p>
                </div>
                <div class="col-sm-6 text-right d-print-none">
                    <div class="btn-group shadow-sm">
                        <button onclick="window.print()" class="btn btn-outline-info">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                        <button class="btn btn-outline-info">
                            <i class="fas fa-file-pdf mr-1"></i> PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content px-3">
        @include('flash::message')

        <!-- Admin Selection & Filters -->
        <div class="card card-outline card-info elevation-2 mb-4 d-print-none">
            <div class="card-body py-3">
                {!! Form::open(['route' => 'timetables.teacher', 'method' => 'GET', 'class' => 'row align-items-end']) !!}
                    @if($isAdmin)
                        <div class="form-group col-md-4 mb-0">
                            <label class="small text-uppercase text-muted font-weight-bold">Select Teacher</label>
                            <select name="staff_id" class="form-control select2 shadow-sm" onchange="this.form.submit()">
                                @foreach($allTeachers as $t)
                                    <option value="{{ $t->staff_id }}" {{ $viewingStaff->staff_id == $t->staff_id ? 'selected' : '' }}>
                                        {{ $t->full_name }} ({{ $t->employee_id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    
                    <div class="form-group col-md-3 mb-0">
                        <label class="small text-uppercase text-muted font-weight-bold">Academic Year</label>
                        {!! Form::select('academic_year_id', $academicYearOptions, $selectedAcademicYearId, ['class' => 'form-control shadow-sm', 'onchange' => 'this.form.submit()']) !!}
                    </div>

                    <div class="form-group col-md-3 mb-0">
                        <label class="small text-uppercase text-muted font-weight-bold">Term</label>
                        <select class="form-control shadow-sm" disabled>
                            <option>Term 1 (Current)</option>
                            <option>Term 2</option>
                            <option>Term 3</option>
                        </select>
                    </div>

                    <div class="col-md-2 text-right">
                        <a href="{{ route('timetables.teacher') }}" class="btn btn-default btn-block">
                            <i class="fas fa-sync-alt mr-1"></i> Reset
                        </a>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>

        <!-- Summary Stats Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="info-box shadow-sm border-left border-info" style="border-left-width: 4px !important;">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-layer-group"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-uppercase x-small font-weight-bold">Weekly Load</span>
                        <span class="info-box-number h4 mb-0">{{ $timetables->count() }} <small class="text-muted">Lessons</small></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box shadow-sm border-left border-success" style="border-left-width: 4px !important;">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-calendar-day"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-uppercase x-small font-weight-bold">Today's Load</span>
                        <span class="info-box-number h4 mb-0">{{ $todayClasses->count() }} <small class="text-muted">Lessons</small></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box shadow-sm border-left border-warning" style="border-left-width: 4px !important;">
                    <span class="info-box-icon bg-warning elevation-1 text-white"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-uppercase x-small font-weight-bold">Credit Hours</span>
                        <span class="info-box-number h4 mb-0">{{ number_format($timetables->count() * 0.75, 1) }} <small class="text-muted">Hrs/Wk</small></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box shadow-sm border-left border-danger" style="border-left-width: 4px !important;">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-mug-hot"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-uppercase x-small font-weight-bold">Free Periods</span>
                        @php $freeCount = (count($daysOfWeek) * count($periods)) - $timetables->count(); @endphp
                        <span class="info-box-number h4 mb-0">{{ max(0, $freeCount) }} <small class="text-muted">Slots</small></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Weekly Grid -->
            <div class="col-lg-9">
                <div class="card card-outline card-info elevation-2">
                    <div class="card-header border-0 bg-white">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-table mr-1"></i> Weekly Teaching Grid
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-light border x-small"><i class="fas fa-circle text-info mr-1"></i> Teaching</span>
                            <span class="badge badge-light border x-small ml-2"><i class="fas fa-square text-muted border mr-1"></i> Free</span>
                        </div>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-bordered table-fixed mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 120px;" class="text-center align-middle">
                                        <div class="small text-muted font-weight-bold">TIME / DAY</div>
                                    </th>
                                    @foreach($daysOfWeek as $dayKey => $dayLabel)
                                        @php $isToday = strtolower(now()->format('l')) == $dayKey; @endphp
                                        <th class="text-center {{ $isToday ? 'bg-info text-white' : '' }}" style="min-width: 140px;">
                                            <div class="font-weight-bold">{{ $dayLabel }}</div>
                                            @if($isToday) <div class="x-small text-uppercase opacity-75">Today</div> @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($periods as $period)
                                    @php
                                        $pStart = \Carbon\Carbon::parse($period->start_time);
                                        $pEnd = \Carbon\Carbon::parse($period->end_time);
                                        $isNow = now()->between($pStart, $pEnd);
                                    @endphp
                                    <tr>
                                        <td class="bg-light text-center align-middle px-1 py-3 {{ $isNow ? 'border-left border-info' : '' }}" style="{{ $isNow ? 'border-left-width: 5px !important;' : '' }}">
                                            <div class="font-weight-bold small">{{ $period->name }}</div>
                                            <div class="x-small text-muted">{{ $period->start_time }}</div>
                                            <div class="x-small text-muted">- {{ $period->end_time }}</div>
                                            @if($isNow)
                                                <div class="badge badge-info mt-1 animate__animated animate__flash animate__infinite">NOW</div>
                                            @endif
                                        </td>
                                        @foreach($daysOfWeek as $dayKey => $dayLabel)
                                            @php
                                                $entry = $schedule[$dayKey][$period->period_id] ?? null;
                                                $isToday = strtolower(now()->format('l')) == $dayKey;
                                                
                                                // Color generation based on class
                                                $colors = ['info', 'primary', 'indigo', 'navy', 'teal', 'success'];
                                                $colorIdx = ($entry ? $entry->class_section_id : 0) % count($colors);
                                                $theme = $colors[$colorIdx];
                                            @endphp
                                            <td class="p-1 align-top {{ $isToday ? 'bg-light-info' : '' }}" style="height: 100px;">
                                                @if($entry)
                                                    <div class="h-100 p-2 rounded shadow-xs border-left border-{{ $theme }} bg-white lesson-card transition-all" style="border-left-width: 4px !important;">
                                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                                            <span class="badge badge-{{ $theme }} x-small px-2">{{ $entry->classSection->class->name ?? '' }} {{ $entry->classSection->section->name ?? '' }}</span>
                                                            @if($isAdmin)
                                                                <a href="{{ route('timetables.edit', $entry->timetable_id) }}" class="text-muted x-small hover-info d-print-none"><i class="fas fa-edit"></i></a>
                                                            @endif
                                                        </div>
                                                        <div class="font-weight-bold small text-dark line-clamp-2" title="{{ $entry->subject->name }}">
                                                            {{ $entry->subject->name }}
                                                        </div>
                                                        <div class="x-small text-muted mt-auto pt-2 border-top-dashed">
                                                            <i class="fas fa-door-open mr-1"></i> Room {{ $entry->classroom->room_number }}
                                                        </div>
                                                        <div class="d-print-none mt-2">
                                                            <a href="#" class="btn btn-xs btn-outline-{{ $theme }} btn-block">
                                                                <i class="fas fa-check-circle mr-1"></i> Attendance
                                                            </a>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="h-100 rounded border border-dashed d-flex align-items-center justify-content-center text-muted font-italic x-small bg-light-soft opacity-50">
                                                        <span>Free Slot</span>
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-lg-3">
                <!-- Today's Classes Widget REFINED -->
                <div class="card card-outline card-success elevation-2 mb-4 d-print-none">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-running mr-1"></i> Today's Activity</h3>
                    </div>
                    <div class="card-body p-0">
                        @forelse($todayClasses as $lesson)
                            @php
                                $startTime = \Carbon\Carbon::parse($lesson->period->start_time);
                                $isNext = $startTime->isFuture() && (!isset($foundNext) || !$foundNext);
                                if($isNext) $foundNext = true;
                            @endphp
                            <div class="p-3 border-bottom {{ $isNext ? 'bg-light-success' : '' }}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-xs font-weight-bold text-success">{{ $lesson->period->start_time }} - {{ $lesson->period->end_time }}</span>
                                    @if($isNext)
                                        <span class="badge badge-success animate__animated animate__pulse animate__infinite">NEXT CLASS</span>
                                    @endif
                                </div>
                                <div class="font-weight-bold text-dark">{{ $lesson->subject->name }}</div>
                                <div class="text-muted x-small">
                                    <i class="fas fa-users mr-1"></i> {{ $lesson->classSection->class->name ?? '' }} {{ $lesson->classSection->section->name ?? '' }} | Room {{ $lesson->classroom->room_number }}
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-check-circle fa-2x mb-2 opacity-25"></i>
                                <p class="small mb-0">No classes remaining today.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Assignment Summary -->
                <div class="card card-outline card-info elevation-2">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-book mr-1"></i> Subjects Taught</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($assignments as $a)
                                <li class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 font-weight-bold x-small">{{ $a->subject->name }}</h6>
                                    </div>
                                    <p class="mb-1 text-muted x-small">
                                        {{ $a->classSection->class->name ?? '' }} {{ $a->classSection->section->name ?? '' }}
                                    </p>
                                    @php 
                                        $weeklyMatches = $timetables->where('class_section_id', $a->class_section_id)->where('subject_id', $a->subject_id)->count();
                                    @endphp
                                    <span class="badge badge-light border x-small">{{ $weeklyMatches }} Lessons/Wk</span>
                                </li>
                            @empty
                                <li class="list-group-item text-center py-4 text-muted x-small">No direct subject assignments found.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .x-small { font-size: 0.70rem; }
        .bg-light-info { background-color: rgba(23, 162, 184, 0.05); }
        .bg-light-success { background-color: rgba(40, 167, 69, 0.05); }
        .bg-light-soft { background-color: rgba(0, 0, 0, 0.02); }
        .border-top-dashed { border-top: 1px dashed #dee2e6; }
        .lesson-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important; z-index: 10; cursor: pointer; }
        .transition-all { transition: all 0.2s ease; }
        .table-fixed { table-layout: fixed; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        
        @media print {
            .main-sidebar, .main-header, .d-print-none, .card-tools, .info-box { display: none !important; }
            .content-wrapper { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
            .table { width: 100% !important; border: 1px solid #333 !important; }
            .badge { border: 1px solid #333 !important; color: black !important; background: white !important; }
            .text-white { color: black !important; }
            .bg-info, .bg-success, .bg-primary, .bg-indigo { background-color: #f8f9fa !important; border: 1px solid #ddd !important; }
            .lesson-card { border: 1px solid #999 !important; background-color: #fff !important; }
            .text-muted { color: #555 !important; }
        }
    </style>
@endsection
