<!-- Overview -->
<div class="detail-card mb-3">
    <div class="detail-card-head">
        <i class="fas fa-info-circle mr-2"></i> Overview
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Category</span>
        <span class="detail-row-value">{{ $income->category ? $income->category->name : 'N/A' }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Payer / Source</span>
        <span class="detail-row-value">{{ $income->payer_name ?? 'General Source' }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Income Date</span>
        <span class="detail-row-value">{{ $income->income_date ? $income->income_date->format('d M, Y') : '—' }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Payment Method</span>
        <span class="detail-row-value">{{ ucfirst(str_replace('_', ' ', $income->payment_method ?? '')) ?: '—' }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Bank Account</span>
        <span class="detail-row-value">{{ $income->bankAccount ? $income->bankAccount->account_name : 'Petty Cash / Other' }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Reference Number</span>
        <span class="detail-row-value">{{ $income->reference_number ?? '—' }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Receipt Number</span>
        <span class="detail-row-value">{{ $income->receipt_number ?? '—' }}</span>
    </div>
</div>

<!-- Description -->
<div class="detail-card">
    <div class="detail-card-head">
        <i class="fas fa-align-left mr-2"></i> Description / Purpose
    </div>
    <div class="detail-card-body">
        <p class="detail-description">{{ $income->description ?? 'No description provided.' }}</p>
    </div>
</div>