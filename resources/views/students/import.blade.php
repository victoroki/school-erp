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
                    <h3 class="card-title font-weight-bold">Upload Excel File</h3>
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
                                            <option value="{{ $cs->class_section_id }}" {{ old('class_section_id') == $cs->class_section_id ? 'selected' : '' }}>
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
                                            <option value="{{ $year->academic_year_id }}" {{ old('academic_year_id') == $year->academic_year_id ? 'selected' : '' }}>{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label class="small text-uppercase text-muted font-weight-bold">Excel File</label>
                            <div class="custom-file shadow-sm">
                                <input type="file" name="excel_file" class="custom-file-input" id="excel_file" accept=".xlsx,.xls" required>
                                <label class="custom-file-label" for="excel_file">Choose file...</label>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle mr-1"></i> File must be a valid Excel (.xlsx) workbook. Download the template below and keep the column headers unchanged.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-warning shadow-sm px-4 mt-3">
                            <i class="fas fa-upload mr-1 text-dark"></i> Start Import
                        </button>
                    </form>
                </div>
            </div>

            @if(session('import_report'))
                @php
                    $report = session('import_report');
                @endphp
                <div class="card card-outline {{ $report['failures'] ? 'card-danger' : 'card-success' }} elevation-2">
                    <div class="card-header border-0 bg-white">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-clipboard-check mr-1"></i> Import Report
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-md-4">
                                <div class="border rounded p-3">
                                    <div class="h3 mb-0">{{ $report['total_rows'] }}</div>
                                    <small class="text-muted">Total Rows</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 bg-success-soft">
                                    <div class="h3 mb-0 text-success">{{ $report['imported'] }}</div>
                                    <small class="text-muted">Imported</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 bg-danger-soft">
                                    <div class="h3 mb-0 text-danger">{{ count($report['failures']) }}</div>
                                    <small class="text-muted">Skipped</small>
                                </div>
                            </div>
                        </div>

                        @if($report['failures'])
                            <div class="alert alert-danger">
                                <strong><i class="fas fa-exclamation-triangle mr-1"></i> The following rows were skipped:</strong>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 90px;">Row</th>
                                            <th>Reason(s)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($report['failures'] as $failure)
                                            <tr>
                                                <td class="align-middle font-weight-bold">{{ $failure['row'] }}</td>
                                                <td>
                                                    <ul class="mb-0 pl-3">
                                                        @foreach($failure['errors'] as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-check-circle mr-1"></i> All rows imported successfully.
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-info elevation-2">
                <div class="card-header border-0 bg-white">
                    <h3 class="card-title font-weight-bold">Instructions</h3>
                </div>
                <div class="card-body small">
                    <p>Follow these steps for a successful import:</p>
                    <ol class="pl-3">
                        <li>Download the <a href="{{ route('students.import.template') }}" class="text-info font-weight-bold">Excel Template</a>.</li>
                        <li>Fill in one student per row. Do not change the column headers in the first row.</li>
                        <li>Required columns are highlighted in yellow in the template.</li>
                        <li>Ensure <strong>Admission Number</strong> is unique per student.</li>
                        <li>Gender must be exactly: <code>male</code>, <code>female</code>, or <code>other</code>.</li>
                        <li>Dates use the format DD-MM-YYYY or YYYY-MM-DD.</li>
                        <li>Class/Section and Academic Year are selected on the upload form above.</li>
                        <li>Upload the final .xlsx file here.</li>
                    </ol>

                    <h6 class="font-weight-bold mt-3 mb-2">Column Reference</h6>
                    <table class="table table-sm table-bordered mb-2">
                        <thead class="bg-light">
                            <tr><th>Column</th><th>Req?</th><th>Format / Notes</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code>admission_no</code></td><td>Yes</td><td>Unique ID, max 20 chars</td></tr>
                            <tr><td><code>first_name</code></td><td>Yes</td><td>Max 50 chars</td></tr>
                            <tr><td><code>middle_name</code></td><td>No</td><td>Max 50 chars</td></tr>
                            <tr><td><code>last_name</code></td><td>Yes</td><td>Max 50 chars</td></tr>
                            <tr><td><code>date_of_birth</code></td><td>Yes</td><td>DD-MM-YYYY or YYYY-MM-DD</td></tr>
                            <tr><td><code>gender</code></td><td>Yes</td><td><code>male</code>, <code>female</code>, <code>other</code></td></tr>
                            <tr><td><code>city</code></td><td>No</td><td>Defaults to "N/A" if blank</td></tr>
                            <tr><td><code>admission_date</code></td><td>Yes</td><td>DD-MM-YYYY or YYYY-MM-DD</td></tr>
                            <tr><td><code>country</code></td><td>No</td><td>Defaults to Kenya</td></tr>
                            <tr><td><code>nemis_number</code></td><td>No</td><td>Kenyan NEMIS ID</td></tr>
                            <tr><td><code>phone</code></td><td>No</td><td>Student phone, max 20 chars</td></tr>
                            <tr><td><code>emergency_contact</code></td><td>No</td><td>Emergency phone number</td></tr>
                            <tr><td><code>emergency_contact_name</code></td><td>No</td><td>Contact person's name</td></tr>
                            <tr><td><code>previous_school</code></td><td>No</td><td>Previous school name</td></tr>
                            <tr><td><code>medical_conditions</code></td><td>No</td><td>Chronic conditions</td></tr>
                            <tr><td><code>allergies</code></td><td>No</td><td>Known allergies</td></tr>
                        </tbody>
                    </table>
                    <small class="text-muted d-block mb-2">Additional fields (blood group, transport, address, etc.) can be edited after import via the student edit form.</small>

                    <div class="alert alert-warning-soft p-2 border-warning mb-0">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Rows with errors are skipped and reported — the remaining valid rows are still imported.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .alert-warning-soft { background-color: rgba(255, 193, 7, 0.1); border-left: 3px solid #ffc107; color: #856404; }
    .bg-success-soft { background-color: rgba(40, 167, 69, 0.05); }
    .bg-danger-soft { background-color: rgba(220, 53, 69, 0.05); }
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
