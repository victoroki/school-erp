@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Vacancy Report</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <button onclick="window.print()" class="btn btn-default"><i class="fas fa-print mr-1"></i> Print Report</button>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title">Available Beds by Room</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Hostel</th>
                            <th>Room Number</th>
                            <th>Room Type</th>
                            <th>Capacity</th>
                            <th>Occupied</th>
                            <th>Available Beds</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $room)
                            <tr>
                                <td>{{ $room->hostel->name }}</td>
                                <td>{{ $room->room_number }}</td>
                                <td>{{ ucfirst($room->room_type) }}</td>
                                <td>{{ $room->capacity }}</td>
                                <td>{{ $room->occupied }}</td>
                                <td class="text-success font-weight-bold">{{ $room->capacity - $room->occupied }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No vacant rooms found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('page_css')
<style>
    @media print {
        .main-footer, .nav-item, .btn, .content-header h1 {
            display: none !important;
        }
        .content-header .text-right {
            display: none !important;
        }
        .card {
            border: none !important;
        }
    }
</style>
@endpush
