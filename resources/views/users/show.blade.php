@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">User Details</h1>
            <p class="dash-sub">{{ $user->name }}'s account information</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <a class="btn-dash btn-ghost" href="{{ route('users.index') }}">
                <i class="fas fa-arrow-left me-1"></i> Back to Users
            </a>
        </div>
    </div>

    <div class="dash-panel">
        <div class="dash-detail-body">
            <div class="dash-avatar">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="detail-list">
                <div class="detail-row">
                    <span class="detail-label">Name</span>
                    <span class="detail-value">{{ $user->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">{{ $user->email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Member Since</span>
                    <span class="detail-value">{{ $user->created_at->format('M j, Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Roles</span>
                    <span class="detail-value">
                        @forelse($user->roles as $role)
                            <span class="badge-role">{{ $role->role_name }}</span>
                        @empty
                            <span class="text-muted">No roles assigned</span>
                        @endforelse
                    </span>
                </div>
            </div>
        </div>
        <div class="dash-detail-footer d-flex justify-content-end gap-2">
            <a href="{{ route('users.edit', [$user->id]) }}" class="btn-dash btn-primary-dash">
                <i class="fas fa-edit me-1"></i> Edit User
            </a>
        </div>
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5;
    --blue: #3b82f6;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}
.dash-wrap { padding: 1rem; }
.dash-heading { font-size: 1.375rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.813rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }
.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden; }
.dash-detail-body { padding: 1.75rem; }
.dash-avatar { width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, var(--indigo), var(--blue)); color: #fff; font-size: 1.5rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; }
.detail-list { max-width: 480px; }
.detail-row { display: flex; align-items: center; justify-content: space-between; padding: .75rem 0; border-bottom: 1px solid #f8fafc; }
.detail-row:last-child { border-bottom: 0; }
.detail-label { font-size: .75rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
.detail-value { font-size: .875rem; font-weight: 600; color: var(--text); }
.badge-role { background: #eff6ff; color: #3b82f6; font-size: .625rem; font-weight: 800; padding: .15rem .45rem; border-radius: 6px; margin-right: .25rem; display: inline-block; }
.dash-detail-footer { padding: 1rem 1.75rem; background: #f8fafc; border-top: 1px solid var(--border); }
.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .4rem .875rem; border-radius: 8px; font-size: .813rem; font-weight: 600; transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer; }
.btn-primary-dash { background: var(--indigo); color: #fff; border-color: var(--indigo); }
.btn-primary-dash:hover { background: #4338ca; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
.btn-ghost { background: transparent; color: var(--muted); border-color: var(--border); }
.btn-ghost:hover { background: #f8fafc; color: var(--text); border-color: #cbd5e1; }
</style>
@endsection