@extends('layouts.app')

@section('content')
<style>
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --rose: #f43f5e; --rose-dark: #9f1239; --rose-line: #fecdd3;
    --text: #0f172a; --muted: #64748b; --border: #e2e8f0;
    --slate-light: #f1f5f9;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 1.5rem; background: #fafafa; min-height: 100vh; }
.dash-heading { font-size: 1.5rem; font-weight: 850; color: var(--text); letter-spacing: -0.04em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.875rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.dp-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.875rem 1.5rem; border-bottom: 1px solid var(--border); }
.dp-title { font-size: 0.813rem; font-weight: 750; color: var(--text); letter-spacing: -0.01em; }
.dp-hint { font-size: 0.7rem; font-weight: 700; color: var(--muted); letter-spacing: 0.04em; text-transform: uppercase; }
.dp-hint .req { color: var(--rose); }
.dp-body { padding: 1.5rem; }
.dp-foot { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.5rem; border-top: 1px solid var(--border); background: #fcfcfd; }
.dp-foot .dp-note { font-size: 0.75rem; color: #94a3b8; font-weight: 500; }

.page-icon { width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; background: var(--indigo-light); color: var(--indigo); }

.btn-dash { display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 0.813rem; font-weight: 750; transition: all 200ms var(--ease-out); text-decoration: none !important; }
.btn-primary-dash { background: var(--indigo); color: #fff; padding: 0.625rem 1.25rem; border: 1px solid var(--indigo); }
.btn-primary-dash:hover { background: #4338ca; border-color: #4338ca; color: #fff; }
.btn-primary-dash:focus { box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.16); outline: none; }
.btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text); padding: 0.5rem 1rem; }
.btn-ghost:hover { background: var(--slate-light); border-color: #cbd5e1; color: var(--text); }
.btn-ghost:focus { box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); outline: none; }

.error-panel { background: var(--rose-dark); border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; }
.error-panel .ep-title { display: flex; align-items: center; gap: 0.5rem; font-size: 0.813rem; font-weight: 750; color: #fff; margin-bottom: 0.625rem; }
.error-panel ul { margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat(auto-fit, minmax(15rem, 1fr)); gap: 0.25rem 1rem; }
.error-panel li { font-size: 0.75rem; font-weight: 500; color: #ffe4e6; }
.error-panel a { color: #fff; text-decoration: underline; text-underline-offset: 2px; }
.error-panel a:hover { color: #ffe4e6; }

.form-control, .custom-select, select.form-control {
    border-radius: 10px; border: 1px solid var(--border); padding: 0.5rem 0.75rem;
    font-size: 0.875rem; font-weight: 600; color: var(--text); transition: all 200ms var(--ease-out);
}
.form-control:focus, select.form-control:focus {
    border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); outline: none;
}
.form-control::placeholder { color: #94a3b8; font-weight: 500; }
.form-control.is-invalid { border-color: var(--rose); background-image: none; padding-right: 0.75rem; }
.form-control.is-invalid:focus { box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.10); }
</style>

<div class="dash-wrap">

    <div class="row align-items-center mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <span class="page-icon"><i class="fas fa-user-plus"></i></span>
                <div>
                    <h1 class="dash-heading">Create Staff</h1>
                    <p class="dash-sub">Add a personnel record to the staff directory.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a class="btn-dash btn-ghost px-3" href="{{ route('staff.index') }}">
                <i class="fas fa-arrow-left me-2"></i> Back to Staff
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="error-panel" role="alert">
            <div class="ep-title"><i class="fas fa-exclamation-triangle"></i> Fix these before saving</div>
            <ul>
                @foreach($errors->keys() as $errorKey)
                    <li><a href="#{{ $errorKey }}">{{ $errors->first($errorKey) }}</a></li>
                @endforeach
            </ul>
        </div>
    @endif

    {!! Form::open(['route' => 'staff.store', 'files' => true]) !!}

        <div class="dash-panel">
            <div class="dp-head">
                <span class="dp-title">Personnel Details</span>
                <span class="dp-hint"><span class="req">*</span> Required</span>
            </div>

            <div class="dp-body">
                <div class="row">
                    @include('staff.fields')
                </div>
            </div>

            <div class="dp-foot">
                <span class="dp-note">You can update any of these details later.</span>
                <div class="d-flex gap-2">
                    <a class="btn-dash btn-ghost px-4" href="{{ route('staff.index') }}">Cancel</a>
                    {!! Form::submit('Create Staff', ['class' => 'btn-dash btn-primary-dash px-4']) !!}
                </div>
            </div>
        </div>

    {!! Form::close() !!}

</div>
@endsection
