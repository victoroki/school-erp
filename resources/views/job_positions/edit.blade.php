@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- Header Section --}}
    <div class="row align-items-center mb-5">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-box bg-amber-light text-amber shadow-sm">
                    <i class="fas fa-edit"></i>
                </div>
                <div>
                    <h1 class="dash-heading mb-0">Modify Role</h1>
                    <p class="dash-sub mb-0">Updating specifications for <span class="text-dark fw-bold">{{ $jobPosition->title }}</span></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('job-positions.index') }}" class="btn-dash btn-ghost">
                <i class="fas fa-arrow-left me-2"></i> Back to Positions
            </a>
        </div>
    </div>

    @include('adminlte-templates::common.errors')

    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="dash-panel shadow-sm border-0 mb-5">
                <div class="dash-panel-header px-4 py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-850"><i class="fas fa-sliders-h me-2 text-muted"></i> Configuration</h5>
                </div>

                {!! Form::model($jobPosition, ['route' => ['job-positions.update', $jobPosition->position_id], 'method' => 'patch', 'class' => 'dash-form']) !!}
                    <div class="dash-panel-body p-4">
                        <div class="row g-4">
                            @include('job_positions.fields')
                        </div>
                    </div>

                    <div class="dash-panel-footer px-4 py-4 border-top bg-light-soft d-flex justify-content-end gap-2">
                        <a href="{{ route('job-positions.index') }}" class="btn-dash btn-ghost">Cancel</a>
                        <button type="submit" class="btn-dash btn-amber px-5">
                            <i class="fas fa-check-circle me-2"></i> Save Changes
                        </button>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --slate: #64748b; --slate-light: #f1f5f9;
    --text: #0f172a; --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 2.5rem; background: #fafafa; min-height: 100vh; }
.dash-heading { font-size: 1.875rem; font-weight: 850; color: var(--text); letter-spacing: -0.04em; }
.dash-sub { color: var(--muted); font-size: 0.9375rem; font-weight: 500; }

.icon-box { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
.dash-panel { background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid var(--border); }
.fw-850 { font-weight: 850; }

.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: 0.625rem 1.25rem; border-radius: 10px; font-size: 0.875rem; font-weight: 750; transition: all 200ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; }
.btn-amber { background: var(--amber); color: #fff; }
.btn-amber:hover { background: #d97706; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.2); }
.btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text); }
.btn-ghost:hover { background: var(--slate-light); }

.bg-amber-light { background-color: var(--amber-light); }
.text-amber { color: var(--amber); }
.bg-light-soft { background-color: #fcfcfd; }

/* Form Enhancements */
.form-group label { font-weight: 750; color: var(--text); margin-bottom: 0.5rem; font-size: 0.875rem; }
.form-control { border-radius: 10px; border: 1px solid var(--border); padding: 0.625rem 0.875rem; font-size: 0.9375rem; transition: all 150ms ease; }
.form-control:focus { border-color: var(--amber); box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1); }
</style>
@endsection
