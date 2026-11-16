@extends('layouts.portal')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Attendance — {{ $student->full_name ?? '' }}</h3>
                    <div class="card-tools">
                        <form method="GET" action="{{ route('portal.attendance') }}" class="form-inline">
                            <input type="month" name="month" class="form-control form-control-sm mr-2" value="{{ $month ?? now()->format('Y-m') }}">
                            <button type="submit" class="btn btn-sm btn-primary">Go</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    @if(!empty($message))
                        <p class="text-muted">{{ $message }}</p>
                    @else
                    <!-- Summary -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Present</span>
                                    <span class="info-box-number">{{ $summary['present'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Absent</span>
                                    <span class="info-box-number">{{ $summary['absent'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Late</span>
                                    <span class="info-box-number">{{ $summary['late'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-calendar-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Days</span>
                                    <span class="info-box-number">{{ $summary['total'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Records -->
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Status</th>
                                <th>Class</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                            <tr>
                                <td>{{ $record->date->format('d/m/Y') }}</td>
                                <td>{{ $record->date->format('l') }}</td>
                                <td>
                                    @if($record->status === 'present')
                                        <span class="badge badge-success">Present</span>
                                    @elseif($record->status === 'absent')
                                        <span class="badge badge-danger">Absent</span>
                                    @elseif($record->status === 'late')
                                        <span class="badge badge-warning">Late</span>
                                    @else
                                        <span class="badge badge-info">{{ ucfirst($record->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $record->classSection?->name ?? 'N/A' }}</td>
                                <td>{{ $record->remarks ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No attendance records for this month.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
