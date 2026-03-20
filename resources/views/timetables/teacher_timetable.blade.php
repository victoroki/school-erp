@extends('layouts.app')

@section('content')
    <section class="content-header py-4">
        <div class="container-fluid">
            <div class="row align-items-center mb-3">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-dark" style="font-size: 1.85rem;">
                        <i class="fas fa-calendar-alt text-primary mr-2"></i>
                        @if($isAdmin && $viewingStaff->staff_id != ($staff->staff_id ?? 0))
                            <span class="text-muted font-weight-light">Schedule for:</span> {{ $viewingStaff->full_name }}
                        @else
                            My Teaching Schedule
                        @endif
                    </h1>
                    <div class="d-flex align-items-center mt-1">
                        <span class="badge badge-primary mr-2 px-2 py-1" style="font-size: 0.75rem; border-radius: 6px;">{{ $viewingStaff->designation ?? 'Academic Staff' }}</span>
                        <span class="text-muted small"><i class="fas fa-building mr-1"></i> {{ $viewingStaff->department->name ?? 'Main Department' }}</span>
                    </div>
                </div>
                <div class="col-sm-6 d-flex justify-content-end d-print-none mt-3 mt-sm-0">
                    <button onclick="window.print()" class="btn btn-white shadow-sm mr-2 px-3 py-2 border" style="border-radius: 10px; font-weight: 600;">
                        <i class="fas fa-print mr-2 text-primary"></i> Print Schedule
                    </button>
                    @if($isAdmin)
                    <a href="{{ route('timetables.index') }}" class="btn btn-primary shadow-sm px-4 py-2" style="border-radius: 10px; font-weight: 600;">
                        <i class="fas fa-plus mr-2"></i> Manage Lessons
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <!-- Filter & Selection Section -->
        <div class="card border-0 shadow-sm mb-4 d-print-none" style="border-radius: 15px;">
            <div class="card-body p-4">
                {!! Form::open(['route' => 'timetables.teacher', 'method' => 'GET', 'class' => 'row align-items-end']) !!}
                    @if($isAdmin)
                        <div class="form-group col-lg-4 col-md-6 mb-3 mb-lg-0">
                            <label class="small text-uppercase text-muted font-weight-bold mb-2">Select Teacher</label>
                            <select name="staff_id" class="form-control select2 custom-select shadow-xs" onchange="this.form.submit()" style="border-radius: 8px; height: 45px;">
                                @foreach($allTeachers as $t)
                                    <option value="{{ $t->staff_id }}" {{ $viewingStaff->staff_id == $t->staff_id ? 'selected' : '' }}>
                                        {{ $t->full_name }} ({{ $t->employee_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    
                    <div class="form-group col-lg-3 col-md-6 mb-3 mb-lg-0">
                        <label class="small text-uppercase text-muted font-weight-bold mb-2">Academic Year</label>
                        {!! Form::select('academic_year_id', $academicYearOptions, $selectedAcademicYearId, ['class' => 'form-control custom-select shadow-xs', 'onchange' => 'this.form.submit()', 'style' => 'border-radius: 8px; height: 45px;']) !!}
                    </div>

                    <div class="form-group col-lg-3 col-md-6 mb-3 mb-lg-0">
                        <label class="small text-uppercase text-muted font-weight-bold mb-2">Viewing View</label>
                        <select class="form-control custom-select shadow-xs" id="viewToggle" style="border-radius: 8px; height: 45px;">
                            <option value="grid">Grid View (Desktop)</option>
                            <option value="list">List View (Mobile Friendly)</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6 text-right">
                        <a href="{{ route('timetables.teacher') }}" class="btn btn-light btn-block shadow-xs" style="border-radius: 8px; height: 45px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-sync-alt mr-2"></i> Reset
                        </a>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="row mb-4">
            <div class="col-6 col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-bottom: 3px solid #3b82f6;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="bg-blue-light text-blue rounded-circle p-2 mr-2">
                                <i class="fas fa-book-reader fa-xs"></i>
                            </div>
                            <span class="text-uppercase text-muted extra-small font-weight-bold">Total Lessons</span>
                        </div>
                        <h3 class="font-weight-bold mb-0 text-dark">{{ $timetables->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-bottom: 3px solid #10b981;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="bg-green-light text-green rounded-circle p-2 mr-2">
                                <i class="fas fa-calendar-check fa-xs"></i>
                            </div>
                            <span class="text-uppercase text-muted extra-small font-weight-bold">Today</span>
                        </div>
                        <h3 class="font-weight-bold mb-0 text-dark">{{ $todayClasses->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-bottom: 3px solid #f59e0b;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="bg-orange-light text-orange rounded-circle p-2 mr-2">
                                <i class="fas fa-clock fa-xs"></i>
                            </div>
                            <span class="text-uppercase text-muted extra-small font-weight-bold">Credit Hours</span>
                        </div>
                        <h3 class="font-weight-bold mb-0 text-dark">{{ number_format($timetables->count() * 0.75, 1) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; border-bottom: 3px solid #ef4444;">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-1">
                            <div class="bg-red-light text-red rounded-circle p-2 mr-2">
                                <i class="fas fa-coffee fa-xs"></i>
                            </div>
                            <span class="text-uppercase text-muted extra-small font-weight-bold">Free Slots</span>
                            @php $freeCount = (count($daysOfWeek) * count($periods)) - $timetables->count(); @endphp
                        </div>
                        <h3 class="font-weight-bold mb-0 text-dark">{{ max(0, $freeCount) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Schedule Section -->
            <div class="col-lg-12">
                <!-- Desktop View: Weekly Grid -->
                <div class="card border-0 shadow-sm d-none d-lg-block mb-5" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-th-large mr-2 text-primary"></i>Weekly Grid Overview</h5>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-bordered mb-0" style="table-layout: fixed;">
                            <thead style="background-color: #f8fafc;">
                                <tr>
                                    <th style="width: 100px;" class="text-center py-3">Time</th>
                                    @foreach($daysOfWeek as $dayKey => $dayLabel)
                                        @php $isToday = strtolower(now()->format('l')) == $dayKey; @endphp
                                        <th class="text-center py-3 {{ $isToday ? 'bg-primary text-white shadow-sm' : '' }}">
                                            <div class="font-weight-bold">{{ $dayLabel }}</div>
                                            @if($isToday) <small class="text-uppercase font-weight-bold" style="opacity: 0.8; font-size: 0.65rem;">Today</small> @endif
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
                                        <td class="text-center align-middle px-2 py-3 bg-light-soft" style="position: relative;">
                                            @if($isNow)
                                                <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background-color: #3b82f6;"></div>
                                            @endif
                                            <div class="font-weight-bold small text-dark">{{ $period->name }}</div>
                                            <div class="extra-small text-muted mt-1">{{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }}</div>
                                        </td>
                                        @foreach($daysOfWeek as $dayKey => $dayLabel)
                                            @php
                                                $entry = $schedule[$dayKey][$period->period_id] ?? null;
                                                $isToday = strtolower(now()->format('l')) == $dayKey;
                                                $themeClass = $entry ? 'lesson-active' : 'lesson-free';
                                            @endphp
                                            <td class="p-2 align-top {{ $isToday ? 'bg-blue-soft' : '' }}" style="min-height: 110px;">
                                                @if($entry)
                                                    <div class="lesson-card h-100 p-2 border-0 shadow-xs shadow-hover transition-all" 
                                                         style="border-radius: 10px; border-left: 4px solid #3b82f6 !important; background-color: white;">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <span class="badge badge-primary-soft extra-small font-weight-bold">
                                                                {{ $entry->classSection->class->name ?? '' }} {{ $entry->classSection->section->name ?? '' }}
                                                            </span>
                                                            @if($isAdmin)
                                                                <a href="{{ route('timetables.edit', $entry->timetable_id) }}" class="text-muted extra-small"><i class="fas fa-edit"></i></a>
                                                            @endif
                                                        </div>
                                                        <div class="font-weight-bold small text-dark mt-1" style="line-height: 1.2;">
                                                            {{ $entry->subject->name }}
                                                        </div>
                                                        <div class="extra-small text-muted mt-2">
                                                            <i class="fas fa-map-marker-alt mr-1"></i> {{ $entry->classroom->room_number ?? 'Room' }}
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="border border-dashed h-100 rounded d-flex align-items-center justify-content-center text-muted opacity-25" style="border-width: 2px !important;">
                                                        <i class="fas fa-minus small"></i>
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

                <!-- Mobile View: Day Tabs and Cards -->
                <div class="d-lg-none mb-5">
                    <div class="nav-scroller mb-4">
                        <nav class="nav nav-pills justify-content-start flex-nowrap pb-2" style="overflow-x: auto;">
                            @foreach($daysOfWeek as $dayKey => $dayLabel)
                                @php $isToday = strtolower(now()->format('l')) == $dayKey; @endphp
                                <a class="nav-link mr-2 px-4 py-2 font-weight-bold shadow-xs {{ $isToday ? 'active' : 'bg-white text-dark' }}" 
                                   id="tab-{{ $dayKey }}" data-target="schedule-{{ $dayKey }}" 
                                   style="border-radius: 12px; white-space: nowrap;">
                                    {{ $dayLabel }}
                                </a>
                            @endforeach
                        </nav>
                    </div>

                    <div id="scheduleContainer">
                        @foreach($daysOfWeek as $dayKey => $dayLabel)
                            @php $isToday = strtolower(now()->format('l')) == $dayKey; @endphp
                            <div class="day-schedule {{ $isToday ? '' : 'd-none' }}" id="schedule-{{ $dayKey }}">
                                <h6 class="text-muted font-weight-bold text-uppercase mb-3 px-1">{{ $dayLabel }}'s Classes</h6>
                                @php $dayLessons = collect(); @endphp
                                @foreach($periods as $period)
                                    @php $entry = $schedule[$dayKey][$period->period_id] ?? null; @endphp
                                    @if($entry)
                                        @php $dayLessons->push($entry); @endphp
                                        <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px;">
                                            <div class="card-body p-3">
                                                <div class="row align-items-center">
                                                    <div class="col-3 text-center border-right">
                                                        <div class="font-weight-bold text-primary">{{ \Carbon\Carbon::parse($entry->period->start_time)->format('h:i') }}</div>
                                                        <div class="extra-small text-muted">{{ \Carbon\Carbon::parse($entry->period->end_time)->format('h:i A') }}</div>
                                                    </div>
                                                    <div class="col-9 pl-4">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="badge badge-light border extra-small mb-1">{{ $entry->classSection->class->name ?? '' }} {{ $entry->classSection->section->name ?? '' }}</span>
                                                            <span class="text-muted extra-small"><i class="fas fa-door-open mr-1"></i> {{ $entry->classroom->room_number ?? '-' }}</span>
                                                        </div>
                                                        <h6 class="font-weight-bold text-dark mb-0">{{ $entry->subject->name }}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                @if($dayLessons->isEmpty())
                                    <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 14px; background-color: #f8fafc;">
                                        <div class="mb-2 text-muted opacity-25">
                                            <i class="fas fa-calendar-day fa-3x"></i>
                                        </div>
                                        <p class="text-muted mb-0">No classes scheduled for {{ $dayLabel }}.</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .extra-small { font-size: 0.65rem; }
        .bg-blue-light { background-color: #eff6ff; }
        .bg-green-light { background-color: #ecfdf5; }
        .bg-orange-light { background-color: #fffbeb; }
        .bg-red-light { background-color: #fef2f2; }
        .bg-light-soft { background-color: #f8fafc; }
        .bg-blue-soft { background-color: #f0f9ff; }
        
        .text-blue { color: #3b82f6; }
        .text-green { color: #10b981; }
        .text-orange { color: #f59e0b; }
        .text-red { color: #ef4444; }
        
        .badge-primary-soft { background-color: #dbeafe; color: #1e40af; }
        
        .shadow-xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
        .shadow-hover:hover { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important; z-index: 5; }
        .transition-all { transition: all 0.2s ease-in-out; }
        
        .day-schedule { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .nav-scroller { position: relative; z-index: 2; height: 3.5rem; overflow-y: hidden; }
        .nav-scroller .nav { display: flex; flex-wrap: nowrap; padding-bottom: 1rem; margin-top: -1px; overflow-x: auto; text-align: center; white-space: nowrap; -webkit-overflow-scrolling: touch; }

        @media print {
            .nav-scroller, .d-print-none, section.content-header .btn, .card-header .btn { display: none !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
            .content-wrapper { margin-left: 0 !important; }
        }
    </style>

    @push('page_scripts')
    <script>
        $(document).ready(function() {
            // Day Tab Switching
            $('[id^="tab-"]').on('click', function(e) {
                e.preventDefault();
                var targetId = $(this).attr('data-target');
                
                $('[id^="tab-"]').removeClass('active bg-primary').addClass('bg-white text-dark');
                $(this).addClass('active bg-primary').removeClass('bg-white text-dark');
                
                $('.day-schedule').addClass('d-none');
                $('#' + targetId).removeClass('d-none');
            });

            // Handle selection from query param if needed
            var urlParams = new URLSearchParams(window.location.search);
            if(urlParams.has('day')) {
                $('#tab-' + urlParams.get('day')).click();
            }
        });
    </script>
    @endpush
@endsection
