@extends('layouts.app')

@section('content')
<div class="report-wrap">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-rose-light text-rose"><i class="fas fa-undo"></i></div>
            <div>
                <h1 class="page-title mb-0">Reverse Payment</h1>
                <p class="page-subtitle mb-0">Contra the payment in the ledger — original record preserved for audit</p>
            </div>
        </div>
        <a href="{{ route('fee-management.show', $payment->studentFeeAssignment?->student_id) }}" class="btn-ghost-custom"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    <div class="form-card">
        <div class="payment-summary mb-4">
            <div class="summary-row">
                <span class="info-label">Receipt No</span>
                <span class="info-value mono-sm">{{ $payment->receipt_number ?? 'RCP-'.$payment->payment_id }}</span>
            </div>
            <div class="summary-row">
                <span class="info-label">Student</span>
                <span class="info-value">{{ $payment->studentFeeAssignment->student->full_name ?? 'N/A' }} ({{ $payment->studentFeeAssignment->student->admission_no ?? '' }})</span>
            </div>
            <div class="summary-row">
                <span class="info-label">Charge</span>
                <span class="info-value">{{ $payment->studentFeeAssignment->feeStructure->category->name ?? '—' }}</span>
            </div>
            <div class="summary-row">
                <span class="info-label">Payment Date</span>
                <span class="info-value">{{ $payment->payment_date->format('d M Y') }}</span>
            </div>
            <div class="summary-row">
                <span class="info-label">Method</span>
                <span class="info-value">{{ ucwords(str_replace('_',' ', $payment->payment_method)) }}</span>
            </div>
            <div class="summary-row amount-row">
                <span class="info-label">Amount</span>
                <span class="amount-value">KSh {{ number_format($payment->amount, 2) }}</span>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert-danger-box mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('fees.payments.reverse', $payment->payment_id) }}" method="POST">
            @csrf
            <div class="form-field mb-3">
                <label for="reason">Reversal Reason <span class="req">*</span></label>
                <textarea name="reason" id="reason" rows="3" class="form-control" placeholder="Detailed reason for reversing this payment" required></textarea>
            </div>

            <div class="alert-warn-box mb-4">
                <i class="fas fa-exclamation-triangle me-1"></i> This action reverses the payment in the ledger and restores the student's outstanding balance. It cannot be undone — a contra entry is recorded for audit.
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-reverse" onclick="return confirm('Reverse this payment? This is an auditable action.')"><i class="fas fa-undo me-1"></i> Reverse Payment</button>
                <a href="{{ route('fee-management.show', $payment->studentFeeAssignment?->student_id) }}" class="btn-ghost-custom">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
:root{
    --indigo:#4f46e5; --indigo-light:#eef2ff; --amber:#f59e0b; --amber-600:#d97706; --amber-light:#fffbeb;
    --emerald:#10b981; --emerald-light:#ecfdf5; --rose:#f43f5e; --rose-light:#fff1f2;
    --slate-50:#f8fafc; --slate-100:#f1f5f9; --slate-200:#e2e8f0; --slate-300:#cbd5e1; --slate-400:#94a3b8;
    --slate-500:#64748b; --slate-600:#475569; --slate-700:#334155; --slate-800:#1e293b; --slate-900:#0f172a;
    --border:#e2e8f0;
}
.report-wrap{padding:1.5rem 2rem;background:#f9fafb;min-height:100vh;}
.page-title{font-size:1.25rem;font-weight:900;color:var(--slate-900);}
.page-subtitle{color:var(--slate-400);font-size:.8rem;font-weight:500;}
.mono-sm{font-family:monospace;font-size:.78rem;font-weight:600;}
.icon-box{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;}
.bg-rose-light{background:var(--rose-light);} .text-rose{color:var(--rose);}
.btn-ghost-custom{display:inline-flex;align-items:center;padding:.5rem 1.25rem;border-radius:8px;font-size:.75rem;font-weight:700;text-decoration:none!important;cursor:pointer;background:#fff;border:1px solid var(--border);color:var(--slate-700);}
.form-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:1.5rem 1.75rem;max-width:640px;}
.payment-summary{background:var(--slate-50);border:1px solid var(--border);border-radius:10px;padding:1rem 1.25rem;}
.summary-row{display:flex;justify-content:space-between;padding:.45rem 0;border-bottom:1px solid var(--slate-100);font-size:.84rem;}
.summary-row:last-child{border-bottom:none;}
.info-label{color:var(--slate-500);font-weight:600;}
.info-value{color:var(--slate-800);font-weight:700;text-align:right;}
.amount-row .amount-value{font-size:1.35rem;font-weight:900;color:var(--rose);font-family:monospace;}
.form-field{display:flex;flex-direction:column;gap:.4rem;}
.form-field label{font-size:.72rem;font-weight:700;color:var(--slate-600);text-transform:uppercase;letter-spacing:.04em;}
.form-field .req{color:var(--rose);}
.form-field .form-control{border:1px solid var(--border);border-radius:8px;padding:.55rem .75rem;font-size:.85rem;color:var(--slate-800);}
.form-field .form-control:focus{outline:2px solid var(--rose-light);border-color:var(--rose);}
.alert-warn-box{background:var(--amber-light);border:1px solid #fde68a;color:var(--amber-600);border-radius:10px;padding:1rem 1.25rem;font-size:.82rem;}
.alert-danger-box{background:var(--rose-light);border:1px solid #fecdd3;color:var(--rose);border-radius:10px;padding:1rem 1.25rem;font-size:.85rem;}
.alert-danger-box ul{margin-left:1rem;}
.form-actions{display:flex;gap:.75rem;align-items:center;}
.btn-reverse{display:inline-flex;align-items:center;padding:.55rem 1.25rem;border-radius:8px;font-size:.78rem;font-weight:800;border:none;cursor:pointer;background:var(--rose);color:#fff;}
</style>
@endsection
