@extends('layouts.app')

@section('content')
<div class="fee-show-wrap">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-amber-light text-amber">
                <i class="fas fa-percentage"></i>
            </div>
            <div>
                <h1 class="page-title mb-0">{{ $discountScheme->name }}</h1>
                <p class="page-subtitle mb-0">
                    <span class="code-badge">{{ $discountScheme->code }}</span>
                    <span class="mx-1">&middot;</span>
                    <span class="status-badge status-{{ $discountScheme->status }}">{{ ucfirst($discountScheme->status) }}</span>
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('fees.discounts.edit', $discountScheme->id) }}" class="btn-ghost-custom">
                <i class="fas fa-pencil-alt me-1"></i> Edit
            </a>
            <a href="{{ route('fees.discounts.index') }}" class="btn-ghost-custom">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    {{-- Hero Value --}}
    <div class="hero-card mb-4">
        <div class="hero-value">
            @if($discountScheme->type === 'full_waiver')
                Full Waiver
            @elseif($discountScheme->type === 'percentage')
                {{ number_format($discountScheme->value, 0) }}%
            @else
                {{ number_format($discountScheme->value, 2) }}
            @endif
        </div>
        <div class="hero-label">
            {{ ucfirst($discountScheme->type) }} Discount
            @if($discountScheme->type !== 'full_waiver' && $discountScheme->type === 'fixed')
                Amount
            @endif
        </div>
    </div>

    <div class="row g-4">
        {{-- Scope & Eligibility --}}
        <div class="col-lg-6">
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-crosshairs"></i>
                    <span>Scope & Eligibility</span>
                </div>
                <div class="detail-card-body">
                    <div class="detail-row">
                        <span class="detail-label">Applies To</span>
                        <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $discountScheme->applies_to)) }}</span>
                    </div>

                    @if($discountScheme->applicable_fee_categories && count($discountScheme->applicable_fee_categories) > 0)
                    <div class="detail-row">
                        <span class="detail-label">Fee Categories</span>
                        <div class="detail-value">
                            <div class="tag-list">
                                @foreach($discountScheme->applicable_fee_categories as $category)
                                    <span class="tag">{{ $category }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="detail-row">
                        <span class="detail-label">Eligibility</span>
                        <span class="detail-value">
                            <span class="eligibility-badge">
                                <i class="fas fa-{{ $discountScheme->eligibility_criteria === 'staff_child' ? 'user-tie' : ($discountScheme->eligibility_criteria === 'sibling' ? 'people-arrows' : ($discountScheme->eligibility_criteria === 'merit' ? 'trophy' : ($discountScheme->eligibility_criteria === 'financial_aid' ? 'hand-holding-heart' : 'cog'))) }}"></i>
                                {{ ucfirst(str_replace('_', ' ', $discountScheme->eligibility_criteria)) }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Validity & Settings --}}
        <div class="col-lg-6">
            <div class="detail-card">
                <div class="detail-card-header">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Validity & Settings</span>
                </div>
                <div class="detail-card-body">
                    @if($discountScheme->academic_year_id)
                    <div class="detail-row">
                        <span class="detail-label">Academic Year</span>
                        <span class="detail-value">{{ $discountScheme->academicYear?->name ?? 'N/A' }}</span>
                    </div>
                    @endif

                    <div class="detail-row">
                        <span class="detail-label">Valid From</span>
                        <span class="detail-value">{{ $discountScheme->valid_from?->format('M d, Y') ?? 'Not set' }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Valid To</span>
                        <span class="detail-value">{{ $discountScheme->valid_to?->format('M d, Y') ?? 'Not set' }}</span>
                    </div>

                    @if($discountScheme->max_instances)
                    <div class="detail-row">
                        <span class="detail-label">Max Instances</span>
                        <span class="detail-value">{{ number_format($discountScheme->max_instances) }}</span>
                    </div>
                    @endif

                    @if($discountScheme->budget_allocated)
                    <div class="detail-row">
                        <span class="detail-label">Budget Allocated</span>
                        <span class="detail-value budget-value">{{ number_format($discountScheme->budget_allocated, 2) }}</span>
                    </div>
                    @endif

                    <div class="detail-row">
                        <span class="detail-label">Requires Approval</span>
                        <span class="detail-value">
                            <span class="toggle-badge {{ $discountScheme->requires_approval ? 'toggle-on' : 'toggle-off' }}">
                                <i class="fas fa-{{ $discountScheme->requires_approval ? 'check' : 'times' }}"></i>
                                {{ $discountScheme->requires_approval ? 'Yes' : 'No' }}
                            </span>
                        </span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Auto Apply</span>
                        <span class="detail-value">
                            <span class="toggle-badge {{ $discountScheme->auto_apply ? 'toggle-on' : 'toggle-off' }}">
                                <i class="fas fa-{{ $discountScheme->auto_apply ? 'check' : 'times' }}"></i>
                                {{ $discountScheme->auto_apply ? 'Yes' : 'No' }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Meta --}}
    <div class="meta-bar mt-4">
        <span>Created {{ $discountScheme->created_at?->diffForHumans() ?? 'N/A' }}</span>
        <span class="mx-2">&middot;</span>
        <span>Last updated {{ $discountScheme->updated_at?->diffForHumans() ?? 'N/A' }}</span>
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5;
    --indigo-light: #eef2ff;
    --amber: #f59e0b;
    --amber-light: #fffbeb;
    --emerald: #10b981;
    --emerald-light: #ecfdf5;
    --rose: #f43f5e;
    --rose-light: #fff1f2;
    --slate: #475569;
    --slate-light: #f8fafc;
    --slate-muted: #94a3b8;
    --text: #0f172a;
    --text-secondary: #334155;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
}

.fee-show-wrap { padding: 1.5rem 2rem; background: #f9fafb; min-height: 100vh; }

.page-title { font-size: 1.25rem; font-weight: 900; color: var(--text); letter-spacing: -0.02em; }
.page-subtitle { color: var(--slate-muted); font-size: 0.8rem; font-weight: 500; display: flex; align-items: center; }

.icon-box { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.bg-amber-light { background: var(--amber-light); }
.text-amber { color: var(--amber); }

.code-badge {
    display: inline-block; padding: 2px 8px; border-radius: 4px;
    background: var(--indigo-light); color: var(--indigo);
    font-size: 0.7rem; font-weight: 700; font-family: monospace; letter-spacing: 0.05em;
}

.status-badge {
    display: inline-block; padding: 2px 8px; border-radius: 4px;
    font-size: 0.7rem; font-weight: 700; text-transform: capitalize;
}
.status-active { background: var(--emerald-light); color: var(--emerald); }
.status-inactive { background: var(--rose-light); color: var(--rose); }

.btn-ghost-custom {
    display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 8px;
    font-size: 0.75rem; font-weight: 700; text-decoration: none !important;
    background: #fff; border: 1px solid var(--border); color: var(--text); transition: all 160ms var(--ease-out);
}
.btn-ghost-custom:hover { background: var(--slate-light); }
.btn-ghost-custom:active { transform: scale(0.97); }

/* Hero Card */
.hero-card {
    background: linear-gradient(135deg, var(--indigo) 0%, #6366f1 100%);
    border-radius: 16px; padding: 2rem; color: #fff;
    box-shadow: 0 4px 24px rgba(79, 70, 229, 0.25);
}
.hero-value {
    font-size: 3rem; font-weight: 900; letter-spacing: -0.03em; line-height: 1;
    margin-bottom: 0.5rem;
}
.hero-label {
    font-size: 0.85rem; font-weight: 600; opacity: 0.85; letter-spacing: 0.02em;
}

/* Detail Cards */
.detail-card {
    background: #fff; border-radius: 12px; border: 1px solid var(--border);
    box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: hidden;
    transition: box-shadow 200ms var(--ease-out);
}
.detail-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }

.detail-card-header {
    display: flex; align-items: center; gap: 0.5rem;
    padding: 0.875rem 1.25rem; border-bottom: 1px solid var(--border);
    font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em;
    color: var(--slate-muted); background: var(--slate-light);
}
.detail-card-header i { font-size: 0.75rem; color: var(--indigo); }

.detail-card-body { padding: 0.25rem 0; }

.detail-row {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding: 0.75rem 1.25rem; border-bottom: 1px solid #f1f5f9;
}
.detail-row:last-child { border-bottom: none; }

.detail-label {
    font-size: 0.75rem; font-weight: 600; color: var(--slate-muted);
    text-transform: uppercase; letter-spacing: 0.04em; flex-shrink: 0;
}

.detail-value {
    font-size: 0.85rem; font-weight: 600; color: var(--text-secondary);
    text-align: right;
}

.tag-list { display: flex; flex-wrap: wrap; gap: 4px; justify: flex-end; }
.tag {
    display: inline-block; padding: 2px 8px; border-radius: 4px;
    background: var(--indigo-light); color: var(--indigo);
    font-size: 0.7rem; font-weight: 600;
}

.eligibility-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 10px; border-radius: 6px;
    background: var(--amber-light); color: var(--amber);
    font-size: 0.75rem; font-weight: 700;
}

.toggle-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700;
}
.toggle-on { background: var(--emerald-light); color: var(--emerald); }
.toggle-off { background: var(--slate-light); color: var(--slate-muted); }

.budget-value { font-family: monospace; letter-spacing: 0.02em; }

/* Meta Bar */
.meta-bar {
    font-size: 0.7rem; color: var(--slate-muted); font-weight: 500;
}
</style>
@endsection
