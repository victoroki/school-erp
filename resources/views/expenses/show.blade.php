@extends('layouts.app')

@section('content')
    @php
        $statusStyles = [
            'draft' => ['label' => 'Draft', 'bg' => '#f1f5f9', 'fg' => '#475569'],
            'pending' => ['label' => 'Pending Approval', 'bg' => '#fffbeb', 'fg' => '#d97706'],
            'approved' => ['label' => 'Approved', 'bg' => '#eef2ff', 'fg' => '#4338ca'],
            'paid' => ['label' => 'Paid', 'bg' => '#ecfdf5', 'fg' => '#059669'],
            'rejected' => ['label' => 'Rejected', 'bg' => '#fff1f2', 'fg' => '#e11d48'],
        ];
        $status = $statusStyles[$expenses->status] ?? $statusStyles['draft'];
    @endphp

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-7">
                    <div class="detail-heading">
                        <div class="detail-heading-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <h1 class="detail-heading-title">Expense Details</h1>
                            <p class="detail-heading-sub">Ref {{ $expenses->reference_number ?: '—' }} &middot; {{ $expenses->expense_date ? $expenses->expense_date->format('d M, Y') : '—' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5 detail-heading-actions">
                    <a href="{{ route('expenses.edit', $expenses->expense_id) }}" class="btn-detail btn-detail--primary">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <a href="{{ route('expenses.index') }}" class="btn-detail btn-detail--ghost">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    @if($expenses->status === 'approved' && !$expenses->bankAccount && $expenses->payment_method !== 'cash')
        <div class="content px-3 pt-0 pb-0">
            <div class="pay-attention mb-3">
                <div class="pay-attention-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="pay-attention-body">
                    <div>
                        <div class="pay-attention-title">Bank account required before payment</div>
                        <p class="pay-attention-text">This {{ ucfirst(str_replace('_', ' ', $expenses->payment_method)) }} expense has no bank account. Choose one to deduct from, then confirm payment.</p>
                    </div>
                    {!! Form::open(['route' => ['expenses.pay', $expenses->expense_id], 'method' => 'post', 'class' => 'pay-attention-form']) !!}
                    <select name="bank_account_id" class="form-control pay-attention-select" required>
                        <option value="">Select account</option>
                        @foreach($bankAccounts as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    {!! Form::button('<i class="fas fa-hand-holding-usd mr-1"></i>Mark as Paid', ['type' => 'submit', 'class' => 'btn-detail btn-detail--primary']) !!}
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    @endif

    <div class="content px-3">
        <div class="row">
            <div class="col-lg-4">
                <div class="detail-card detail-card--summary mb-3">
                    <div class="detail-label">Amount</div>
                    <div class="detail-amount">{{ \App\Support\Money::format($expenses->amount ?? 0) }}</div>
                    <div class="detail-summary-foot">
                        <span class="detail-badge" style="background: {{ $status['bg'] }}; color: {{ $status['fg'] }}">{{ $status['label'] }}</span>
                        <span class="detail-date">{{ $expenses->expense_date ? $expenses->expense_date->format('d M, Y') : '—' }}</span>
                    </div>
                </div>

                <div class="detail-card">
                    <div class="detail-card-head">
                        <i class="fas fa-clipboard-check mr-2"></i> Approval Trail
                    </div>
                    <div class="detail-row">
                        <span class="detail-row-label">Requested By</span>
                        <span class="detail-row-value">{{ $expenses->requestedBy ? $expenses->requestedBy->full_name : '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-row-label">Approved By</span>
                        <span class="detail-row-value">{{ $expenses->approvedBy->name ?? 'Not approved yet' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-row-label">Created By</span>
                        <span class="detail-row-value">{{ $expenses->createdBy->name ?? '—' }}</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                @include('expenses.show_fields')
            </div>
        </div>
    </div>

    <style>
        .detail-heading { display: flex; align-items: center; gap: 14px; }
        .detail-heading-icon {
            width: 44px; height: 44px; border-radius: 10px;
            background: #fff1f2; color: #e11d48;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem; flex-shrink: 0;
        }
        .detail-heading-title { font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2; }
        .detail-heading-sub { color: #64748b; font-size: 0.85rem; font-weight: 500; margin: 2px 0 0; }
        .detail-heading-actions { text-align: right; }
        @media (max-width: 767px) { .detail-heading-actions { text-align: left; margin-top: 0.75rem; } }

        .btn-detail {
            display: inline-block; padding: 8px 18px; border-radius: 8px;
            font-size: 0.85rem; font-weight: 600; line-height: 1.4; text-decoration: none;
            transition: transform 160ms cubic-bezier(0.23, 1, 0.32, 1), box-shadow 200ms;
        }
        .btn-detail:active { transform: scale(0.97); }
        .btn-detail--primary { background: #e11d48; border: 1px solid #e11d48; color: #fff; }
        .btn-detail--primary:hover { background: #be123c; border-color: #be123c; color: #fff; }
        .btn-detail--ghost { background: #fff; border: 1px solid #e2e8f0; color: #334155; }
        .btn-detail--ghost:hover { background: #f8fafc; color: #0f172a; }
        @media (max-width: 575px) { .btn-detail { width: 100%; text-align: center; margin-bottom: 0.4rem; } }

        .detail-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06); }
        .detail-card--summary { padding: 1.5rem; }
        .detail-card-head {
            display: flex; align-items: center;
            font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em;
            color: #334155; padding: 0.9rem 1.25rem; border-bottom: 1px solid #f1f5f9;
        }
        .detail-card-body { padding: 1.25rem; }
        .detail-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; }
        .detail-amount {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 1.75rem; font-weight: 900; color: #0f172a; margin-top: 4px;
        }
        .detail-summary-foot {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f1f5f9;
        }
        .detail-badge { font-size: 0.72rem; font-weight: 700; padding: 5px 12px; border-radius: 999px; }
        .detail-date { color: #64748b; font-size: 0.8rem; font-weight: 500; }

        .detail-row {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            padding: 0.8rem 1.25rem; border-bottom: 1px solid #f8fafc;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-row-label { font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; flex-shrink: 0; }
        .detail-row-value { font-size: 0.88rem; font-weight: 600; color: #1e293b; text-align: right; }

        .detail-description { color: #334155; font-size: 0.9rem; line-height: 1.6; margin: 0; }
        .detail-attachment {
            display: inline-flex; align-items: center; margin-top: 1rem;
            font-size: 0.85rem; font-weight: 600; color: #4338ca; text-decoration: none;
        }
        .detail-attachment:hover { color: #4338ca; text-decoration: underline; }

        .pay-attention {
            display: flex; align-items: flex-start; gap: 14px;
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px;
            padding: 1rem 1.25rem;
        }
        .pay-attention-icon {
            width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
            background: #fef3c7; color: #d97706;
            display: flex; align-items: center; justify-content: center; font-size: 1rem;
        }
        .pay-attention-body { flex: 1; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        .pay-attention-title { font-size: 0.95rem; font-weight: 800; color: #92400e; }
        .pay-attention-text { color: #92400e; opacity: 0.85; font-size: 0.85rem; margin: 2px 0 0; }
        .pay-attention-form { display: flex; align-items: center; gap: 10px; }
        .pay-attention-select { width: 220px; border-radius: 8px; }
    </style>
@endsection