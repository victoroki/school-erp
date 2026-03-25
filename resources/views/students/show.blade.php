@extends('layouts.app')

@push('page_css')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --font-main: 'Outfit', sans-serif;
        --bg-color: #f8fafc;
        --border-color: #f1f5f9;
        --text-color: #1e293b;
        --muted-color: #64748b;
    }
    body { font-family: var(--font-main); background-color: var(--bg-color); }
    
    .student-header-card {
        background: #fff;
        border-radius: 20px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        margin-bottom: 24px;
        margin-top: 10px;
        border: none;
    }

    .avatar-wrapper { position: relative; display: inline-block; }
    .avatar-wrapper img {
        width: 100px; height: 100px;
        border-radius: 20px;
        object-fit: cover;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .status-dot {
        position: absolute;
        bottom: -5px; right: -5px;
        width: 20px; height: 20px;
        background: #22c55e;
        border: 4px solid #fff;
        border-radius: 50%;
    }

    .student-name-title { font-size: 1.75rem; font-weight: 700; color: var(--text-color); margin-bottom: 0.25rem; }
    .student-meta { color: var(--muted-color); font-size: 0.9rem; font-weight: 500; }
    
    .badge-status { font-size: 0.65rem; font-weight: 700; padding: 6px 12px; border-radius: 50px; text-transform: uppercase; letter-spacing: 0.5px; margin-left: 8px; vertical-align: middle; }
    .badge-enrolled { background: #dcfce7; color: #166534; }
    .badge-active { background: #e0f2fe; color: #075985; }

    .btn-action-text { font-weight: 600; color: #0369a1; text-decoration: none; font-size: 0.9rem; }
    .btn-action-text:hover { color: #0284c7; text-decoration: none; }
    
    .btn-primary-custom { 
        background: #0369a1; color: white; border-radius: 12px; padding: 8px 24px; font-weight: 600; font-size: 0.9rem; border: none;
        transition: all 0.2s;
    }
    .btn-primary-custom:hover { background: #075985; color: white; transform: translateY(-1px); }

    .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
    .stat-box {
        background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        border-bottom: 4px solid transparent; display: flex; flex-direction: column; justify-content: space-between;
    }
    .stat-box.blue { border-bottom-color: #3b82f6; }
    .stat-box.green { border-bottom-color: #10b981; }
    .stat-box.orange { border-bottom-color: #f59e0b; }
    .stat-box.purple { border-bottom-color: #8b5cf6; }

    .stat-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;}
    .stat-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    .stat-title { font-size: 0.75rem; font-weight: 700; color: var(--muted-color); text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-value { font-size: 1.5rem; font-weight: 700; color: var(--text-color); line-height: 1; margin-bottom: 5px; }
    .stat-subtitle { font-size: 0.8rem; color: var(--muted-color); font-weight: 500; }

    .custom-tabs .nav-link {
        font-weight: 600; color: var(--muted-color); border: none !important; border-bottom: 2px solid transparent !important;
        padding: 12px 20px; transition: all 0.2s; border-radius: 0; background: transparent; font-size: 0.95rem;
    }
    .custom-tabs .nav-link:hover { color: #0369a1; }
    .custom-tabs .nav-link.active { color: #0369a1; border-bottom: 2px solid #0369a1 !important; font-weight: 700; }



    @media (max-width: 991px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 575px) {
        .stats-row { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="content px-4 py-4">
    @include('flash::message')
    
    <!-- Header Section -->
    <div class="student-header-card">
        <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-wrapper shadow-sm mr-2">
                    <img src="{{ $student->avatar_url }}" alt="Avatar">
                    @if($student->status === 'active')
                        <div class="status-dot"></div>
                    @endif
                </div>
                <div>
                    <div class="d-flex align-items-center flex-wrap">
                        <h1 class="student-name-title mb-0">{{ $student->full_name }}</h1>
                        <div>
                            @if($student->enrollment_status === 'enrolled')
                                <span class="badge badge-status badge-enrolled ml-3">ENROLLED</span>
                            @else
                                {!! $student->enrollment_status_badge !!}
                            @endif
                            @if($student->status === 'active')
                                <span class="badge badge-status badge-active">ACTIVE</span>
                            @else
                                {!! $student->status_badge !!}
                            @endif
                        </div>
                    </div>
                    <div class="student-meta mt-2 d-flex align-items-center">
                        <span>Student ID <span style="color:#0369a1; font-weight:700;">{{ $student->admission_no }}</span></span>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center mt-3 mt-md-0">
                <a href="{{ route('students.edit', $student->student_id) }}" class="btn-action-text mr-4"><i class="fas fa-pen mr-2"></i> Edit Profile</a>
                <a href="{{ route('students.id-card', $student->student_id) }}" class="btn-primary-custom text-decoration-none" target="_blank">
                    <i class="fas fa-print mr-2"></i> ID Card
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <!-- Age & Grade -->
        <div class="stat-box blue">
            <div class="stat-header">
                <div class="stat-icon" style="background:#eff6ff; color:#3b82f6;"><i class="fas fa-birthday-cake"></i></div>
                <div class="stat-title">STUDENT AGE</div>
            </div>
            <div>
                <div class="stat-value">{{ $student->age ?? 'N/A' }} Years Old</div>
                <div class="stat-subtitle">
                    @if($student->current_enrollment)
                        Grade {{ $student->current_enrollment->classSection->schoolClass->name ?? '' }}, Section {{ $student->current_enrollment->classSection->section->name ?? '' }}
                    @else
                        No active class
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Attendance -->
        <div class="stat-box green">
            <div class="stat-header">
                <div class="stat-icon" style="background:#ecfdf5; color:#10b981;"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-title">ATTENDANCE</div>
            </div>
            <div>
                <div class="stat-value">{{ $student->attendance_percentage }}%</div>
                <div class="stat-subtitle text-transparent" style="color:transparent; user-select:none;">-</div>
            </div>
        </div>

        <!-- Fee Balance -->
        <div class="stat-box orange">
            <div class="stat-header">
                <div class="stat-icon" style="background:#fffbeb; color:#f59e0b;"><i class="fas fa-wallet"></i></div>
                <div class="stat-title">FEE BALANCE</div>
            </div>
            <div>
                <div class="stat-value text-{{ $student->balance_fee > 0 ? 'dark' : 'success' }}">KES {{ number_format($student->balance_fee) }}</div>
                <div class="stat-subtitle {{ $student->balance_fee > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $student->balance_fee > 0 ? 'Due' : 'Cleared' }}
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="stat-box purple">
            <div class="stat-header">
                <div class="stat-icon" style="background:#f5f3ff; color:#8b5cf6;"><i class="fas fa-file-alt"></i></div>
                <div class="stat-title">DOCUMENTS</div>
            </div>
            <div>
                <div class="stat-value">{{ $student->studentDocuments->count() }}</div>
                <div class="stat-subtitle">Uploaded records</div>
            </div>
        </div>
    </div>

    <!-- Tabs Container -->
    <div style="background:transparent;">
        <ul class="nav nav-tabs custom-tabs mb-4 px-2" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#overview" role="tab" onclick="document.getElementById('active_tab_input').value='overview'">Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#academic" role="tab" onclick="document.getElementById('active_tab_input').value='academic'">Academic</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ session('active_tab') == 'family' ? 'active' : '' }}" data-toggle="tab" href="#family" role="tab" id="family-tab-link" onclick="document.getElementById('active_tab_input').value='family'">Family</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#medical" role="tab" onclick="document.getElementById('active_tab_input').value='medical'">Medical</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#disciplinary" role="tab" onclick="document.getElementById('active_tab_input').value='disciplinary'">Disciplinary</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#documents" role="tab" onclick="document.getElementById('active_tab_input').value='documents'">Documents</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#fees" role="tab" onclick="document.getElementById('active_tab_input').value='fees'">Fees</a>
            </li>
        </ul>

        <div class="tab-content" style="background:transparent; border:none; padding: 0;">
            <div class="tab-pane fade {{ session('active_tab') ? '' : 'show active' }}" id="overview" role="tabpanel">
                @include('students.tabs.overview')
            </div>
            <div class="tab-pane fade" id="academic" role="tabpanel">
                @include('students.tabs.academic')
            </div>
            <div class="tab-pane fade {{ session('active_tab') == 'family' ? 'show active' : '' }}" id="family" role="tabpanel">
                @include('students.tabs.family')
            </div>
            <div class="tab-pane fade" id="medical" role="tabpanel">
                @include('students.tabs.medical')
            </div>
            <div class="tab-pane fade" id="disciplinary" role="tabpanel">
                @include('students.tabs.disciplinary')
            </div>
            <div class="tab-pane fade" id="documents" role="tabpanel">
                @include('students.tabs.documents')
            </div>
            <div class="tab-pane fade" id="fees" role="tabpanel">
                @include('students.tabs.fees')
            </div>
        </div>
    </div>
</div>

<!-- Add Sibling Modal -->
<div class="modal fade" id="addSiblingModal" tabindex="-1" role="dialog" aria-labelledby="addSiblingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="addSiblingModalLabel" style="font-family: 'Outfit', sans-serif;">Add Sibling</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('students.add-sibling', $student->student_id) }}" method="POST">
                @csrf
                <div class="modal-body pt-3">
                    <div class="form-group">
                        <label for="sibling_id" style="font-weight:600; font-size: 0.9rem;">Select Sibling (Student)</label>
                        <!-- For simplicity in UI, building a select. Better logic would use Select2 ajax. -->
                        <select class="form-control" name="sibling_id" id="sibling_id" required>
                            <option value="">-- Choose Sibling --</option>
                            @foreach(\App\Models\Student::where('student_id', '!=', $student->student_id)->where('is_active', true)->get() as $s)
                                <option value="{{ $s->student_id }}">{{ $s->full_name }} ({{ $s->admission_no }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-2">
                        <label for="relationship_type" style="font-weight:600; font-size: 0.9rem;">Relationship Type</label>
                        <select class="form-control" name="relationship_type" id="relationship_type" required>
                            <option value="brother">Brother</option>
                            <option value="sister">Sister</option>
                        </select>
                    </div>
                    <div class="form-group form-check mt-3">
                        <input type="checkbox" class="form-check-input" id="is_twin" name="is_twin" value="1">
                        <label class="form-check-label" for="is_twin" style="font-weight: 500;">Are they twins?</label>
                    </div>
                    <div class="form-group">
                        <label for="notes" style="font-weight:600; font-size: 0.9rem;">Notes</label>
                        <textarea class="form-control" name="notes" id="notes" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill">Link Sibling</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('page_scripts')
<script>
    $(document    ).ready(function() {
        if('{{ session('active_tab') }}' == 'family') {
            $('#family-tab-link').tab('show');
        }
    });
</script>
@endpush

@endsection
