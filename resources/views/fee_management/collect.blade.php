@extends('layouts.app')

@section('content')
<div class="collect-fees-wrap">
    @include('adminlte-templates::common.errors')

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-emerald-light text-emerald">
                <i class="fas fa-cash-register"></i>
            </div>
            <div>
                <h1 class="page-title mb-0">Collect Fees</h1>
                <p class="page-subtitle mb-0">Find a student by admission number or name to record a payment</p>
            </div>
        </div>
        <a href="{{ route('fees.dashboard') }}" class="btn-ghost-custom">
            <i class="fas fa-chart-pie me-1"></i> Dashboard
        </a>
    </div>

    <div class="collect-grid">
        {{-- Search Card --}}
        <div class="search-card">
            <div class="search-header">
                <i class="fas fa-search"></i>
                <span>Search Student</span>
            </div>
            <form action="{{ route('fees.collect') }}" method="GET" class="search-form">
                <div class="form-group">
                    <label for="q" class="form-label-custom">Admission Number / Student Name</label>
                    <input type="text" name="q" id="q" value="{{ request('q') }}" class="form-input-custom"
                        placeholder="e.g. ADM-1001 or Jane Doe">
                </div>

                <div class="form-group">
                    <label for="class_id" class="form-label-custom">Class</label>
                    <select name="class_id" id="class_id" class="form-select-custom">
                        <option value="">All Classes</option>
                        @foreach($classes as $id => $name)
                            <option value="{{ $id }}" @selected((string) request('class_id') === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-0">
                    <button type="submit" class="btn-submit w-100 justify-content-center">
                        <i class="fas fa-search me-2"></i> Search
                    </button>
                    @if(request()->filled('q') || request()->filled('class_id'))
                        <a href="{{ route('fees.collect') }}" class="btn-cancel d-block text-center mt-2">
                            Clear search
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Results --}}
        <div class="results-card">
            <div class="results-header">
                <i class="fas fa-user-graduate"></i>
                <span>
                    @if(request()->filled('q') || request()->filled('class_id'))
                        {{ $students->total() }} student{{ $students->total() !== 1 ? 's' : '' }} found
                    @else
                        Students
                    @endif
                </span>
            </div>

            <div class="results-body">
                @if(! request()->filled('q') && ! request()->filled('class_id'))
                    <div class="results-empty">
                        <i class="fas fa-search-plus"></i>
                        <p class="results-empty-title">Search for a student</p>
                        <p class="results-empty-sub">Type an admission number or a student's name, then pick the student to start collecting.</p>
                    </div>
                @elseif($students->isEmpty())
                    <div class="results-empty">
                        <i class="fas fa-user-slash"></i>
                        <p class="results-empty-title">No students found</p>
                        <p class="results-empty-sub">Try a different admission number or name.</p>
                    </div>
                @else
                    <div class="results-list">
                        @foreach($students as $student)
                            @php
                                $enrollment = $student->studentClassEnrollments->firstWhere('is_current', true)
                                    ?? $student->studentClassEnrollments->first();
                                $className = $enrollment?->classSection?->schoolClass?->name ?? '—';
                                $status = $student->payment_status;
                                $badge = match ($status) {
                                    'Paid' => 'badge-paid',
                                    'Partial' => 'badge-partial',
                                    'Unpaid' => 'badge-unpaid',
                                    default => 'badge-none',
                                };
                            @endphp
                            <div class="student-row">
                                <div class="student-profile">
                                    @if($student->photo_url)
                                        <img src="{{ $student->photo_url }}" class="student-photo" alt="">
                                    @else
                                        <div class="student-photo-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                    <div class="student-info">
                                        <span class="student-name">{{ $student->full_name }}</span>
                                        <span class="student-admission">
                                            {{ $student->admission_no }}
                                            <span class="student-class">{{ $className }}</span>
                                        </span>
                                        <span class="badge {{ $badge }}">{{ $status }}</span>
                                    </div>
                                </div>
                                <div class="student-balance">
                                    <span class="financial-label">Balance</span>
                                    <span class="financial-value {{ $student->balance_fee > 0 ? 'text-rose' : 'text-emerald' }}">
                                        KSh {{ number_format($student->balance_fee, 2) }}
                                    </span>
                                </div>
                                <a href="{{ route('fee-management.collect-payment', $student->student_id) }}"
                                   class="btn-collect">
                                    <i class="fas fa-cash-register me-2"></i> Collect
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="results-pagination">
                        {{ $students->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

<style>
:root {
    --indigo: #4f46e5;
    --indigo-light: #eef2ff;
    --amber: #f59e0b;
    --amber-light: #fffbeb;
    --emerald: #10b981;
    --emerald-light: #ecfdf5;
    --rose: #f43f5e;
    --rose-light: #fff1f2;
    --slate-50: #f8fafc;
    --slate-100: #f1f5f9;
    --slate-200: #e2e8f0;
    --slate-300: #cbd5e1;
    --slate-400: #94a3b8;
    --slate-500: #64748b;
    --slate-600: #475569;
    --slate-700: #334155;
    --slate-800: #1e293b;
    --slate-900: #0f172a;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
}

.collect-fees-wrap { padding: 1.5rem 2rem; background: #f9fafb; }

.page-title { font-size: 1.25rem; font-weight: 900; color: var(--slate-900); letter-spacing: -0.02em; }
.page-subtitle { color: var(--slate-400); font-size: 0.8rem; font-weight: 500; }

.icon-box { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.bg-emerald-light { background: var(--emerald-light); }
.text-emerald { color: var(--emerald); }
.text-rose { color: var(--rose); }

.btn-ghost-custom {
    display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 8px;
    font-size: 0.75rem; font-weight: 700; text-decoration: none !important; cursor: pointer;
    background: #fff; border: 1px solid var(--border); color: var(--slate-700); transition: all 160ms var(--ease-out);
}
.btn-ghost-custom:hover { background: var(--slate-100); }
.btn-ghost-custom:active { transform: scale(0.97); }

.collect-grid { display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; align-items: start; }

.search-card, .results-card {
    background: #fff; border-radius: 12px; border: 1px solid var(--border); overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.search-header, .results-header {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.875rem 1.25rem;
    border-bottom: 1px solid var(--border); background: var(--slate-50);
    font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--slate-400);
}
.search-header i { color: var(--indigo); font-size: 0.75rem; }
.results-header i { color: var(--emerald); font-size: 0.75rem; }
.search-form { padding: 1.25rem; }

.form-group { margin-bottom: 1.25rem; }
.form-label-custom { display: block; font-size: 0.75rem; font-weight: 700; color: var(--slate-600); margin-bottom: 6px; }

.form-select-custom {
    width: 100%; height: 40px; padding: 0 2.5rem 0 0.75rem; border-radius: 8px; border: 1px solid var(--border);
    font-size: 0.82rem; font-weight: 600; color: var(--slate-700); background: #fff;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 0.75rem center;
    transition: border-color 160ms var(--ease-out), box-shadow 160ms var(--ease-out);
}
.form-select-custom:focus { outline: none; border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

.form-input-custom {
    width: 100%; height: 40px; padding: 0 0.75rem; border-radius: 8px; border: 1px solid var(--border);
    font-size: 0.82rem; font-weight: 600; color: var(--slate-700); background: #fff;
    transition: border-color 160ms var(--ease-out), box-shadow 160ms var(--ease-out);
}
.form-input-custom:focus { outline: none; border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
.form-input-custom::placeholder { color: var(--slate-300); }

.btn-submit {
    display: inline-flex; align-items: center; padding: 0.75rem 2rem; border-radius: 8px;
    font-size: 0.85rem; font-weight: 800; border: none; cursor: pointer;
    background: var(--emerald); color: #fff; transition: all 160ms var(--ease-out);
}
.btn-submit:hover { background: #059669; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
.btn-submit:active { transform: scale(0.97); }
.btn-cancel { font-size: 0.78rem; font-weight: 600; color: var(--slate-400); text-decoration: none; transition: color 160ms var(--ease-out); }
.btn-cancel:hover { color: var(--indigo); }

.results-body { padding: 0.5rem 1.25rem 1.25rem; }
.results-list { display: flex; flex-direction: column; }
.student-row {
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    padding: 1rem 0; border-bottom: 1px solid var(--slate-100);
}
.student-row:last-child { border-bottom: none; }

.student-profile { display: flex; align-items: center; gap: 1rem; }
.student-photo { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid var(--slate-100); }
.student-photo-placeholder {
    width: 48px; height: 48px; border-radius: 50%; background: var(--slate-100);
    display: flex; align-items: center; justify-content: center; color: var(--slate-400); font-size: 1.1rem;
}
.student-info { display: flex; flex-direction: column; gap: 2px; }
.student-name { font-size: 0.95rem; font-weight: 800; color: var(--slate-900); }
.student-admission { font-size: 0.72rem; color: var(--slate-400); font-weight: 600; font-family: monospace; }
.student-class { color: var(--slate-500); font-family: inherit; font-size: 0.7rem; }

.badge {
    align-self: flex-start; margin-top: 2px; padding: 2px 8px; border-radius: 999px;
    font-size: 0.62rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em;
}
.badge-paid { background: var(--emerald-light); color: var(--emerald); }
.badge-partial { background: var(--amber-light); color: var(--amber); }
.badge-unpaid { background: var(--rose-light); color: var(--rose); }
.badge-none { background: var(--slate-100); color: var(--slate-400); }

.student-balance { display: flex; flex-direction: column; align-items: flex-end; gap: 2px; }
.financial-label { font-size: 0.7rem; font-weight: 600; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.04em; }
.financial-value { font-size: 0.9rem; font-weight: 800; font-family: monospace; }

.btn-collect {
    display: inline-flex; align-items: center; padding: 0.55rem 1.25rem; border-radius: 8px;
    font-size: 0.78rem; font-weight: 800; text-decoration: none !important; cursor: pointer;
    background: var(--indigo); color: #fff; border: none; white-space: nowrap; transition: all 160ms var(--ease-out);
}
.btn-collect:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }
.btn-collect:active { transform: scale(0.97); }

.results-empty { text-align: center; padding: 3.5rem 1rem; }
.results-empty i { font-size: 2rem; color: var(--slate-200); margin-bottom: 0.75rem; }
.results-empty-title { font-size: 0.95rem; font-weight: 800; color: var(--slate-700); margin: 0; }
.results-empty-sub { font-size: 0.78rem; color: var(--slate-400); font-weight: 500; margin: 4px 0 0; }

.results-pagination { display: flex; justify-content: flex-end; padding-top: 1rem; }
.results-pagination .pagination { margin-bottom: 0; }
.results-pagination .page-link { font-size: 0.78rem; color: var(--indigo); }

@media (max-width: 1024px) {
    .collect-grid { grid-template-columns: 1fr; }
    .student-row { flex-wrap: wrap; }
}
</style>
