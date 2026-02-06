@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-file-import text-warning mr-2"></i>Bulk Student Import
                </h1>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-warning elevation-2">
                <div class="card-header border-0 bg-white">
                    <h3 class="card-title font-weight-bold">Upload CSV File</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('students.import.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small text-uppercase text-muted font-weight-bold">Target Class Section</label>
                                    <select name="class_section_id" class="form-control select2" required>
                                        <option value="">Select Class Section</option>
                                        @foreach(\App\Models\ClassSection::with(['schoolClass', 'section'])->get() as $cs)
                                            <option value="{{ $cs->class_section_id }}">
                                                {{ $cs->schoolClass->name }} - {{ $cs->section->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="small text-uppercase text-muted font-weight-bold">Academic Year</label>
                                    <select name="academic_year_id" class="form-control select2" required>
                                        <option value="">Select Year</option>
                                        @foreach(\App\Models\AcademicYear::all() as $year)
                                            <option value="{{ $year->academic_year_id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label class="small text-uppercase text-muted font-weight-bold">CSV File</label>
                            <div class="custom-file shadow-sm">
                                <input type="file" name="csv_file" class="custom-file-input" id="csv_file" required>
                                <label class="custom-file-label" for="csv_file">Choose file...</label>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle mr-1"></i> File must be a valid CSV. Download the template below.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-warning shadow-sm px-4 mt-3">
                            <i class="fas fa-upload mr-1 text-dark"></i> Start Import
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-info elevation-2">
                <div class="card-header border-0 bg-white">
                    <h3 class="card-title font-weight-bold">Instructions</h3>
                </div>
                <div class="card-body small">
                    <p>Follow these steps for a successful import:</p>
                    <ol class="pl-3">
                        <li>Download the <a href="#" class="text-info font-weight-bold">Sample CSV Template</a>.</li>
                        <li>Prepare your student data in the exact order shown.</li>
                        <li>Ensure <strong>Admission Number</strong> is unique.</li>
                        <li>The gender column should be 'male' or 'female'.</li>
                        <li>Upload the final CSV file here.</li>
                    </ol>
                    <div class="alert alert-warning-soft p-2 border-warning">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Missing fields will be filled with default values.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .alert-warning-soft { background-color: rgba(255, 193, 7, 0.1); border-left: 3px solid #ffc107; color: #856404; }
</style>

@push('page_scripts')
<script>
    $(function() {
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    });
</script>
@endpush
@endsection
