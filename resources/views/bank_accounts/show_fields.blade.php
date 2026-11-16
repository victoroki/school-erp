<!-- Bank Account Overview Card -->
<div class="detail-card">
    <div class="detail-card-head">
        <i class="fas fa-circle-info text-indigo mr-2"></i> Overview
    </div>
    <div class="detail-card-body">
        <div class="detail-row">
            <span class="detail-row-label">Account Name</span>
            <span class="detail-row-value">{{ $bankAccount->account_name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Account Number</span>
            <span class="detail-row-value">{{ $bankAccount->account_number }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Bank Name</span>
            <span class="detail-row-value">{{ $bankAccount->bank_name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Branch</span>
            <span class="detail-row-value">{{ $bankAccount->branch_name }}</span>
        </div>
        @if(!empty($bankAccount->ifsc_code))
            <div class="detail-row">
                <span class="detail-row-label">Bank / Branch Code</span>
                <span class="detail-row-value">{{ $bankAccount->ifsc_code }}</span>
            </div>
        @endif
    </div>
</div>

<!-- Balances Card -->
<div class="detail-card mt-3">
    <div class="detail-card-head">
        <i class="fas fa-scale-balanced text-indigo mr-2"></i> Balances
    </div>
    <div class="detail-card-body">
        <div class="detail-row">
            <span class="detail-row-label">Opening Balance</span>
            <span class="detail-row-value">{{ \App\Support\Money::format($bankAccount->opening_balance ?? 0) }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Current Balance</span>
            <span class="detail-row-value" style="color: #4338ca; font-size: 1.05rem; font-weight: 800;">
                {{ \App\Support\Money::format($bankAccount->current_balance ?? 0) }}
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-row-label">Account Type</span>
            <span class="detail-row-value">
                <span class="badge px-3 py-2 rounded-pill font-weight-bold"
                      style="background: #eef2ff; color: #4338ca;">
                    {{ ucwords(str_replace('_', ' ', $bankAccount->account_type ?? 'Account')) }}
                </span>
            </span>
        </div>
    </div>
</div>
