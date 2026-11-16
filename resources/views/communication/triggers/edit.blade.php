@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit Trigger: {{ $trigger->name }}</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('communication.triggers.index') }}" class="btn btn-default">Back to Triggers</a>
            </div>
        </div>
    </div>
</section>

<div class="content px-3">
    @include('flash::message')

    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Trigger Configuration</h3>
                </div>
                <form action="{{ route('communication.triggers.update', $trigger->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label>Trigger Type</label>
                            <input type="text" class="form-control" value="{{ $trigger->trigger_type }}" disabled>
                        </div>

                        <div class="form-group">
                            <label>Channel</label>
                            <select name="channel" class="form-control" required>
                                <option value="sms" {{ $trigger->channel === 'sms' ? 'selected' : '' }}>SMS Only</option>
                                <option value="email" {{ $trigger->channel === 'email' ? 'selected' : '' }}>Email Only</option>
                                <option value="both" {{ $trigger->channel === 'both' ? 'selected' : '' }}>SMS + Email</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Default Template</label>
                            <select name="default_template_id" class="form-control">
                                <option value="">Auto-detect from trigger type</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" {{ $trigger->default_template_id == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }} ({{ strtoupper($template->channel) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="is_enabled" value="0">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               name="is_enabled"
                                               id="is_enabled"
                                               value="1"
                                               {{ $trigger->is_enabled ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_enabled">Enable this trigger</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="hidden" name="requires_confirmation" value="0">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               name="requires_confirmation"
                                               id="requires_confirmation"
                                               value="1"
                                               {{ $trigger->requires_confirmation ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="requires_confirmation">Requires manual confirmation</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                        <a href="{{ route('communication.triggers.index') }}" class="btn btn-default ml-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle me-1"></i> About This Trigger</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">{{ $trigger->description ?? 'Configure when and how notifications are sent for this event type.' }}</p>
                    <hr>
                    <div class="small text-muted">
                        <strong>Phase A</strong> (requires confirmation): Medical Incident, Disciplinary<br>
                        <strong>Phase B</strong> (auto-send): Exam Published, Fee Reminder, Attendance
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
