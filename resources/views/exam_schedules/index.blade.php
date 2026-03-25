@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Exam Schedules</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('exam-schedules.create') }}">
                        Add New
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <!-- Filter Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('exam-schedules.index') }}" method="GET" class="form-inline">
                    <div class="form-group mr-3">
                        <label for="exam_id" class="mr-2 font-weight-bold">Select Exam Session:</label>
                        <select name="exam_id" id="exam_id" class="form-control select2 rounded-pill pr-5" style="min-width: 300px;">
                            <option value="">— Choose Session —</option>
                            @foreach($exams as $id => $name)
                                <option value="{{ $id }}" {{ $examId == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                        <i class="fas fa-filter mr-1"></i> View Master Timetable
                    </button>
                    @if($examId)
                        <a href="{{ route('exam-schedules.index') }}" class="btn btn-outline-secondary ml-2 rounded-pill px-4">
                            <i class="fas fa-sync-alt mr-1"></i> Reset
                        </a>
                        <button type="button" onclick="window.print()" class="btn btn-dark ml-2 rounded-pill px-4 shadow-sm">
                            <i class="fas fa-print mr-1"></i> Print Master
                        </button>
                    @endif
                </form>
            </div>
        </div>

        <div class="clearfix"></div>

        @if($selectedExam)
            @php
                $exam = $selectedExam;
                $uniqueDates = $exam->examSchedules->map(function($s) { return \Carbon\Carbon::parse($s->exam_date)->format('Y-m-d'); })->unique()->sort();
                $uniqueClasses = $exam->examSchedules->map(function($s) { return $s->class; })->filter()->unique('class_id')->sortBy('numeric_value');
                
                $grid = [];
                foreach($exam->examSchedules as $s) {
                    $d = \Carbon\Carbon::parse($s->exam_date)->format('Y-m-d');
                    $grid[$d][$s->class_id][] = $s;
                }
            @endphp
            
            <div class="card shadow-sm border-0 mb-5 no-print">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 font-weight-bold">
                        <i class="fas fa-th-large text-danger mr-2"></i> Master Timetable: {{ $selectedExam->name }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="timetable-grid-wrapper overflow-auto">
                        <table class="table table-bordered text-center bg-white mb-0" style="min-width: 800px;">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th class="align-middle" style="width: 180px; background: #212529 !important;">Date / Day</th>
                                    @foreach($uniqueClasses as $class)
                                        <th class="align-middle px-3 py-3" style="min-width: 150px;">
                                            <div class="h6 mb-0 font-weight-bold">{{ $class->name }}</div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @if($uniqueDates->count() > 0)
                                    @foreach($uniqueDates as $date)
                                        <tr>
                                            <td class="bg-light align-middle text-left p-3" style="border-right: 2px solid #dee2e6;">
                                                <div class="font-weight-bold text-dark">{{ \Carbon\Carbon::parse($date)->format('l') }}</div>
                                                <div class="small text-danger font-weight-bold">{{ \Carbon\Carbon::parse($date)->format('d M, Y') }}</div>
                                            </td>
                                            @foreach($uniqueClasses as $class)
                                                @php $entries = $grid[$date][$class->class_id] ?? []; @endphp
                                                <td class="align-top p-2 {{ empty($entries) ? 'bg-light-fade' : '' }}">
                                                    @foreach($entries as $schedule)
                                                        <div class="schedule-item border rounded p-2 mb-2 text-left bg-white shadow-xs" style="border-left: 3px solid #dc3545 !important;">
                                                            <div class="font-weight-bold small text-primary mb-1">{{ $schedule->subject->name }}</div>
                                                            <div class="small text-muted mb-1">
                                                                <i class="far fa-clock mr-1"></i> 
                                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                            </div>
                                                            <div class="small text-dark">
                                                                <i class="fas fa-door-open mr-1 opacity-50"></i> {{ $schedule->room->name ?? '102' }}
                                                            </div>
                                                            <div class="mt-1 d-flex justify-content-end no-print">
                                                                <a href="{{ route('exam-schedules.edit', $schedule->schedule_id) }}" class="text-info pt-1">
                                                                    <i class="fas fa-edit fa-xs"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    @if(empty($entries))
                                                        <span class="text-muted small italic opacity-30">No Exam</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="{{ count($uniqueClasses) + 1 }}" class="py-5 text-muted">
                                            No schedules recorded for this session.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Printable Version -->
            <div id="printableTimetable" class="d-none no-screen printable-area">
                <div class="text-center mb-4">
                    <h2 class="font-weight-bold text-uppercase">Garikon School</h2>
                    <h4 class="text-muted">Master Exam Timetable: {{ $selectedExam->name }}</h4>
                    <hr>
                </div>
                <!-- Table repeated for print with no wrapping/overflow -->
                <table class="table table-bordered text-center">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th>Date / Day</th>
                            @foreach($uniqueClasses as $class)
                                <th>{{ $class->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($uniqueDates as $date)
                            <tr>
                                <td class="text-left">
                                    <b>{{ \Carbon\Carbon::parse($date)->format('l') }}</b><br>
                                    <small>{{ \Carbon\Carbon::parse($date)->format('d M, Y') }}</small>
                                </td>
                                @foreach($uniqueClasses as $class)
                                    @php $entries = $grid[$date][$class->class_id] ?? []; @endphp
                                    <td>
                                        @foreach($entries as $schedule)
                                            <div style="text-align: left; margin-bottom: 5px; border-bottom: 1px solid #eee;">
                                                <b>{{ $schedule->subject->name }}</b><br>
                                                <small>{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</small>
                                            </div>
                                        @endforeach
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @else
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white font-weight-bold">Recent Exam Schedules</div>
                @include('exam_schedules.table')
            </div>
            
            <div class="mt-4 p-5 text-center bg-light rounded border border-dashed">
                <i class="fas fa-th-large fa-3x text-muted mb-3 opacity-30"></i>
                <h5 class="text-secondary">Select an Exam Session to view the Master Timetable Grid</h5>
                <p class="text-muted small">You can choose a session from the dropdown above to see the overview of the entire session's schedule across all classes.</p>
            </div>
        @endif
    </div>

    <style>
        .no-screen { display: none; }
        .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .border-dashed { border-style: dashed !important; }
        .bg-light-fade { background-color: rgba(248, 249, 250, 0.4); }

        @media print {
            body { background: white !important; font-family: 'Times New Roman', serif; }
            .main-sidebar, .main-header, .content-header, .card:not(.printable-area), .btn, form, .no-print, footer { display: none !important; }
            .content-wrapper, .content, .container-fluid { 
                margin: 0 !important; 
                padding: 0 !important; 
                border: 0 !important; 
                box-shadow: none !important; 
                width: 100% !important;
            }
            .no-screen { display: block !important; }
            .printable-area { display: block !important; width: 100% !important; }
            .table-bordered th, .table-bordered td { border: 1px solid #000 !important; color: #000 !important; }
            .bg-dark { background-color: #333 !important; color: white !important; -webkit-print-color-adjust: exact; }
        }
    </style>

@endsection
