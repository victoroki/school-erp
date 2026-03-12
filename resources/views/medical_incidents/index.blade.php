@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-success font-weight-bold">
                        <i class="fas fa-notes-medical mr-2"></i> Medical Incidents
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('medical-incidents.create') }}" class="btn btn-success shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Add Incident
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card card-outline card-success shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Student</th>
                                <th>Incident Date</th>
                                <th>Symptoms</th>
                                <th>Details</th>
                                <th>Treatment Given</th>
                                <th>Parents Notified</th>
                                <th>Marked By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($medicalIncidents as $incident)
                                <tr>
                                    <td>
                                        <a href="{{ route('students.show', [$incident->student_id]) }}?tab=medical" class="font-weight-bold">
                                            {{ optional($incident->student)->first_name }} {{ optional($incident->student)->last_name }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ optional($incident->student)->admission_no }}</small>
                                    </td>
                                    <td>{{ $incident->incident_date->format('d M, Y') }}</td>
                                    <td>{{ $incident->symptoms }}</td>
                                    <td title="{{ $incident->details }}">
                                        {{ Str::limit($incident->details, 50) }}
                                    </td>
                                    <td>{{ Str::limit($incident->treatment_given, 50) }}</td>
                                    <td>
                                        @if($incident->notified_parents)
                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Yes</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($incident->marker)->name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix bg-white">
                <div class="float-right">
                    {{ $medicalIncidents->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
