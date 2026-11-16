@extends('layouts.app')

@section('content')
<div class="dash-wrap d-flex align-items-center justify-content-center" style="min-height: 80vh;">
    <div class="dash-panel" style="max-width: 650px; width: 100%;">
        <div class="dash-panel-header">
            <div class="d-flex align-items-center gap-2">
                <div class="icon-wrap bg-amber-light text-amber">
                    <i class="fas fa-edit"></i>
                </div>
                <h3 class="dash-panel-title">Edit Subject</h3>
            </div>
            <a href="{{ route('subjects.index') }}" class="btn-dash btn-ghost py-1 px-2">
                <i class="fas fa-times"></i>
            </a>
        </div>

        {!! Form::model($subject, ['route' => ['subjects.update', $subject->subject_id], 'method' => 'patch']) !!}
        <div class="dash-panel-body p-4">
            @include('adminlte-templates::common.errors')
            
            <div class="row">
                @include('subjects.fields')
            </div>
        </div>

        <div class="dash-panel-footer p-4 border-top bg-light-soft d-flex justify-content-end gap-2">
            <a href="{{ route('subjects.index') }}" class="btn-dash btn-ghost">
                Cancel
            </a>
            <button type="submit" class="btn-dash btn-amber-dash">
                Update Subject
            </button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<style>
/* ── Emil Kowalski Utility Suite ── */
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --slate: #64748b; --slate-light: #f1f5f9;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 2rem; background: #fafafa; }
.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
.dash-panel-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
.dash-panel-title { font-size: 1rem; font-weight: 700; color: var(--text); margin: 0; }
.icon-wrap { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }

/* Form Controls */
.dash-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--slate); margin-bottom: 0.5rem; display: block; }
.dash-control { border: 1px solid var(--border); border-radius: 8px; padding: 0.625rem 0.875rem; font-size: 0.875rem; transition: all 150ms var(--ease-out); color: var(--text); background-color: #fff; }
.dash-control:focus { border-color: var(--amber); box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1); outline: none; }

/* Buttons */
.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .625rem 1.25rem; border-radius: 8px; font-size: .813rem; font-weight: 600; transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer; }
.btn-amber-dash { background: var(--amber); color: #fff; }
.btn-amber-dash:hover { background: #d97706; transform: translateY(-1px); }
.btn-ghost { background: transparent; color: var(--muted); border-color: var(--border); }
.btn-ghost:hover { background: var(--slate-light); color: var(--text); }

.bg-light-soft { background-color: #f8fafc; }
.gap-2 { gap: 0.5rem; }
</style>
@endsection
