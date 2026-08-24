@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER & ACTIONS --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('teacher-management.index') }}" class="action-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="dash-heading mb-0">Teacher Profile</h1>
                <p class="dash-sub mb-0">Detailed view for {{ $teacher->full_name }}</p>
            </div>
        </div>
        <div class="d-flex gap-3">
            <a href="{{ route('teacher-management.edit', $teacher->staff_id) }}" class="btn-dash btn-ghost px-4">
                <i class="far fa-edit" style="margin-right: 10px; color: var(--indigo);"></i> Edit Profile
            </a>
            <button onclick="window.print()" class="btn-dash btn-ghost px-4">
                <i class="fas fa-print" style="margin-right: 10px; color: var(--rose);"></i> Print Dossier
            </button>
        </div>
    </div>

    <div class="row g-4">
        {{-- ② PROFILE SIDEBAR --}}
        <div class="col-xl-3">
            <div class="dash-panel border-0 shadow-sm text-center p-4 h-100">
                <div class="profile-photo-wrap mb-4 mx-auto">
                    @if($teacher->photo_url)
                        <img src="{{ $teacher->photo_url }}" alt="Teacher Photo" class="profile-photo shadow-sm">
                    @else
                        <div class="profile-initials shadow-sm">
                            {{ strtoupper(substr($teacher->first_name, 0, 1)) }}{{ strtoupper(substr($teacher->last_name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="status-indicator {{ $teacher->employment_status === 'active' ? 'active' : '' }}"></div>
                </div>

                <h4 class="profile-name mb-1">{{ $teacher->full_name }}</h4>
                <div class="badge-soft bg-indigo-light text-indigo px-3 py-1 rounded-pill fs-xs fw-800 mb-4 text-uppercase">
                    {{ $teacher->designation ?: 'Teacher' }}
                </div>

                <div class="divider mb-4"></div>

                <div class="text-start ps-2">
                    <div class="sidebar-item mb-4">
                        <label class="sidebar-label">Employee ID</label>
                        <div class="sidebar-value">{{ $teacher->employee_number ?: 'N/A' }}</div>
                    </div>
                    <div class="sidebar-item mb-4">
                        <label class="sidebar-label">TSC Number</label>
                        <div class="sidebar-value">{{ $teacher->tsc_number ?: 'N/A' }}</div>
                    </div>
                    <div class="sidebar-item mb-0">
                        <label class="sidebar-label">Department</label>
                        <div class="sidebar-value">{{ $teacher->department->name ?? 'Unassigned' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ③ MAIN CONTENT AREA --}}
        <div class="col-xl-9">
            <div class="dash-panel border-0 shadow-sm p-0 h-100">
                <div class="panel-nav border-bottom px-4" style="background: #fafbfc;">
                    <div class="d-flex" style="gap: 50px;">
                        <button class="nav-tab active" data-target="personal" style="padding: 1.25rem 0.5rem;">
                            <i class="fas fa-user-circle" style="margin-right: 10px;"></i> Personal Information
                        </button>
                        <button class="nav-tab" data-target="professional" style="padding: 1.25rem 0.5rem;">
                            <i class="fas fa-briefcase" style="margin-right: 10px;"></i> Professional Details
                        </button>
                        <button class="nav-tab" data-target="contact" style="padding: 1.25rem 0.5rem;">
                            <i class="fas fa-address-book" style="margin-right: 10px;"></i> Contact & Residence
                        </button>
                    </div>
                </div>

                <div class="panel-content p-4">
                    {{-- Personal Section --}}
                    <div class="tab-section" id="personal">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="detail-label">Gender</label>
                                <div class="detail-value text-capitalize">{{ $teacher->gender ?: '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="detail-label">Date of Birth</label>
                                <div class="detail-value">{{ $teacher->date_of_birth ? $teacher->date_of_birth->format('M d, Y') : '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="detail-label">Employment Status</label>
                                <div class="detail-value text-capitalize">{{ str_replace('_', ' ', $teacher->employment_status ?: '—') }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Professional Section --}}
                    <div class="tab-section d-none" id="professional">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="detail-label">Joining Date</label>
                                <div class="detail-value">{{ $teacher->date_of_joining ? $teacher->date_of_joining->format('M d, Y') : '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="detail-label">Employment Type</label>
                                <div class="detail-value text-capitalize">{{ str_replace('_', ' ', $teacher->employment_type ?: '—') }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="detail-label">Designation</label>
                                <div class="detail-value">{{ $teacher->designation ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="detail-label">Job Position</label>
                                <div class="detail-value">{{ $teacher->jobPosition->name ?? '—' }}</div>
                            </div>
                            <div class="col-md-12">
                                <label class="detail-label">Highest Qualification</label>
                                <div class="detail-value">{{ $teacher->qualification ?: '—' }}</div>
                            </div>
                            <div class="col-md-12">
                                <label class="detail-label">TSC Number</label>
                                <div class="detail-value">{{ $teacher->tsc_number ?: '—' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Contact Section --}}
                    <div class="tab-section d-none" id="contact">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="detail-label">Work Email</label>
                                <div class="detail-value text-lowercase">{{ $teacher->work_email ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="detail-label">Personal Email</label>
                                <div class="detail-value text-lowercase">{{ $teacher->personal_email ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="detail-label">Primary Phone</label>
                                <div class="detail-value">{{ $teacher->phone_primary ?: '—' }}</div>
                            </div>
                            <div class="col-md-12">
                                <label class="detail-label">Current Residence</label>
                                <div class="detail-value">{{ $teacher->current_address ?: '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="detail-label">City / Town</label>
                                <div class="detail-value">{{ $teacher->city ?: '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="detail-label">County / State</label>
                                <div class="detail-value">{{ $teacher->county ?: '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="detail-label">Country</label>
                                <div class="detail-value">{{ $teacher->country ?: '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Teacher Profile System ── */
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --slate: #64748b; --slate-light: #f1f5f9;
    --text: #0f172a; --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 2rem; background: #fafafa; min-height: 100vh; }
.dash-heading { font-size: 1.75rem; font-weight: 850; color: var(--text); letter-spacing: -0.04em; }
.dash-sub { font-size: 0.938rem; color: var(--muted); }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 16px; overflow: hidden; }

.profile-photo-wrap { width: 120px; height: 120px; position: relative; }
.profile-photo { width: 100%; height: 100%; border-radius: 32px; object-fit: cover; border: 4px solid #fff; }
.profile-initials { width: 100%; height: 100%; border-radius: 32px; background: var(--indigo-light); color: var(--indigo); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 850; border: 4px solid #fff; }
.status-indicator { position: absolute; bottom: 5px; right: 5px; width: 22px; height: 22px; border: 4px solid #fff; border-radius: 50%; background: #cbd5e1; }
.status-indicator.active { background: #10b981; }

.profile-name { font-size: 1.25rem; font-weight: 850; color: var(--text); letter-spacing: -0.02em; }
.divider { height: 1px; background: var(--border); }

.sidebar-label { font-size: 0.65rem; font-weight: 800; color: var(--slate); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.25rem; }
.sidebar-value { font-size: 0.875rem; font-weight: 700; color: var(--text); }

.panel-nav { display: flex; background: #fff; }
.nav-tab { background: transparent; border: none; padding: 1.25rem 0; font-size: 0.875rem; font-weight: 750; color: var(--muted); position: relative; transition: all 200ms var(--ease-out); cursor: pointer; }
.nav-tab.active { color: var(--indigo); }
.nav-tab.active::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 2px; background: var(--indigo); }
.nav-tab:hover:not(.active) { color: var(--text); }

.detail-label { font-size: 0.75rem; font-weight: 700; color: var(--slate); margin-bottom: 0.375rem; display: block; }
.detail-value { font-size: 0.938rem; font-weight: 600; color: var(--text); background: var(--slate-light); padding: 0.625rem 1rem; border-radius: 10px; border: 1px solid #f1f5f9; }

.action-btn { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--slate); border: 1px solid var(--border); background: #fff; transition: all 150ms ease; text-decoration: none !important; }
.action-btn:hover { background: var(--slate-light); color: var(--text); }

.btn-dash { display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 0.875rem; font-weight: 750; transition: all 200ms var(--ease-out); text-decoration: none !important; }
.btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text); padding: 0.625rem 1.25rem; }
.btn-ghost:hover { background: var(--slate-light); border-color: #cbd5e1; }

.badge-soft { border-radius: 6px; }
.fs-xs { font-size: 0.7rem; }
.fw-800 { font-weight: 800; }

@media print {
    .dash-wrap { padding: 0; background: #fff; }
    .action-btn, .btn-dash, .panel-nav { display: none !important; }
    .tab-section { display: block !important; margin-bottom: 2rem; border-bottom: 1px solid #eee; padding-bottom: 1rem; }
}
</style>

@push('page_scripts')
<script>
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const target = tab.getAttribute('data-target');
            document.querySelectorAll('.tab-section').forEach(sec => {
                sec.classList.add('d-none');
            });
            document.getElementById(target).classList.remove('d-none');
        });
    });
</script>
@endpush
@endsection
