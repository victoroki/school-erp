@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">

<style>
/* Select2 sizing inside the refund form (bootstrap4 theme) */
.report-wrap .select2-container .select2-selection--single {
    height: 38px;
    border-radius: 8px;
    border-color: var(--border);
}
.report-wrap .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
.report-wrap .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    color: var(--slate-800);
    font-size: 0.85rem;
}
.report-wrap .select2-container--default .select2-selection--single .select2-selection__placeholder { color: var(--slate-400); }
.report-wrap .select2-container--default.select2-container--focus .select2-selection--single {
    border-color: var(--indigo);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
}
.report-wrap .select2-dropdown { border-color: var(--border); border-radius: 8px; }
.report-wrap .select2-results__option { font-size: 0.85rem; padding: 0.5rem 0.75rem; }
.report-wrap .select2-container--default .select2-results__option--highlighted[aria-selected] {
    background: var(--indigo);
}
</style>

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
                            <option value="{{ $s->student_id }}" {{ old('student_id') == $s->student_id ? 'selected' : '' }}>{{ $s->full_name }} ({{ $s->admission_no }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Figures come from FeeBalanceService — the same source the
                     server-side validation uses — so what is shown here is what
                     the backend enforces. Hidden until a student is chosen. --}}
                <div class="form-field form-field-full" id="refundablePanel" style="display:none;">
                    <label>Refundable balance</label>
                    <div class="refundable-box">
                        <div class="refundable-row">
                            <span>Total valid payments</span>
                            <strong id="rfPaid">&mdash;</strong>
                        </div>
                        <div class="refundable-row">
                            <span>Already refunded</span>
                            <strong id="rfRefunded" class="rf-rose">&mdash;</strong>
                        </div>
                        <div class="refundable-row" id="rfPendingRow" style="display:none;">
                            <span>Requested, not yet paid</span>
                            <strong id="rfPending" class="rf-amber">&mdash;</strong>
                        </div>
                        <div class="refundable-row refundable-row--total">
                            <span>Maximum refundable</span>
                            <strong id="rfMax">&mdash;</strong>
                        </div>
                        <div class="refundable-row">
                            <span>Requested refund amount</span>
                            <strong id="rfRequested">&mdash;</strong>
                        </div>
                        <p class="refundable-note" id="rfNote"></p>
                    </div>
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
                    <label for="amount">Amount (KES) <span class="req">*</span></label>
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

/* What the student has available to refund. These are the figures the server
   validates against, shown before submission so the number is never a surprise. */
.refundable-box { background:var(--slate-50); border:1px solid var(--border); border-radius:10px; padding:.9rem 1.1rem; }
.refundable-row { display:flex; justify-content:space-between; align-items:baseline; gap:1rem; font-size:.82rem; color:var(--slate-600); padding:.2rem 0; }
.refundable-row strong { color:var(--slate-800); font-variant-numeric:tabular-nums; }
.refundable-row--total { border-top:1px solid var(--border); margin-top:.4rem; padding-top:.55rem; font-weight:700; color:var(--slate-800); }
.refundable-row--total strong { color:var(--emerald); font-size:.95rem; }
.refundable-note { font-size:.75rem; margin:.55rem 0 0; color:var(--slate-500); }
.refundable-note.is-over { color:var(--rose); font-weight:600; }
.rf-rose { color:var(--rose); }
.rf-amber { color:var(--amber-600); }
.btn-primary-custom.is-disabled { opacity:.5; cursor:not-allowed; }
@media (max-width:700px){ .form-grid{grid-template-columns:1fr;} }
</style>
@endsection

@push('page_scripts')
<script>
(function(){
    var studentSel = document.getElementById('student_id');
    var paymentSel = document.getElementById('payment_id');
    var feeSel = document.getElementById('student_fee_assignment_id');
    var amountInput = document.getElementById('amount');
    var panel = document.getElementById('refundablePanel');
    var form = document.getElementById('refundForm');
    var submitBtn = form ? form.querySelector('button[type="submit"]') : null;

    /*
     * The student's refundable position, as the backend computes it. Kept in a
     * variable so typing in the amount field re-renders without another request.
     */
    var refundable = null;

    /* Same shape as App\Support\Money::format() — symbol, separators, 2dp. */
    function money(value) {
        return 'KES ' + Number(value || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    /*
     * Show the figures and gate the submit button. This is guidance only: the
     * server re-checks the same rule on submission, and again when the money is
     * actually paid out.
     */
    function renderRefundable() {
        if (!panel) return;

        if (!refundable) {
            panel.style.display = 'none';
            if (submitBtn) { submitBtn.disabled = false; submitBtn.classList.remove('is-disabled'); }
            return;
        }

        panel.style.display = '';

        var max = Number(refundable.maxRefundable);
        var requested = amountInput && amountInput.value !== '' ? Number(amountInput.value) : null;

        document.getElementById('rfPaid').textContent = money(refundable.payments);
        document.getElementById('rfRefunded').textContent = money(refundable.refunded);
        document.getElementById('rfMax').textContent = money(max);
        document.getElementById('rfRequested').textContent = requested === null ? '\u2014' : money(requested);

        var pendingRow = document.getElementById('rfPendingRow');
        if (Number(refundable.pending) > 0) {
            pendingRow.style.display = '';
            document.getElementById('rfPending').textContent = money(refundable.pending);
        } else {
            pendingRow.style.display = 'none';
        }

        var note = document.getElementById('rfNote');
        var blocked = false;

        if (max <= 0) {
            note.textContent = 'This student has no refundable balance, so a refund cannot be submitted. '
                + 'Only money actually received — not reversed — can be refunded, and refunds already made or '
                + 'requested are not refundable a second time.';
            blocked = true;
        } else if (requested !== null && requested > max) {
            note.textContent = 'That is ' + money(requested - max) + ' more than the maximum refundable. '
                + 'Reduce the amount to ' + money(max) + ' or less.';
            blocked = true;
        } else {
            note.textContent = 'Maximum refundable ' + money(max) + '. A refund cannot exceed this.';
        }

        note.classList.toggle('is-over', blocked);

        if (submitBtn) {
            submitBtn.disabled = blocked;
            submitBtn.classList.toggle('is-disabled', blocked);
        }

        if (amountInput) {
            amountInput.max = max > 0 ? max : 0;
        }
    }

    /*
     * Select2 init with polling — the app bundle (jQuery) loads as a deferred
     * module, so it may not exist yet when this classic script runs. Native
     * change listeners below keep the cascade working even before select2
     * initializes.
     */
    function reinitSelect2(sel, placeholder, allowClear) {
        var $ = window.jQuery;
        if (!$ || !$.fn || !$.fn.select2) return;
        var $sel = $(sel);
        if ($sel.hasClass('select2-hidden-accessible')) {
            $sel.select2('destroy');
        }
        $sel.select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: placeholder || (sel.options[0] ? sel.options[0].text : 'Select…'),
            allowClear: !!allowClear,
            minimumResultsForSearch: sel.id === 'student_id' ? 1 : 10
        });
    }

    function fetchPayments(){
        var studentId = studentSel.value;
        paymentSel.innerHTML = '<option value="">Select a payment to refund (optional)</option>';
        feeSel.innerHTML = '<option value="">Select charge (optional)</option>';
        reinitSelect2(paymentSel, 'Select a payment to refund (optional)', true);
        reinitSelect2(feeSel, 'Select charge (optional)', true);
        // Clear the previous student's position rather than leaving it on screen.
        refundable = null;
        renderRefundable();

        if(!studentId) return;

        fetch('/fees/refunds/ajax/student-payments/' + studentId, {headers:{'Accept':'application/json'}})
            .then(function(r){ return r.json(); })
            .then(function(data){
                refundable = (data && data.summary) ? data.summary : null;
                renderRefundable();

                ((data && data.payments) || []).forEach(function(p){
                    var opt = document.createElement('option');
                    opt.value = p.payment_id;
                    opt.textContent = p.label;
                    opt.setAttribute('data-fee', p.student_fee_assignment_id || '');
                    paymentSel.appendChild(opt);
                });
                reinitSelect2(paymentSel, 'Select a payment to refund (optional)', true);
            });
    }

    paymentSel.addEventListener('change', function(){
        var fee = this.options[this.selectedIndex].getAttribute('data-fee') || '';
        // If a payment's charge is known, pre-select it for clarity (still editable if needed).
        if(fee){
            feeSel.innerHTML = '<option value="'+fee+'">Payment charge (auto)</option>';
            feeSel.value = fee;
            var $ = window.jQuery;
            if ($ && $.fn && $.fn.select2 && $(feeSel).hasClass('select2-hidden-accessible')) {
                $(feeSel).val(fee).trigger('change.select2');
            }
            reinitSelect2(feeSel, 'Select charge (optional)', true);
        }
    });

    studentSel.addEventListener('change', fetchPayments);

    // Typing the amount updates "Requested refund amount" and re-checks it
    // against the maximum, from the figures already on screen.
    if (amountInput) amountInput.addEventListener('input', renderRefundable);

    // A refund must name the payment or charge it comes from, or it cannot be
    // taken off a specific fee. The backend enforces this; catching it here saves
    // the round trip.
    if (form) {
        form.addEventListener('submit', function(e){
            if (!feeSel.value && !paymentSel.value) {
                e.preventDefault();
                alert('Select the payment or the fee this refund relates to, so the refund can be taken off that charge.');
            }
        });
    }

    renderRefundable();

    function initSelect2() {
        var $ = window.jQuery;
        if (!($ && $.fn && $.fn.select2)) return false;

        reinitSelect2(studentSel, 'Select Student');
        reinitSelect2(paymentSel, 'Select a payment to refund (optional)', true);
        reinitSelect2(feeSel, 'Select charge (optional)', true);

        $(studentSel).on('change.select2', fetchPayments);
        $(paymentSel).on('change.select2', function(){
            var fee = this.options[this.selectedIndex] ? (this.options[this.selectedIndex].getAttribute('data-fee') || '') : '';
            if (fee) {
                feeSel.innerHTML = '<option value="'+fee+'">Payment charge (auto)</option>';
                $(feeSel).val(fee).trigger('change.select2');
            }
        });

        return true;
    }

    if (!initSelect2()) {
        var attempts = 0;
        var timer = setInterval(function () {
            attempts++;
            if (initSelect2() || attempts > 100) clearInterval(timer);
        }, 50);
    }
})();
</script>
@endpush
