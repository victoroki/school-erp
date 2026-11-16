@extends('layouts.app')

@section('title', 'Import students')

@section('content')
@php
    $studentColumns = [
        ['admission_no', true, 'Unique per student, up to 20 characters'],
        ['first_name', true, 'Up to 50 characters'],
        ['middle_name', false, 'Up to 50 characters'],
        ['last_name', true, 'Up to 50 characters'],
        ['date_of_birth', true, 'DD-MM-YYYY or YYYY-MM-DD'],
        ['gender', true, 'male, female or other'],
        ['city', false, 'Left blank: saved as "N/A"'],
        ['admission_date', true, 'DD-MM-YYYY or YYYY-MM-DD'],
        ['country', false, 'Left blank: Kenya'],
        ['nemis_number', false, 'Kenyan NEMIS ID'],
        ['phone', false, 'Student phone, up to 20 characters'],
        ['emergency_contact', false, 'Emergency phone number'],
        ['emergency_contact_name', false, 'Name of the emergency contact'],
        ['previous_school', false, 'Name of the previous school'],
        ['medical_conditions', false, 'Chronic conditions'],
        ['allergies', false, 'Known allergies'],
    ];
    $guardianColumns = [
        ['guardian_first_name', 'Up to 50 characters'],
        ['guardian_last_name', 'Up to 50 characters'],
        ['guardian_relationship', 'father, mother or guardian (default: guardian)'],
        ['guardian_phone', 'Used to find an existing guardian, up to 20 characters'],
        ['guardian_alternate_phone', 'Up to 20 characters'],
        ['guardian_email', 'Also used to find an existing guardian'],
        ['guardian_occupation', 'Up to 100 characters'],
        ['guardian_is_primary', '1 or yes (default), 0 or no'],
    ];
    $report = session('import_report');
@endphp

<style>
    .imp { padding: 24px 28px 40px; max-width: 1280px; }
    .imp-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
    .imp-head h1 { margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.015em; color: var(--shell-ink); }
    .imp-head p { margin: 4px 0 0; font-size: 14px; color: var(--shell-muted); }
    .imp-grid { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr); gap: 16px; align-items: start; margin-bottom: 16px; }
    .imp-card { background: #fff; border: 1px solid var(--shell-border); border-radius: 12px; }
    .imp-card > header { padding: 16px 20px 0; }
    .imp-card > header h2 { margin: 0; font-size: 15px; font-weight: 600; color: var(--shell-ink); }
    .imp-card > header p { margin: 3px 0 0; font-size: 13px; color: var(--shell-muted); }
    .imp-body { padding: 16px 20px 20px; }
    .imp-steps { list-style: none; margin: 0 0 18px; padding: 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .imp-steps li { display: flex; gap: 10px; align-items: flex-start; padding: 12px; border-radius: 10px; background: var(--shell-bg); font-size: 13px; color: var(--shell-ink-2); line-height: 1.4; }
    .imp-steps b { width: 22px; height: 22px; flex-shrink: 0; border-radius: 50%; background: var(--accent); color: #fff; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; }
    .imp-rules { margin: 0; padding-left: 18px; font-size: 13.5px; color: var(--shell-ink-2); line-height: 1.6; }
    .imp-rules li + li { margin-top: 6px; }
    .imp-rules code, .imp-cols code { font-size: 12.5px; color: var(--accent-strong); background: var(--accent-soft); padding: 1px 6px; border-radius: 5px; }
    .imp-note { margin-top: 14px; padding: 10px 12px; border-radius: 10px; background: #fef6e7; color: #7a4508; font-size: 13px; line-height: 1.45; }
    .imp-cols { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .imp-cols th { text-align: left; font-size: 11.5px; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; color: var(--shell-muted); background: var(--shell-bg); padding: 9px 14px; border-bottom: 1px solid var(--shell-border); }
    .imp-cols td { padding: 9px 14px; border-bottom: 1px solid #f0f2f6; color: var(--shell-ink-2); vertical-align: top; }
    .imp-cols tr.imp-group td { background: #fff; font-weight: 600; color: var(--shell-ink); padding-top: 16px; }
    .imp-cols tr.imp-group td span { font-weight: 400; color: var(--shell-muted); }
    .imp-req { font-size: 11.5px; font-weight: 600; padding: 2px 8px; border-radius: 999px; background: #fdf1f2; color: #b42335; white-space: nowrap; }
    .imp-opt { font-size: 12px; color: var(--shell-faint); }
    .imp-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
    .imp-stat { padding: 14px; border-radius: 10px; background: var(--shell-bg); }
    .imp-stat strong { display: block; font-size: 22px; font-weight: 700; font-variant-numeric: tabular-nums; color: var(--shell-ink); }
    .imp-stat span { font-size: 12.5px; color: var(--shell-muted); }
    .imp-stat.ok strong { color: #0f6b4b; }
    .imp-stat.bad strong { color: #b42335; }
    @media (max-width: 1100px) { .imp-grid { grid-template-columns: 1fr; } }
    @media (max-width: 640px) {
        .imp { padding: 18px 16px 32px; }
        .imp-steps { grid-template-columns: 1fr; }
        .imp-cols th:nth-child(2), .imp-cols td:nth-child(2) { white-space: nowrap; }
    }
</style>

<div class="imp">
    <div class="imp-head">
        <div>
            <h1>Import students</h1>
            <p>Add many students at once from an Excel file.</p>
        </div>
        <a href="{{ route('students.import.template') }}" class="btn btn-default">
            <i class="fas fa-download mr-1" aria-hidden="true"></i> Download template
        </a>
    </div>

    @include('flash::message')

    <div class="imp-grid">
        <div>
            <section class="imp-card" aria-labelledby="imp-upload-title">
                <header>
                    <h2 id="imp-upload-title">Upload the file</h2>
                    <p>Every student in the file joins the class and year you choose here.</p>
                </header>
                <div class="imp-body">
                    <ol class="imp-steps">
                        <li><b>1</b><span>Download the template and keep its header row as it is.</span></li>
                        <li><b>2</b><span>Fill in one student per row. Required columns are yellow.</span></li>
                        <li><b>3</b><span>Choose the class and year, then upload the file.</span></li>
                    </ol>

                    <form action="{{ route('students.import.store') }}" method="POST" enctype="multipart/form-data" id="importForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="class_section_id">Class and section</label>
                                    <select name="class_section_id" id="class_section_id" class="form-control select2" required>
                                        <option value="">Choose a class</option>
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
                                    <label for="academic_year_id">Academic year</label>
                                    <select name="academic_year_id" id="academic_year_id" class="form-control select2" required>
                                        <option value="">Choose a year</option>
                                        @foreach(\App\Models\AcademicYear::all() as $year)
                                            <option value="{{ $year->academic_year_id }}" {{ old('academic_year_id') == $year->academic_year_id ? 'selected' : '' }}>{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="excel_file">Excel file (.xlsx)</label>
                            <div class="custom-file">
                                <input type="file" name="excel_file" class="custom-file-input" id="excel_file" accept=".xlsx,.xls" required>
                                <label class="custom-file-label" for="excel_file">Choose a file</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" id="importBtn">
                            <i class="fas fa-upload mr-1" aria-hidden="true"></i> Import students
                        </button>
                    </form>
                </div>
            </section>

            @if($report)
                <section class="imp-card mt-3" aria-labelledby="imp-report-title">
                    <header><h2 id="imp-report-title">Import result</h2></header>
                    <div class="imp-body">
                        <div class="imp-stats">
                            <div class="imp-stat"><strong>{{ $report['total_rows'] }}</strong><span>Rows in the file</span></div>
                            <div class="imp-stat ok"><strong>{{ $report['imported'] }}</strong><span>Imported</span></div>
                            <div class="imp-stat {{ $report['failures'] ? 'bad' : '' }}"><strong>{{ count($report['failures']) }}</strong><span>Skipped</span></div>
                        </div>

                        @if($report['failures'])
                            <p class="mb-2" style="font-size: 13.5px; color: var(--shell-ink-2);">These rows were skipped. Fix them in the file and import it again; rows already imported are not duplicated.</p>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead><tr><th style="width: 90px;">Row</th><th>What to fix</th></tr></thead>
                                    <tbody>
                                        @foreach($report['failures'] as $failure)
                                            <tr>
                                                <td class="font-weight-bold">{{ $failure['row'] }}</td>
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
                            <div class="alert alert-success mb-0"><i class="fas fa-check-circle mr-1" aria-hidden="true"></i> Every row was imported.</div>
                        @endif
                    </div>
                </section>
            @endif
        </div>

        <aside class="imp-card" aria-labelledby="imp-rules-title">
            <header><h2 id="imp-rules-title">Before you upload</h2></header>
            <div class="imp-body">
                <ul class="imp-rules">
                    <li>Each <strong>admission number</strong> must be unique.</li>
                    <li>Gender is exactly <code>male</code>, <code>female</code> or <code>other</code>.</li>
                    <li>Dates are <code>DD-MM-YYYY</code> or <code>YYYY-MM-DD</code>.</li>
                    <li>To add guardians, fill at least their first name, last name and phone. A guardian already in the system is matched by phone or email, never duplicated.</li>
                    <li>Blood group, transport, address and other details can be added later from each student's page.</li>
                </ul>
                <p class="imp-note">Rows with mistakes are skipped and listed after the upload; all the other rows are still imported.</p>
            </div>
        </aside>
    </div>

    <section class="imp-card" aria-labelledby="imp-cols-title">
        <header>
            <h2 id="imp-cols-title">Template columns</h2>
            <p>The header row of the template, in order. Do not rename or remove columns.</p>
        </header>
        <div class="imp-body" style="padding-left: 0; padding-right: 0; padding-bottom: 8px;">
            <div class="table-responsive">
                <table class="imp-cols">
                    <thead>
                        <tr><th>Column</th><th>Required</th><th>What to enter</th></tr>
                    </thead>
                    <tbody>
                        <tr class="imp-group"><td colspan="3">Student</td></tr>
                        @foreach($studentColumns as [$name, $required, $notes])
                            <tr>
                                <td><code>{{ $name }}</code></td>
                                <td>@if($required)<span class="imp-req">Required</span>@else<span class="imp-opt">Optional</span>@endif</td>
                                <td>{{ $notes }}</td>
                            </tr>
                        @endforeach
                        <tr class="imp-group"><td colspan="3">Guardian <span>(all optional)</span></td></tr>
                        @foreach($guardianColumns as [$name, $notes])
                            <tr>
                                <td><code>{{ $name }}</code></td>
                                <td><span class="imp-opt">Optional</span></td>
                                <td>{{ $notes }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

@push('page_scripts')
<script>
    $(function () {
        $('.custom-file-input').on('change', function () {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass('selected').text(fileName || 'Choose a file');
        });

        // A large file takes a while; say so and stop a second submit.
        $('#importForm').on('submit', function () {
            $('#importBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1" aria-hidden="true"></i> Importing…');
        });
    });
</script>
@endpush
@endsection
