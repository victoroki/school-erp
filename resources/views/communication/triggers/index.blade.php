@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Auto Triggers</h1>
                <p class="text-muted">Configure automatic notification triggers for school events</p>
            </div>
        </div>
    </div>
</section>

<div class="content px-3">
    @include('flash::message')

    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bolt text-warning me-2"></i>Notification Triggers</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light text-muted small text-uppercase fw-bold">
                            <th class="ps-4 py-3">Trigger</th>
                            <th class="py-3">Channel</th>
                            <th class="py-3">Template</th>
                            <th class="py-3">Mode</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($triggers as $trigger)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="icon-sm bg-indigo-light text-indigo rounded-circle">
                                            <i class="fas {{ match($trigger->trigger_type) {
                                                'medical_incident' => 'fa-heartbeat',
                                                'disciplinary' => 'fa-gavel',
                                                'exam_published' => 'fa-graduation-cap',
                                                'fee_reminder' => 'fa-money-bill-wave',
                                                'attendance_absence' => 'fa-user-clock',
                                                'manual' => 'fa-paper-plane',
                                                default => 'fa-bell'
                                            } }}"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $trigger->name }}</div>
                                            <small class="text-muted">{{ $trigger->description ?? $trigger->trigger_type }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $trigger->channel === 'sms' ? 'info' : ($trigger->channel === 'email' ? 'success' : 'primary') }}">
                                        {{ strtoupper($trigger->channel) }}
                                    </span>
                                </td>
                                <td>
                                    @if($trigger->defaultTemplate)
                                        <span class="text-dark">{{ $trigger->defaultTemplate->name }}</span>
                                    @else
                                        <span class="text-muted fst-italic">No default template</span>
                                    @endif
                                </td>
                                <td>
                                    @if($trigger->requires_confirmation)
                                        <span class="badge badge-warning"><i class="fas fa-hand-paper me-1"></i>Manual Confirm</span>
                                    @else
                                        <span class="badge badge-success"><i class="fas fa-bolt me-1"></i>Auto Send</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox"
                                               class="custom-control-input trigger-toggle"
                                               id="toggle_{{ $trigger->id }}"
                                               data-id="{{ $trigger->id }}"
                                               {{ $trigger->is_enabled ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="toggle_{{ $trigger->id }}"></label>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('communication.triggers.edit', $trigger->id) }}"
                                       class="btn btn-xs btn-outline-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No triggers configured.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page_scripts')
<script>
$(document).ready(function() {
    $('.trigger-toggle').on('change', function() {
        var id = $(this).data('id');
        var isEnabled = $(this).is(':checked');
        var toggle = $(this);

        toggle.prop('disabled', true);

        $.ajax({
            url: '/communication/triggers/' + id + '/toggle',
            method: 'POST',
            data: {
                is_enabled: isEnabled ? 1 : 0,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                toggle.prop('disabled', false);
                if (!response.success) {
                    toggle.prop('checked', !isEnabled);
                }
            },
            error: function() {
                toggle.prop('checked', !isEnabled);
                toggle.prop('disabled', false);
            }
        });
    });
});
</script>
@endpush
