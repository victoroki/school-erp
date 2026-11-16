@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-heartbeat text-pink mr-2"></i>Medical Records Report
                </h1>
                <p class="text-muted small mb-0">Students with medical conditions, allergies, or active medications</p>
            </div>
            <div class="col-sm-6 text-right">
                <div class="btn-group mr-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fas fa-download mr-1"></i> Export</button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('student-reports.medical.csv') }}"><i class="fas fa-file-csv text-success mr-2"></i>Download CSV</a>
                        <a class="dropdown-item" href="{{ route('student-reports.medical.pdf') }}"><i class="fas fa-file-pdf text-danger mr-2"></i>Download PDF</a>
                    </div>
                </div>
                <button onclick="window.print()" class="btn btn-outline-info btn-sm shadow-sm">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
                <a href="{{ route('student-reports.index') }}" class="btn btn-default btn-sm shadow-sm border ml-2">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card border-left-danger">
                <div class="stat-icon"><i class="fas fa-notes-medical text-danger"></i></div>
                <div>
                    <div class="stat-value text-danger">{{ $totalWithConditions }}</div>
                    <div class="stat-label">With Medical Conditions</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card border-left-warning">
                <div class="stat-icon"><i class="fas fa-allergies text-warning"></i></div>
                <div>
                    <div class="stat-value text-warning">{{ $totalWithAllergies }}</div>
                    <div class="stat-label">With Allergies</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card border-left-info">
                <div class="stat-icon"><i class="fas fa-pills text-info"></i></div>
                <div>
                    <div class="stat-value text-info">{{ $totalOnMedication }}</div>
                    <div class="stat-label">On Active Medication</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card card-outline card-danger elevation-2 border-0">
        <div class="card-header bg-white">
            <h3 class="card-title font-weight-bold">Student Health Records</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr class="bg-light text-muted small text-uppercase">
                            <th class="pl-4">Student</th>
                            <th>Class</th>
                            <th>Blood</th>
                            <th>Medical Conditions</th>
                            <th>Allergies</th>
                            <th>Medications</th>
                            <th>Emergency Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $s)
                            <tr>
                                <td class="pl-4">
                                    <span class="font-weight-bold text-dark">{{ $s->full_name }}</span>
                                    <br><small class="text-muted">{{ $s->admission_no }}</small>
                                </td>
                                <td><small>{{ $s->class_info }}</small></td>
                                <td>
                                    @if($s->blood_group)
                                        <span class="badge badge-danger">{{ $s->blood_group }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($s->medical_conditions)
                                        <span class="text-danger font-weight-bold">{{ \Illuminate\Support\Str::limit($s->medical_conditions, 60) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($s->allergies)
                                        <span class="text-warning font-weight-bold">{{ \Illuminate\Support\Str::limit($s->allergies, 60) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($s->medications)
                                        <span class="text-info font-weight-bold">{{ \Illuminate\Support\Str::limit($s->medications, 60) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $s->emergency_contact_name ?? '—' }}</small>
                                    <br><small class="text-muted">{{ $s->emergency_contact ?? '—' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-check-circle fa-2x text-success d-block mb-2"></i>
                                    No students with recorded medical conditions, allergies, or medications.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .text-pink { color: #ec4899; }
    .stat-card {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
        padding: 1rem 1.25rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid;
    }
    .stat-icon { font-size: 1.5rem; }
    .stat-value { font-size: 1.15rem; font-weight: 800; color: #1e293b; }
    .stat-label { font-size: 0.72rem; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; }
    @media print {
        .btn, .main-header, .main-sidebar { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
    }
</style>
@endsection
