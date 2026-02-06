@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-file-invoice text-warning mr-2"></i>Attendance Report
                </h1>
                <p class="text-muted mb-0 small">
                    Class: <span class="font-weight-bold text-dark">{{ $classSections->find($classSectionId)->schoolClass->name }} - {{ $classSections->find($classSectionId)->section->name }}</span> | 
                    Period: <span class="font-weight-bold text-dark">{{ date('F Y', mktime(0, 0, 0, $month, 10)) }}</span>
                </p>
            </div>
            <div class="col-sm-6 text-right">
                <div class="btn-group">
                    <button onclick="window.print()" class="btn btn-outline-info shadow-sm mr-2">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                    <a href="{{ route('student-attendance.report') }}" class="btn btn-default shadow-sm text-warning border-warning">
                        <i class="fas fa-filter mr-1"></i> New Filter
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    @php
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    @endphp

    <div class="card card-outline card-warning elevation-2 overflow-hidden">
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-sm mb-0 text-center" style="font-size: 0.8rem;">
                <thead class="bg-light">
                    <tr>
                        <th rowspan="2" class="align-middle text-left pl-3" style="min-width: 200px;">Student Name</th>
                        <th colspan="{{ $daysInMonth }}">Days of the Month</th>
                        <th rowspan="2" class="align-middle bg-light-soft" style="width: 60px;">%</th>
                    </tr>
                    <tr>
                        @for($d=1; $d<=$daysInMonth; $d++)
                            @php 
                                $dateObj = \Carbon\Carbon::createFromDate($year, $month, $d);
                                $isWeekend = $dateObj->isWeekend();
                            @endphp
                            <th class="{{ $isWeekend ? 'bg-secondary opacity-50' : '' }}" style="width: 25px;">{{ $d }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                        @php 
                            $attendanceMap = $student->studentAttendances->keyBy(function($item) {
                                return $item->date->format('j');
                            });
                            $presentCount = 0;
                            $countTotal = 0;
                        @endphp
                        <tr>
                            <td class="text-left pl-3 align-middle">
                                <span class="font-weight-bold">{{ $student->full_name }}</span>
                                <div class="x-small text-muted">{{ $student->admission_no }}</div>
                            </td>
                            @for($d=1; $d<=$daysInMonth; $d++)
                                @php 
                                    $att = $attendanceMap[$d] ?? null;
                                    $statusChar = '-';
                                    $class = 'text-muted';
                                    
                                    if($att) {
                                        $countTotal++;
                                        switch($att->status) {
                                            case 'present': $statusChar = 'P'; $class = 'text-success font-weight-bold'; $presentCount++; break;
                                            case 'absent': $statusChar = 'A'; $class = 'text-danger font-weight-bold'; break;
                                            case 'late': $statusChar = 'L'; $class = 'text-warning font-weight-bold'; $presentCount += 0.5; break;
                                            case 'excused': $statusChar = 'E'; $class = 'text-info font-weight-bold'; break;
                                        }
                                    }
                                    $dateObj = \Carbon\Carbon::createFromDate($year, $month, $d);
                                @endphp
                                <td class="{{ $class }} {{ $dateObj->isWeekend() ? 'bg-light' : '' }} align-middle">
                                    {{ $statusChar }}
                                </td>
                            @endfor
                            <td class="align-middle font-weight-bold bg-light-soft text-dark">
                                {{ $countTotal > 0 ? round(($presentCount / $countTotal) * 100) : 0 }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .bg-light-soft { background-color: rgba(0,0,0,0.03); }
    .x-small { font-size: 0.7rem; }
    @media print {
        .btn, .main-header, .main-sidebar, .card-header .btn-group { display: none !important; }
        .table { font-size: 0.6rem !important; }
        .content-wrapper { margin-left: 0 !important; }
    }
</style>
@endsection
