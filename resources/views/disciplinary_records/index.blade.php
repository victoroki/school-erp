@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-danger font-weight-bold">
                        <i class="fas fa-gavel mr-2"></i> Disciplinary Records
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('disciplinary-records.create') }}" class="btn btn-danger shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Add Record
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card card-outline card-danger shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Student</th>
                                <th>Incident Date</th>
                                <th>Incident Type</th>
                                <th>Description</th>
                                <th>Reported By</th>
                                <th>Status</th>
                                <th>Action Taken</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($disciplinaryRecords as $record)
                                <tr>
                                    <td>
                                        <a href="{{ route('students.show', [$record->student_id]) }}?tab=disciplinary" class="font-weight-bold">
                                            {{ optional($record->student)->first_name }} {{ optional($record->student)->last_name }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ optional($record->student)->admission_no }}</small>
                                    </td>
                                    <td>{{ $record->incident_date->format('d M, Y') }}</td>
                                    <td>
                                        <span class="badge badge-warning">{{ ucfirst($record->incident_type) }}</span>
                                    </td>
                                    <td title="{{ $record->description }}">
                                        {{ Str::limit($record->description, 50) }}
                                    </td>
                                    <td>{{ optional($record->reporter)->name }}</td>
                                    <td>
                                        @php
                                            $badgeClass = [
                                                'open' => 'badge-danger',
                                                'investigating' => 'badge-info',
                                                'closed' => 'badge-success'
                                            ][$record->status] ?? 'badge-secondary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($record->status) }}</span>
                                    </td>
                                    <td>{{ Str::limit($record->action_taken, 50) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix bg-white">
                <div class="float-right">
                    {{ $disciplinaryRecords->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
