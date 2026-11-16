<!-- Overview -->
<div class="detail-card">
    <div class="detail-card-head">
        <i class="fas fa-info-circle mr-2"></i> Overview
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Financial Year</span>
        <span class="detail-row-value">{{ $budget->financialYear->name }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Category</span>
        <span class="detail-row-value">{{ $budget->category ? $budget->category->name : 'N/A' }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Category Type</span>
        <span class="detail-row-value">{{ ucfirst($budget->category_type) }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Alert Threshold</span>
        <span class="detail-row-value">{{ \App\Support\Money::whole($budget->alert_threshold) }}%</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Created At</span>
        <span class="detail-row-value">{{ $budget->created_at ? $budget->created_at->format('d M, Y') : '—' }}</span>
    </div>
    <div class="detail-row">
        <span class="detail-row-label">Updated At</span>
        <span class="detail-row-value">{{ $budget->updated_at ? $budget->updated_at->format('d M, Y') : '—' }}</span>
    </div>
</div>