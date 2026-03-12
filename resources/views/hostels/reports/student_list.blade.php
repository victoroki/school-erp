@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Hostel Student List: {{ $hostel->name ?? 'All Hostels' }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <button onclick="window.print()" class="btn btn-default"><i class="fas fa-print mr-1"></i> Print List</button>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Gender</th>
                            <th>Hostel</th>
                            <th>Room</th>
                            <th>Allotted Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allocations as $allocation)
                            <tr>
                                <td>{{ optional($allocation->student)->student_id ?? 'N/A' }}</td>
                                <td>{{ optional($allocation->student)->first_name ?? 'Unknown' }} {{ optional($allocation->student)->last_name ?? '' }}</td>
                                <td>{{ optional($allocation->student)->gender ? ucfirst($allocation->student->gender) : 'N/A' }}</td>
                                <td>{{ optional($allocation->hostel)->name ?? 'N/A' }}</td>
                                <td>{{ optional($allocation->room)->room_number ?? 'N/A' }}</td>
                                <td>{{ $allocation->allocation_date ? $allocation->allocation_date->format('d M, Y') : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No students found in the selected criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
