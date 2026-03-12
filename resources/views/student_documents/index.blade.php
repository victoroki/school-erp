@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Student Documents</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('student-documents.create') }}">
                        Add New
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <!-- Filter Bar -->
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('student-documents.index') }}">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label class="small text-muted font-weight-bold">SEARCH STUDENT</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                                    </div>
                                    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name or Admission Number...">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label class="small text-muted font-weight-bold">CLASS SECTION</label>
                                <select name="class_section_id" class="form-control select2">
                                    <option value="">All Classes</option>
                                    @foreach($classSections as $cs)
                                        <option value="{{ $cs->class_section_id }}" {{ request('class_section_id') == $cs->class_section_id ? 'selected' : '' }}>
                                            {{ $cs->schoolClass->name }} - {{ $cs->section->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label class="small text-muted font-weight-bold">ACADEMIC YEAR</label>
                                <select name="academic_year_id" class="form-control select2">
                                    <option value="">All Years</option>
                                    @foreach($academicYears as $ay)
                                        <option value="{{ $ay->academic_year_id }}" {{ request('academic_year_id') == $ay->academic_year_id ? 'selected' : '' }}>
                                            {{ $ay->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 text-right">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary px-3 shadow-sm" title="Apply Filter">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                                <a href="{{ route('student-documents.index') }}" class="btn btn-light border px-3" title="Clear All">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-outline card-primary shadow-sm">
            @include('student_documents.table')
        </div>
    </div>

    @push('page_css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
        <style>
            .select2-container .select2-selection--single { height: 38px !important; }
            .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
            .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; }
        </style>
    @endpush

    @push('page_scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            });
        </script>
    @endpush

@endsection
