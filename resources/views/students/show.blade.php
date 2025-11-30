@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
Student Details
                    </h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right"
                       href="{{ route('students.index') }}">
                                                    Back
                                            </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('students.edit', [$student->student_id]) }}" class="btn btn-primary btn-sm">Edit</a>
                    <a href="{{ route('student-documents.create') }}?student_id={{ $student->student_id }}" class="btn btn-outline-secondary btn-sm">Add Document</a>
                </div>
                <a class="btn btn-default btn-sm" href="{{ route('students.index') }}">Back</a>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab-profile" role="tab">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-contact" role="tab">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-admission" role="tab">Admission</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-documents" role="tab">Documents</a>
                    </li>
                </ul>
                <div class="tab-content pt-3">
                    <div class="tab-pane fade show active" id="tab-profile" role="tabpanel">
                        <div class="row">
                            @include('students.show_fields')
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-contact" role="tabpanel">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card"><div class="card-body">City: {{ $student->city }}</div></div>
                            </div>
                            <div class="col-md-4">
                                <div class="card"><div class="card-body">Country: {{ $student->country }}</div></div>
                            </div>
                            <div class="col-md-4">
                                <div class="card"><div class="card-body">Phone: {{ $student->phone }}</div></div>
                            </div>
                            <div class="col-md-4">
                                <div class="card"><div class="card-body">Emergency: {{ $student->emergency_contact }}</div></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-admission" role="tabpanel">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card"><div class="card-body">Admission Date: {{ $student->admission_date }}</div></div>
                            </div>
                            <div class="col-md-4">
                                <div class="card"><div class="card-body">
                                    Status:
                                    @php
                                        $map = [
                                            'active' => 'success',
                                            'inactive' => 'secondary',
                                            'alumni' => 'primary',
                                            'transferred' => 'warning'
                                        ];
                                        $cls = $map[$student->status] ?? 'light';
                                    @endphp
                                    <span class="badge badge-{{ $cls }}">{{ ucfirst($student->status) }}</span>
                                </div></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-documents" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Name</th>
                                        <th>File</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($student->studentDocuments as $doc)
                                    <tr>
                                        <td>{{ $doc->document_type }}</td>
                                        <td>{{ $doc->document_name }}</td>
                                        <td>{{ $doc->file_path }}</td>
                                        <td>
                                            @if($doc->file_path)
                                                <a href="{{ route('student-documents.download', [$doc->document_id]) }}" class="btn btn-outline-primary btn-xs">Download</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
