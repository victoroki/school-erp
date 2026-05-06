@extends('layouts.app')

@section('content')
<div class="fee-wrap">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-amber-light text-amber">
                <i class="fas fa-percentage"></i>
            </div>
            <div>
                <h1 class="page-title mb-0">Discount Schemes</h1>
                <p class="page-subtitle mb-0">Manage discount rules and criteria</p>
            </div>
        </div>
        <a href="{{ route('fees.discounts.create') }}" class="btn-primary-custom">
            <i class="fas fa-plus me-1"></i> New Scheme
        </a>
    </div>

    @include('flash::message')

    {{-- Schemes Grid --}}
    @if($discountSchemes->count() > 0)
        <div class="row g-3">
            @foreach($discountSchemes as $discountScheme)
                <div class="col-xl-4 col-lg-6">
                    <div class="scheme-card h-100 bg-white d-flex flex-column">
                        <div class="card-body px-3 py-3 flex-grow-1">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="scheme-name mb-1">{{ $discountScheme->name }}</h6>
                                    @if($discountScheme->code)
                                        <span class="code-badge">{{ $discountScheme->code }}</span>
                                    @endif
                                </div>
                                <span class="type-badge {{ $discountScheme->type == 'percentage' ? 'percentage' : ($discountScheme->type == 'fixed' ? 'fixed' : 'full-waiver') }}">
                                    {{ ucfirst(str_replace('_', ' ', $discountScheme->type)) }}
                                </span>
                            </div>

                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div>
                                    <span class="value-label">Value</span>
                                    <div class="value-display">
                                        @if($discountScheme->type == 'percentage')
                                            {{ $discountScheme->value }}%
                                        @elseif($discountScheme->type == 'fixed')
                                            KSh {{ number_format($discountScheme->value, 2) }}
                                        @else
                                            Full Waiver
                                        @endif
                                    </div>
                                </div>
                                <div class="border-start ps-3">
                                    <span class="value-label">Status</span>
                                    <div>
                                        <span class="status-indicator {{ $discountScheme->status == 'active' ? 'active' : 'inactive' }}"></span>
                                        <span class="status-text">{{ ucfirst($discountScheme->status) }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($discountScheme->applies_to)
                                <div class="mb-2">
                                    <span class="applies-badge">
                                        <i class="fas fa-users me-1"></i>
                                        Applies to: {{ ucfirst(str_replace('_', ' ', $discountScheme->applies_to)) }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="card-footer bg-light-soft border-top d-flex justify-content-end gap-2">
                            <a href="{{ route('fees.discounts.show', $discountScheme->id) }}" 
                               class="action-btn" title="View">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('fees.discounts.edit', $discountScheme->id) }}" 
                               class="action-btn" title="Edit">
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::open(['route' => ['fees.discounts.destroy', $discountScheme->id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                <button type="submit" class="action-btn delete-btn" 
                                        onclick="return confirm('Are you sure?')" title="Delete">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $discountSchemes->appends(request()->query())->links() }}
        </div>
    @else
        <div class="empty-state text-center py-5">
            <i class="fas fa-percentage text-muted opacity-25 mb-3" style="font-size: 3rem;"></i>
            <h6 class="fw-bold">No Discount Schemes Found</h6>
            <p class="text-muted small mb-3">Create your first discount scheme to start offering discounts.</p>
            <a href="{{ route('fees.discounts.create') }}" class="btn-primary-custom">Create New Scheme</a>
        </div>
    @endif
</div>

<style>
:root {
    --indigo: #4f46e5;
    --amber: #f59e0b;
    --amber-light: #fffbeb;
    --emerald: #10b981;
    --slate: #475569;
    --slate-light: #f1f5f9;
    --text: #1e293b;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
}

.fee-wrap { padding: 1.5rem 2rem; background: #f9fafb; min-height: 100vh; }

.page-title { font-size: 1.25rem; font-weight: 900; color: var(--text); letter-spacing: -0.02em; }
.page-subtitle { color: var(--muted); font-size: 0.8rem; font-weight: 500; }

.icon-box { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }

/* Scheme Cards */
.scheme-card {
    border-radius: 12px; border: 1px solid var(--border);
    transition: transform 200ms var(--ease-out), box-shadow 200ms var(--ease-out), border-color 200ms var(--ease-out);
    overflow: hidden;
}
.scheme-card:hover {
    border-color: var(--amber-light);
    transform: translateY(-3px);
    box-shadow: 0 12px 24px -8px rgba(245, 158, 11, 0.12);
}
.scheme-card:active { transform: translateY(-1px) scale(0.99); }

.scheme-name { font-size: 0.9rem; font-weight: 850; color: var(--text); }
.code-badge {
    display: inline-block; font-size: 0.65rem; font-weight: 800; text-transform: uppercase;
    background: var(--slate-light); color: var(--slate); padding: 2px 8px; border-radius: 4px;
    letter-spacing: 0.05em;
}

.type-badge {
    font-size: 0.65rem; font-weight: 800; text-transform: uppercase;
    padding: 2px 10px; border-radius: 4px; letter-spacing: 0.05em;
}
.type-badge.percentage { background: #f0fdf4; color: #16a34a; }
.type-badge.fixed { background: #eff6ff; color: #2563eb; }
.type-badge.full-waiver { background: #fdf4ff; color: #9333ea; }

.value-label { font-size: 0.65rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; }
.value-display { font-size: 0.9rem; font-weight: 850; color: var(--text); }

.status-indicator {
    width: 8px; height: 8px; border-radius: 50%; display: inline-block;
}
.status-indicator.active { background: var(--emerald); }
.status-indicator.inactive { background: #94a3b8; }

.status-text { font-size: 0.7rem; font-weight: 700; color: var(--muted); text-transform: capitalize; }

.applies-badge {
    display: inline-block; font-size: 0.7rem; font-weight: 700; color: var(--slate);
    background: var(--slate-light); padding: 4px 10px; border-radius: 6px;
}

.bg-light-soft { background-color: #f8faff; }

/* Action Buttons */
.action-btn {
    width: 32px; height: 32px; border-radius: 8px; border: none;
    background: #fff; color: var(--slate); font-size: 0.75rem;
    display: inline-flex; align-items: center; justify-content: center;
    transition: all 160ms var(--ease-out); cursor: pointer;
    text-decoration: none;
}
@media (hover: hover) and (pointer: fine) {
    .action-btn:hover { background: var(--amber-light); color: var(--amber); }
}
.action-btn:active { transform: scale(0.97); }

.action-btn.delete-btn { color: #94a3b8; }
@media (hover: hover) and (pointer: fine) {
    .action-btn.delete-btn:hover { background: #fef2f2; color: #dc2626; }
}

.btn-primary-custom {
    display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 8px;
    font-size: 0.75rem; font-weight: 800; border: none; text-decoration: none !important;
    background: var(--amber); color: #fff; transition: all 160ms var(--ease-out);
}
.btn-primary-custom:hover { background: #d97706; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3); }
.btn-primary-custom:active { transform: scale(0.97); }

.empty-state { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
</style>

@push('page_scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'default',
            width: '100%',
            placeholder: function() {
                return $(this).attr('placeholder') || 'Select an option';
            }
        });
    });
</script>
@endpush
@endsection
