@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">System Users</h1>
            <p class="dash-sub">Manage user accounts and their assigned roles</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <a class="btn-dash btn-primary-dash" href="{{ route('users.create') }}">
                <i class="fas fa-plus me-1"></i> Add New User
            </a>
        </div>
    </div>

    @include('flash::message')

    {{-- ② USERS LIST --}}
    <div class="dash-panel">
        @include('users.table')
    </div>
</div>

{{-- ③ RESET PASSWORD MODAL --}}
<div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content reset-card">
            <div class="modal-header reset-card-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="reset-icon"><i class="fas fa-key"></i></span>
                    <div>
                        <h5 class="modal-title reset-title">Reset Password</h5>
                        <p class="reset-sub mb-0">Set a new password for <strong class="text-dark" id="resetPasswordUserName"></strong></p>
                    </div>
                </div>
                <button type="button" class="close text-muted" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="resetPasswordForm" method="POST" action="" autocomplete="off">
                @csrf
                @method('PATCH')
                <div class="modal-body reset-card-body">
                    <div class="alert alert-danger reset-alert d-none" id="resetPasswordError" role="alert"></div>

                    <div class="form-group mb-3">
                        <label for="resetPasswordNew" class="reset-label">New Password</label>
                        <input type="password" name="password" id="resetPasswordNew"
                               class="form-control reset-input" minlength="8" required placeholder="Minimum 8 characters">
                    </div>

                    <div class="form-group mb-0">
                        <label for="resetPasswordConfirm" class="reset-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="resetPasswordConfirm"
                               class="form-control reset-input" minlength="8" required placeholder="Re-enter the new password">
                    </div>

                    <p class="reset-hint mt-3 mb-0"><i class="fas fa-shield-alt mr-1"></i> The user will need this password for their next login.</p>
                </div>
                <div class="modal-footer reset-card-footer">
                    <button type="button" class="btn-dash btn-ghost" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-dash btn-primary-dash">
                        <i class="fas fa-check me-1"></i> Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* ── Emil Kowalski Utility Suite ── */
:root {
    --blue: #3b82f6;
    --indigo: #4f46e5;
    --emerald: #10b981;
    --slate: #64748b;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 1rem; }
.dash-heading { font-size: 1.375rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.813rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); overflow: hidden; }

/* Buttons */
.btn-dash { 
    display: inline-flex; align-items: center; justify-content: center; padding: .4rem .875rem; border-radius: 8px; font-size: .813rem; font-weight: 600; 
    transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer;
}
.btn-primary-dash { background: var(--indigo); color: #fff; border-color: var(--indigo); }
.btn-primary-dash:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
.btn-ghost { background: transparent; color: var(--muted); border-color: var(--border); }
.btn-ghost:hover { background: #f8fafc; color: var(--text); border-color: #cbd5e1; }

/* Table Styling */
.table { margin-bottom: 0; }
.table thead th { 
    background: #f8fafc; border-bottom: 1px solid var(--border); font-size: .688rem; font-weight: 800; 
    text-transform: uppercase; color: var(--slate); letter-spacing: 0.05em; padding: .5rem .75rem;
}
.table tbody td { padding: .5rem .75rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; border-top: 0; }
.table tbody tr:last-child td { border-bottom: 0; }
.table-hover tbody tr:hover { background-color: #f8fafc; }

.user-name { font-weight: 700; color: var(--text); font-size: .875rem; display: block; }
.user-email { font-size: .75rem; color: var(--muted); margin-top: .125rem; display: block; }

.badge-role { background: #eff6ff; color: #3b82f6; font-size: .625rem; font-weight: 800; padding: .15rem .45rem; border-radius: 6px; margin-right: .25rem; display: inline-block;}

/* Action Buttons */
.action-btn { 
    width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; 
    border-radius: 8px; color: var(--muted); transition: all 150ms ease; border: 1px solid transparent; background: transparent; font-size: .813rem;
}
.action-btn:hover { background: #f1f5f9; color: var(--text); border-color: #e2e8f0; }
.btn-delete:hover { background: #fee2e2; color: #ef4444; border-color: #fecaca; }

/* Reset Password Modal */
.reset-card { border: none; border-radius: 14px; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12); overflow: hidden; }
.reset-card-header { background: #fff; border-bottom: 1px solid #f8fafc; padding: 1.25rem 1.5rem; align-items: center; }
.reset-icon { width: 40px; height: 40px; border-radius: 10px; background: #eef2ff; color: var(--indigo); display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.reset-title { font-size: 1rem; font-weight: 800; color: var(--text); letter-spacing: -0.01em; }
.reset-sub { font-size: .75rem; color: var(--muted); font-weight: 500; }
.reset-card-body { padding: 1.5rem; }
.reset-label { font-size: .75rem; font-weight: 700; color: var(--text); margin-bottom: .375rem; display: block; }
.reset-input { border: 1px solid var(--border); border-radius: 8px; padding: .5rem .75rem; font-size: .875rem; }
.reset-input:focus { border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12); }
.reset-hint { font-size: .75rem; color: var(--muted); }
.reset-alert { font-size: .813rem; border-radius: 8px; border: 0; background: #fef2f2; color: #b91c1c; }
.reset-card-footer { background: #f8fafc; border-top: 1px solid var(--border); padding: .875rem 1.5rem; }
</style>

@push('page_scripts')
<script>
    $(function () {
        const $modal = $('#resetPasswordModal');
        const $form = $('#resetPasswordForm');
        const $errorBox = $('#resetPasswordError');

        $modal.on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const userId = button.data('user-id');
            const userName = button.data('user-name');

            $('#resetPasswordUserName').text(userName);
            $form.attr('action', '{{ url('users') }}/' + userId + '/reset-password');
            $form.find('input[type="password"]').val('');
            $errorBox.addClass('d-none').text('');
        });

        $form.on('submit', function (e) {
            e.preventDefault();

            const password = $('#resetPasswordNew').val();
            const confirmation = $('#resetPasswordConfirm').val();

            if (password.length < 8) {
                return showError('Password must be at least 8 characters.');
            }
            if (password !== confirmation) {
                return showError('The password confirmation does not match.');
            }

            $form.find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Resetting…');

            $.post({
                url: $form.attr('action'),
                data: $form.serialize(),
            }).done(function (data) {
                if (data.success) {
                    $modal.modal('hide');
                    location.reload();
                }
            }).fail(function (xhr) {
                const data = xhr.responseJSON || {};
                if (data.errors) {
                    const flat = Object.values(data.errors).flat();
                    return showError(flat.join(' '));
                }
                showError('Something went wrong. Please try again.');
            }).always(function () {
                $form.find('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-check me-1"></i> Reset Password');
            });

            function showError(msg) {
                $errorBox.removeClass('d-none').text(msg);
                return false;
            }
        });
    });
</script>
@endpush
@endsection
