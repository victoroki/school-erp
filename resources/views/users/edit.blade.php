@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">Edit User</h1>
            <p class="dash-sub">Update {{ $user->name }}'s account details and roles</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <a class="btn-dash btn-ghost" href="{{ route('users.index') }}">
                <i class="fas fa-arrow-left me-1"></i> Back to Users
            </a>
        </div>
    </div>

    @include('adminlte-templates::common.errors')

    <div class="dash-panel">
        {!! Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'patch']) !!}
        <div class="dash-form-body">
            <div class="row">
                @include('users.fields')
            </div>
        </div>
        <div class="dash-form-footer d-flex justify-content-end gap-2">
            <a href="{{ route('users.index') }}" class="btn-dash btn-ghost">Cancel</a>
            {!! Form::submit('Save Changes', ['class' => 'btn-dash btn-primary-dash']) !!}
        </div>
        {!! Form::close() !!}
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}
.dash-wrap { padding: 1rem; }
.dash-heading { font-size: 1.375rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.813rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }
.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden; }
.dash-form-body { padding: 1.5rem 1.5rem 0; }
.dash-form-footer { padding: 1rem 1.5rem; background: #f8fafc; border-top: 1px solid var(--border); }
.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .4rem .875rem; border-radius: 8px; font-size: .813rem; font-weight: 600; transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer; }
.btn-primary-dash { background: var(--indigo); color: #fff; border-color: var(--indigo); }
.btn-primary-dash:hover { background: #4338ca; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
.btn-ghost { background: transparent; color: var(--muted); border-color: var(--border); }
.btn-ghost:hover { background: #f8fafc; color: var(--text); border-color: #cbd5e1; }

.form-group label { font-size: .75rem; font-weight: 700; color: var(--text); margin-bottom: .375rem; }
.form-control, .custom-control-label { font-size: .875rem; border: 1px solid var(--border); border-radius: 8px; padding: .5rem .75rem; }
.form-control:focus { border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12); }
.form-group h3 { font-size: .875rem; font-weight: 800; color: var(--text); margin-bottom: .75rem; }
.checkbox label { font-size: .838rem; color: var(--text); font-weight: 600; }
</style>
@endsection