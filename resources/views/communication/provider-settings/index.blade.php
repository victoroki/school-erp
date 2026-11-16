@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Provider Settings</h1>
                    <p class="text-muted">Configure SMS and email provider credentials</p>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-outline-info" id="testSmsBtn">
                        <i class="fas fa-paper-plane me-1"></i> Send Test SMS
                    </button>
                </div>
            </div>
    </div>
</section>

<div class="content px-3">
    @include('flash::message')

    {{-- Active Provider Selector --}}
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-toggle-on me-2"></i> Active SMS Provider</h3>
                </div>
                <form action="{{ route('communication.providers.set-active') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <p class="text-muted mb-3">Only one SMS provider can be active at a time. The selected provider will be used for all outgoing SMS.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" name="provider" id="provider_at" value="africastalking"
                                        {{ ($activeProvider ?? null) === 'africastalking' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="provider_at">
                                        <strong>Africa's Talking</strong>
                                        @if(($activeProvider ?? null) === 'africastalking')
                                            <span class="badge badge-success ml-1">Active</span>
                                        @elseif($smsSetting && $smsSetting->is_active)
                                            <span class="badge badge-info ml-1">Saved</span>
                                        @else
                                            <span class="badge badge-secondary ml-1">Not configured</span>
                                        @endif
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="custom-control custom-radio">
                                    <input type="radio" class="custom-control-input" name="provider" id="provider_sozuri" value="sozuri"
                                        {{ ($activeProvider ?? null) === 'sozuri' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="provider_sozuri">
                                        <strong>Sozuri</strong>
                                        @if(($activeProvider ?? null) === 'sozuri')
                                            <span class="badge badge-success ml-1">Active</span>
                                        @elseif($sozuriSetting && $sozuriSetting->is_active)
                                            <span class="badge badge-info ml-1">Saved</span>
                                        @else
                                            <span class="badge badge-secondary ml-1">Not configured</span>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-check me-1"></i> Set Active Provider</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Africa's Talking --}}
        <div class="col-md-6">
            <div class="card card-outline card-info {{ ($activeProvider ?? null) !== 'africastalking' ? 'opacity-75' : '' }}">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-sms me-2 text-info"></i> Africa's Talking
                        @if(($activeProvider ?? null) === 'africastalking')
                            <span class="badge badge-info ml-2">Active</span>
                        @endif
                    </h3>
                    <div class="card-tools">
                        @if($smsSetting && $smsSetting->last_tested_at)
                            <span class="badge badge-{{ $smsSetting->last_test_status === 'success' ? 'success' : 'danger' }}">
                                Last test: {{ $smsSetting->last_test_status }}
                            </span>
                        @endif
                    </div>
                </div>
                <form action="{{ route('communication.providers.update-sms') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="custom-control-input" name="is_active" id="sms_active" value="1"
                                    {{ ($smsSetting && $smsSetting->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sms_active">Enable Africa's Talking</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>API Key</label>
                            <input type="password" class="form-control" name="api_key"
                                   value="{{ $smsSetting ? $smsSetting->getMaskedCredential('api_key') : '' }}"
                                   placeholder="Your Africa's Talking API key">
                            <small class="text-muted">From <a href="https://account.africastalking.com" target="_blank">AT Dashboard</a></small>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" name="username"
                                   value="{{ $smsSetting->credentials['username'] ?? 'sandbox' }}">
                            <small class="text-muted">Use "sandbox" for testing</small>
                        </div>
                        <div class="form-group">
                            <label>Sender ID (Shortcode)</label>
                            <input type="text" class="form-control" name="sender_id"
                                   value="{{ $smsSetting->credentials['sender_id'] ?? '' }}"
                                   placeholder="e.g. 12345 or SchoolName">
                            <small class="text-muted">Registered alphanumeric ID or shortcode</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info"><i class="fas fa-save me-1"></i> Save Africa's Talking</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sozuri --}}
        <div class="col-md-6">
            <div class="card card-outline card-primary {{ ($activeProvider ?? null) !== 'sozuri' ? 'opacity-75' : '' }}">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-sms me-2 text-primary"></i> Sozuri
                        @if(($activeProvider ?? null) === 'sozuri')
                            <span class="badge badge-primary ml-2">Active</span>
                        @endif
                    </h3>
                    <div class="card-tools">
                        @if($sozuriSetting && $sozuriSetting->last_tested_at)
                            <span class="badge badge-{{ $sozuriSetting->last_test_status === 'success' ? 'success' : 'danger' }}">
                                Last test: {{ $sozuriSetting->last_test_status }}
                            </span>
                        @endif
                    </div>
                </div>
                <form action="{{ route('communication.providers.update-sozuri') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="custom-control-input" name="is_active" id="sozuri_active" value="1"
                                    {{ ($sozuriSetting && $sozuriSetting->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="sozuri_active">Enable Sozuri</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>API Key</label>
                            <input type="password" class="form-control" name="api_key"
                                   value="{{ $sozuriSetting ? $sozuriSetting->getMaskedCredential('api_key') : '' }}"
                                   placeholder="Your Sozuri API key">
                            <small class="text-muted">Bearer token from <a href="https://sozuri.net" target="_blank">Sozuri Dashboard</a></small>
                        </div>
                        <div class="form-group">
                            <label>Project Name</label>
                            <input type="text" class="form-control" name="project"
                                   value="{{ $sozuriSetting->credentials['project'] ?? '' }}"
                                   placeholder="e.g. my-school-project">
                            <small class="text-muted">Your Sozuri project identifier</small>
                        </div>
                        <div class="form-group">
                            <label>Sender ID (From)</label>
                            <input type="text" class="form-control" name="sender_id"
                                   value="{{ $sozuriSetting->credentials['sender_id'] ?? '' }}"
                                   placeholder="e.g. SchoolName">
                            <small class="text-muted">Must match a registered sender ID on Sozuri</small>
                        </div>
                        <div class="form-group">
                            <label>Message Type</label>
                            <select name="message_type" class="form-control">
                                <option value="transactional" {{ ($sozuriSetting->credentials['message_type'] ?? 'transactional') === 'transactional' ? 'selected' : '' }}>Transactional</option>
                                <option value="promotional" {{ ($sozuriSetting->credentials['message_type'] ?? '') === 'promotional' ? 'selected' : '' }}>Promotional</option>
                            </select>
                            <small class="text-muted">Must match the registered sender ID type</small>
                        </div>
                        <div class="form-group">
                            <label>Webhook Auth Key <small class="text-muted">(optional)</small></label>
                            <input type="password" class="form-control" name="auth_key"
                                   value="{{ $sozuriSetting && !empty($sozuriSetting->credentials['auth_key']) ? $sozuriSetting->getMaskedCredential('auth_key') : '' }}"
                                   placeholder="Optional pre-shared key for webhook verification">
                            <small class="text-muted">Used to verify delivery status callbacks from Sozuri</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Sozuri</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Daily Limit --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-shield-alt me-2 text-warning"></i> Cost Safeguards</h3>
                </div>
                <form action="{{ route('communication.providers.update-daily-limit') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>Daily SMS Send Limit</label>
                            <input type="number" class="form-control" name="daily_limit"
                                   value="{{ $dailyLimitSetting->credentials['limit'] ?? 500 }}"
                                   min="1" max="10000" required>
                            <small class="text-muted">Maximum SMS messages per day. Prevents runaway costs from trigger bugs.</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save me-1"></i> Update Limit</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Email Provider --}}
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-envelope me-2 text-success"></i> Email Provider (SMTP)
                    </h3>
                    <div class="card-tools">
                        @if($emailSetting && $emailSetting->last_tested_at)
                            <span class="badge badge-{{ $emailSetting->last_test_status === 'success' ? 'success' : 'danger' }}">
                                Last test: {{ $emailSetting->last_test_status }}
                            </span>
                        @endif
                    </div>
                </div>
                <form action="{{ route('communication.providers.update-email') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="custom-control-input" name="is_active" id="email_active" value="1"
                                    {{ ($emailSetting && $emailSetting->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="email_active">Enable Email Provider</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>SMTP Host</label>
                                    <input type="text" class="form-control" name="host"
                                           value="{{ $emailSetting->credentials['host'] ?? 'mailpit' }}"
                                           placeholder="smtp.gmail.com">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Port</label>
                                    <input type="number" class="form-control" name="port"
                                           value="{{ $emailSetting->credentials['port'] ?? '1025' }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" name="username"
                                   value="{{ $emailSetting->credentials['username'] ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" class="form-control" name="password"
                                   value="{{ $emailSetting ? $emailSetting->getMaskedCredential('password') : '' }}">
                        </div>
                        <div class="form-group">
                            <label>Encryption</label>
                            <select name="encryption" class="form-control">
                                <option value="tls" {{ ($emailSetting->credentials['encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($emailSetting->credentials['encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ ($emailSetting->credentials['encryption'] ?? '') === '' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label>From Address</label>
                                    <input type="email" class="form-control" name="from_address"
                                           value="{{ $emailSetting->credentials['from_address'] ?? 'noreply@example.com' }}">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>From Name</label>
                                    <input type="text" class="form-control" name="from_name"
                                           value="{{ $emailSetting->credentials['from_name'] ?? config('app.name') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Save Email Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Webhook URL Info --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-link me-2"></i> Webhook Configuration</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2">Configure these URLs in your Sozuri dashboard for delivery status callbacks:</p>
                    <div class="form-group">
                        <label>Sozuri Delivery Status Webhook</label>
                        <div class="input-group">
                            <input type="text" class="form-control font-weight-bold" readonly
                                   value="{{ route('communication.webhooks.sozuri') }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary copy-btn" data-target="input.readonly" type="button">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                        </div>
                        <small class="text-muted">Sozuri will POST delivery statuses here. Add your Auth Key above to verify incoming callbacks.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test SMS Modal -->
<div class="modal fade" id="testSmsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Test SMS</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Test message will be sent via
                    <strong>{{ ($activeProvider ?? 'africastalking') === 'sozuri' ? 'Sozuri' : "Africa's Talking" }}</strong>
                    (your active provider).
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" class="form-control" id="testSmsPhone" placeholder="07XX XXX XXX">
                    <small class="text-muted">Will be formatted to +254 format automatically</small>
                </div>
                <div id="testSmsResult" class="d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" id="sendTestSmsBtn">
                    <i class="fas fa-paper-plane me-1"></i> Send Test
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page_scripts')
<script>
    // jQuery ships in the deferred Vite module script, which executes AFTER
    // this inline block. Poll until it is ready - same strategy as the
    // AdminLTE initializer in layouts/app.blade.php.
    function initTestSmsPage() {
        if (typeof window.jQuery === 'undefined') {
            setTimeout(initTestSmsPage, 50);
            return;
        }
        var $ = window.jQuery;

        $('#testSmsBtn').on('click', function() {
            $('#testSmsResult').addClass('d-none');
            $('#testSmsPhone').val('');
            $('#testSmsModal').modal('show');
            $('#testSmsPhone').trigger('focus');
        });

        $('#testSmsPhone').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#sendTestSmsBtn').trigger('click');
            }
        });

        $('#sendTestSmsBtn').on('click', function() {
            var phone = $('#testSmsPhone').val();
            var btn = $(this);
            var result = $('#testSmsResult');

            if (!phone) {
                result.removeClass('d-none').html('<div class="alert alert-danger">Please enter a phone number.</div>');
                return;
            }

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Sending...');
            result.addClass('d-none');

            $.ajax({
                url: '{{ route("communication.providers.test-sms") }}',
                method: 'POST',
                data: {
                    phone: phone,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Send Test');
                    result.removeClass('d-none').html(
                        '<div class="alert alert-' + (response.success ? 'success' : 'danger') + '">' +
                        response.message + '</div>'
                    );
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Send Test');
                    var msg = 'Request failed.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                    }
                    result.removeClass('d-none').html('<div class="alert alert-danger">' + msg + '</div>');
                }
            });
        });

        $('.copy-btn').on('click', function() {
            var input = $(this).closest('.input-group').find('input');
            input.select();
            document.execCommand('copy');
            $(this).html('<i class="fas fa-check"></i> Copied');
            setTimeout(() => $(this).html('<i class="fas fa-copy"></i> Copy'), 2000);
        });
    }

    initTestSmsPage();
</script>
@endpush
