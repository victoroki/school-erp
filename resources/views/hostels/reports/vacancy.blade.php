@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="mb-1">Vacancy Report</h1>
                    <p class="text-muted mb-0">
                        {{ $hostel->name ?? 'All hostels' }} &middot; rooms with at least one free bed
                    </p>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ route('hostel.vacancy-report.pdf', request()->only('hostel_id')) }}" class="btn btn-outline-danger">
                        <i class="fas fa-file-pdf mr-1"></i> Export PDF
                    </a>
                    <button onclick="window.print()" class="btn btn-default"><i class="fas fa-print mr-1"></i> Print</button>
                    <a href="{{ route('hostel.reports') }}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i> Reports</a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-door-open"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Rooms with beds free</span>
                        <span class="info-box-number">{{ $summary['rooms'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-teal"><i class="fas fa-bed"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Free beds</span>
                        <span class="info-box-number">{{ $summary['free_beds'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-user-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Beds occupied in these rooms</span>
                        <span class="info-box-number">{{ $summary['occupied'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-secondary"><i class="fas fa-tools"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Rooms in maintenance</span>
                        <span class="info-box-number">{{ $summary['maintenance_rooms'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Available Beds by Room</h3>
                <div class="card-tools">
                    <form action="{{ route('hostel.vacancy-report') }}" method="GET" class="d-flex">
                        <select name="hostel_id" class="form-control form-control-sm mr-2" style="width: auto">
                            <option value="">All Hostels</option>
                            @foreach($hostels as $hostelId => $hostelName)
                                <option value="{{ $hostelId }}" @selected((string) request('hostel_id') === (string) $hostelId)>{{ $hostelName }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-info">Filter</button>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Hostel</th>
                                <th>Room Number</th>
                                <th>Room Type</th>
                                <th class="text-center">Capacity</th>
                                <th class="text-center">Occupied</th>
                                <th class="text-center">Available Beds</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rooms as $room)
                                <tr>
                                    <td>{{ optional($room->hostel)->name ?? 'N/A' }}</td>
                                    <td>{{ $room->room_number }}</td>
                                    <td>{{ ucfirst($room->room_type) }}</td>
                                    <td class="text-center">{{ $room->capacity }}</td>
                                    <td class="text-center">{{ $room->occupied }}</td>
                                    <td class="text-center text-success font-weight-bold">{{ $room->getAvailableBeds() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                                        <strong>No free beds to report</strong>
                                        <p class="text-muted small mb-0">
                                            Every room in this selection is full or under maintenance.
                                        </p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($rooms->isNotEmpty())
                            <tfoot>
                                <tr class="bg-light">
                                    <th colspan="3">Totals</th>
                                    <th class="text-center">{{ $summary['capacity'] }}</th>
                                    <th class="text-center">{{ $summary['occupied'] }}</th>
                                    <th class="text-center text-success">{{ $summary['free_beds'] }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_css')
<style>
    @media print {
        .main-footer, .nav-item, .btn, .content-header h1, .info-box {
            display: none !important;
        }
        .content-header .text-right, .card-tools {
            display: none !important;
        }
        .card {
            border: none !important;
        }
    }
</style>
@endpush
