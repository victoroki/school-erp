@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-list-ol mr-2"></i> Bulk Reports: {{ $exam->name }}
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <button class="btn btn-outline-success px-4" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Print All Selected
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-outline card-danger elevation-2 border-0">
            <div class="card-header bg-white">
                <h3 class="card-title">Class: <b>{{ $classSection->schoolClass->name }} - {{ $classSection->section->name }}</b></h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Admission No</th>
                            <th>Student Name</th>
                            <th>Status</th>
                            <th class="text-right pr-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td class="pl-4">{{ $student->admission_no }}</td>
                            <td class="font-weight-bold">{{ $student->full_name }}</td>
                            <td><span class="badge badge-success px-2">Ready</span></td>
                            <td class="text-right pr-4">
                                <a href="{{ route('exam-reports.individual', [$exam->exam_id, $student->student_id]) }}" class="btn btn-sm btn-light border shadow-sm" target="_blank">
                                    <i class="fas fa-file-pdf text-danger mr-1"></i> View Report Card
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
