<!-- Overview -->
<div class="detail-card mb-3">
    <div class="detail-card-head">
        <i class="fas fa-info-circle mr-2"></i> Overview
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Category</span>
        <span class="detail-row-value">{{ $expenses->category->name ?? 'N/A' }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Supplier / Payee</span>
        <span class="detail-row-value">{{ $expenses->supplier ? $expenses->supplier->name : '—' }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Payment Method</span>
        <span class="detail-row-value">{{ ucfirst(str_replace('_', ' ', $expenses->payment_method ?? '')) ?: '—' }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Bank Account</span>
        <span class="detail-row-value">{{ $expenses->bankAccount ? $expenses->bankAccount->account_name : '—' }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Reference Number</span>
        <span class="detail-row-value">{{ $expenses->reference_number ?? '—' }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Receipt Number</span>
        <span class="detail-row-value">{{ $expenses->receipt_number ?? '—' }}</span>
    </div>
</div>

<!-- Description -->
<div class="detail-card">
    <div class="detail-card-head">
        <i class="fas fa-align-left mr-2"></i> Description / Purpose
    </div>
    <div class="detail-card-body">
        <p class="detail-description">{{ $expenses->description ?? 'No description provided.' }}</p>
        @if($expenses->attachment)
            <a href="{{ \Illuminate\Support\Facades\Storage::url($expenses->attachment) }}" class="detail-attachment" target="_blank">
                <i class="fas fa-paperclip mr-1"></i> View Attachment
            </a>
        @endif
    </div>
</div>