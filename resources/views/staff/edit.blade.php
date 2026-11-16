@extends('layouts.app')

@section('content')
<div class="dash-wrap d-flex align-items-center justify-content-center" style="min-height: 85vh;">
    <div class="dash-panel" style="max-width: 900px; width: 100%;">
        <div class="dash-panel-header px-4 py-3 border-bottom d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-wrap bg-amber-light text-amber">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div>
                    <h3 class="dash-panel-title">Update Staff Record</h3>
                    <p class="text-muted small mb-0">Modify professional and personal details for this employee.</p>
                </div>
            </div>
            <a href="{{ route('staff.index') }}" class="btn-dash btn-ghost py-1 px-2">
                <i class="fas fa-times"></i>
            </a>
        </div>

        {!! Form::model($staff, ['route' => ['staff.update', $staff->staff_id], 'method' => 'patch', 'files' => true]) !!}
        <div class="dash-panel-body p-4">
            @include('adminlte-templates::common.errors')
            
            <div class="row">
                @include('staff.fields')
            </div>
        </div>

        <div class="dash-panel-footer p-4 border-top bg-light-soft d-flex justify-content-end gap-2">
            <a href="{{ route('staff.index') }}" class="btn-dash btn-ghost">
                Discard Changes
            </a>
            <button type="submit" class="btn-dash btn-amber-dash px-4">
                Update Personnel
            </button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<style>
/* ── Emil Kowalski Edit Suite ── */
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --slate: #64748b; --slate-light: #f1f5f9;
    --text: #0f172a; --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 2rem; background: #fafafa; }
.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 16px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
.dash-panel-title { font-size: 1.125rem; font-weight: 850; color: var(--text); margin: 0; letter-spacing: -0.02em; }

.icon-wrap { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }

/* Buttons */
.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .625rem 1.25rem; border-radius: 10px; font-size: .875rem; font-weight: 750; transition: all 200ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; }
.btn-amber-dash { background: var(--amber); color: #fff; }
.btn-amber-dash:hover { background: #d97706; transform: translateY(-1px); }
.btn-ghost { background: transparent; color: var(--muted); border-color: var(--border); }
.btn-ghost:hover { background: var(--slate-light); color: var(--text); }

.bg-light-soft { background-color: #fcfcfd; }
</style>
@endsection
