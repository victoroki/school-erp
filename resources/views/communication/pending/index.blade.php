@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Pending Confirmations</h1>
                <p class="text-muted">Review and send notifications that require manual approval</p>
            </div>
            <div class="col-sm-6 text-right">
                @if($counts['total'] > 0)
                    <form action="{{ route('communication.pending.bulk-confirm') }}" method="POST" class="d-inline" id="bulkConfirmForm">
                        @csrf
                        <input type="hidden" name="ids[]" id="bulkConfirmIds" value="">
                        <button type="submit" class="btn btn-success" id="bulkConfirmBtn" disabled>
                            <i class="fas fa-paper-plane me-1"></i> Confirm Selected
                        </button>
                    </form>
                    <form action="{{ route('communication.pending.bulk-discard') }}" method="POST" class="d-inline" id="bulkDiscardForm">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="ids[]" id="bulkDiscardIds" value="">
                        <button type="submit" class="btn btn-danger" id="bulkDiscardBtn" disabled>
                            <i class="fas fa-trash me-1"></i> Discard Selected
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</section>

<div class="content px-3">
    @include('flash::message')

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number">{{ $counts['total'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-sms"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">SMS Pending</span>
                    <span class="info-box-number">{{ $counts['sms'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-envelope"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Email Pending</span>
                    <span class="info-box-number">{{ $counts['email'] }}</span>
                </div>
            </div>
        </div>
    </div>

    @forelse($pending as $item)
        <div class="card card-outline card-secondary mb-3 pending-card" data-id="{{ $item->id }}">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox" class="pending-checkbox mr-2" data-id="{{ $item->id }}">
                        <span class="badge badge-{{ $item->channel === 'sms' ? 'info' : 'success' }} me-2">
                            {{ strtoupper($item->channel) }}
                        </span>
                        <span class="badge badge-warning me-2">{{ ucfirst(str_replace('_', ' ', $item->trigger_type)) }}</span>
                        <strong class="text-dark">{{ $item->recipient_name }}</strong>
                        <small class="text-muted">({{ $item->student_name }})</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-muted">{{ $item->created_at->diffForHumans() }}</small>
                        <form action="{{ route('communication.pending.confirm', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="fas fa-check me-1"></i> Send
                            </button>
                        </form>
                        <form action="{{ route('communication.pending.discard', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times me-1"></i> Discard
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body py-2 px-4">
                <div class="bg-light rounded p-3">
                    <small class="text-muted d-block mb-1">To: {{ $item->contact }}</small>
                    @if($item->subject)
                        <strong>{{ $item->subject }}</strong><br>
                    @endif
                    <span class="text-dark">{!! nl2br(e($item->rendered_body)) !!}</span>
                </div>
            </div>
        </div>
    @empty
        <div class="card card-outline">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5 class="text-muted">All caught up!</h5>
                <p class="text-muted">No pending confirmations at this time.</p>
            </div>
        </div>
    @endforelse

    <div class="d-flex justify-content-center">
        {{ $pending->links() }}
    </div>
</div>
@endsection

@push('page_scripts')
<script>
$(document).ready(function() {
    function updateBulkButtons() {
        var selected = [];
        $('.pending-checkbox:checked').each(function() {
            selected.push($(this).data('id'));
        });
        var ids = selected.join(',');
        $('#bulkConfirmIds').val(ids);
        $('#bulkDiscardIds').val(ids);
        $('#bulkConfirmBtn').prop('disabled', selected.length === 0);
        $('#bulkDiscardBtn').prop('disabled', selected.length === 0);
    }

    $(document).on('change', '.pending-checkbox', updateBulkButtons);

    $('#bulkConfirmForm, #bulkDiscardForm').on('submit', function(e) {
        if (!$(this).find('input[name="ids[]"]').val()) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
