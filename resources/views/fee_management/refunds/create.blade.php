@extends('layouts.app')

@section('content')
<div class="report-wrap">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-amber-light text-amber"><i class="fas fa-hand-holding-usd"></i></div>
            <div>
                <h1 class="page-title mb-0">Request Refund</h1>
                <p class="page-subtitle mb-0">Submit a refund request for approval</p>
            </div>
        </div>
        <a href="{{ route('fees.refunds.index') }}" class="btn-ghost-custom"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    <div class="form-card">
        <form action="{{ route('fees.refunds.store') }}" method="POST" id="refundForm">
            @csrf

            @if ($errors->any())
                <div class="alert-danger-box mb-4">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-grid">
                <div class="form-field">
                    <label for="student_id">Student <span class="req">*</span></label>
                    <select name="student_id" id="student_id" class="form-control" required>
                        <option value="">Select Student</option>
                        @foreach($students as $s)
                            <option value="{{ $s->student_id }}">{{ $s->full_name }} ({{ $s->admission_no }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field">
                    <label for="payment_id">Source Payment (optional)</label>
                    <select name="payment_id" id="payment_id" class="form-control">
                        <option value="">Select a payment to refund (optional)</option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="student_fee_assignment_id">Charge / Fee (optional)</label>
                    <select name="student_fee_assignment_id" id="student_fee_assignment_id" class="form-control">
                        <option value="">Select charge (optional)</option>
                    </select>
                </div>

                <div class="form-field">
                    <label for="amount">Amount (KSh) <span class="req">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" required>
                </div>

                <div class="form-field form-field-full">
                    <label for="reason">Reason <span class="req">*</span></label>
                    <textarea name="reason" id="reason" rows="3" class="form-control" required>{{ old('reason') }}</textarea>
                </div>

                <div class="form-field form-field-full">
                    <label for="supporting_info">Supporting Information (optional)</label>
                    <textarea name="supporting_info" id="supporting_info" rows="2" class="form-control">{{ old('supporting_info') }}</textarea>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn-primary-custom"><i class="fas fa-paper-plane me-1"></i> Submit Request</button>
                <a href="{{ route('fees.refunds.index') }}" class="btn-ghost-custom">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
:root {
    --indigo:#4f46e5; --indigo-light:#eef2ff; --amber:#f59e0b; --amber-600:#d97706; --amber-light:#fffbeb;
    --emerald:#10b981; --emerald-light:#ecfdf5; --rose:#f43f5e; --rose-light:#fff1f2;
    --slate-50:#f8fafc; --slate-100:#f1f5f9; --slate-200:#e2e8f0; --slate-300:#cbd5e1; --slate-400:#94a3b8;
    --slate-500:#64748b; --slate-600:#475569; --slate-700:#334155; --slate-800:#1e293b; --slate-900:#0f172a;
    --border:#e2e8f0; --ease-out: cubic-bezier(0.23,1,0.32,1);
}
.report-wrap { padding:1.5rem 2rem; background:#f9fafb; min-height:100vh; }
.page-title { font-size:1.25rem; font-weight:900; color:var(--slate-900); }
.page-subtitle { color:var(--slate-400); font-size:.8rem; font-weight:500; }
.icon-box { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; }
.bg-amber-light { background:var(--amber-light); } .text-amber { color:var(--amber-600); }
.btn-primary-custom { display:inline-flex; align-items:center; padding:.5rem 1.25rem; border-radius:8px; font-size:.75rem; font-weight:800; border:none; text-decoration:none!important; cursor:pointer; background:var(--emerald); color:#fff; }
.btn-ghost-custom { display:inline-flex; align-items:center; padding:.5rem 1.25rem; border-radius:8px; font-size:.75rem; font-weight:700; text-decoration:none!important; cursor:pointer; background:#fff; border:1px solid var(--border); color:var(--slate-700); }
.form-card { background:#fff; border:1px solid var(--border); border-radius:12px; padding:1.5rem 1.75rem; max-width:820px; }
.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; }
.form-field { display:flex; flex-direction:column; gap:.4rem; }
.form-field-full { grid-column:1 / -1; }
.form-field label { font-size:.72rem; font-weight:700; color:var(--slate-600); text-transform:uppercase; letter-spacing:.04em; }
.form-field .req { color:var(--rose); }
.form-field .form-control { border:1px solid var(--border); border-radius:8px; padding:.55rem .75rem; font-size:.85rem; color:var(--slate-800); }
.form-field .form-control:focus { outline:2px solid var(--indigo-light); border-color:var(--indigo); }
.form-actions { display:flex; gap:.75rem; align-items:center; }
.alert-danger-box { background:var(--rose-light); border:1px solid #fecdd3; color:var(--rose); border-radius:10px; padding:1rem 1.25rem; font-size:.85rem; }
@media (max-width:700px){ .form-grid{grid-template-columns:1fr;} }
</style>
@endsection

@push('page_scripts')
<script>
(function(){
    var studentSel = document.getElementById('student_id');
    var paymentSel = document.getElementById('payment_id');
    var feeSel = document.getElementById('student_fee_assignment_id');

    function fetchPayments(){
        var studentId = studentSel.value;
        paymentSel.innerHTML = '<option value="">Select a payment to refund (optional)</option>';
        feeSel.innerHTML = '<option value="">Select charge (optional)</option>';
        if(!studentId) return;

        fetch('/fees/refunds/ajax/student-payments/' + studentId, {headers:{'Accept':'application/json'}})
            .then(function(r){ return r.json(); })
            .then(function(data){
                (data || []).forEach(function(p){
                    var opt = document.createElement('option');
                    opt.value = p.payment_id;
                    opt.textContent = p.label;
                    opt.setAttribute('data-fee', p.student_fee_assignment_id || '');
                    paymentSel.appendChild(opt);
                });
            });
    }

    paymentSel.addEventListener('change', function(){
        var fee = this.options[this.selectedIndex].getAttribute('data-fee') || '';
        // If a payment's charge is known, pre-select it for clarity (still editable if needed).
        if(fee){
            feeSel.innerHTML = '<option value="'+fee+'">Payment charge (auto)</option>';
        }
    });

    studentSel.addEventListener('change', fetchPayments);
})();
</script>
@endpush
