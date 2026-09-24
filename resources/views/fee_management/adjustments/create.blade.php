@extends('layouts.app')

@section('content')
<style>
    .fa-cr {
        --fa-indigo-600: oklch(0.511 0.230 272);
        --fa-indigo-500: oklch(0.555 0.210 272);
        --fa-indigo-100: oklch(0.930 0.034 272);
        --fa-indigo-50:  oklch(0.962 0.018 272);
        --fa-slate-900:  oklch(0.206 0.010 264);
        --fa-slate-700:  oklch(0.372 0.016 264);
        --fa-slate-600:  oklch(0.446 0.018 264);
        --fa-slate-500:  oklch(0.554 0.018 264);
        --fa-slate-400:  oklch(0.704 0.015 264);
        --fa-slate-200:  oklch(0.928 0.008 264);
        --fa-slate-100:  oklch(0.967 0.005 264);
        --fa-slate-50:   oklch(0.984 0.003 264);
        --fa-surface:    oklch(0.995 0.003 264);
        --fa-emerald-600: oklch(0.596 0.145 163);
        --fa-emerald-50:  oklch(0.979 0.021 166);
        --fa-rose-600:   oklch(0.575 0.210 22);
        --fa-rose-50:    oklch(0.969 0.015 12);
        --fa-amber-600:  oklch(0.666 0.179 58);
        --fa-amber-50:   oklch(0.980 0.022 95);
        --fa-ease-out:   cubic-bezier(0.23, 1, 0.32, 1);
        --fa-mono: ui-monospace, "SFMono-Regular", "Cascadia Code", Menlo, Consolas, monospace;
    }

    /* ── Page header ── */
    .fa-head { padding: 0.25rem 0 1.25rem; }
    .fa-head h1 {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--fa-slate-900);
        margin: 0;
    }
    .fa-head-sub {
        color: var(--fa-slate-500);
        font-size: 0.813rem;
        margin-top: 0.125rem;
    }

    /* ── Panel ── */
    .fa-panel { max-width: 880px; }
    .fa-panel-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--fa-slate-900);
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin: 0;
    }
    .fa-panel-title .fa-panel-mark {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: var(--fa-indigo-50);
        color: var(--fa-indigo-600);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    .fa-label {
        font-weight: 700;
        color: var(--fa-slate-700);
        font-size: 0.8125rem;
        display: block;
        margin-bottom: 0.375rem;
    }
    .fa-hint { font-size: 0.75rem; color: var(--fa-slate-400); margin-top: 0.375rem; }

    /* Select2 sizing inside the panel (bootstrap4 theme) */
    .fa-cr .select2-container .select2-selection--single {
        height: 38px;
        border-radius: 8px;
        border-color: var(--fa-slate-200);
    }
    .fa-cr .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
    .fa-cr .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        color: var(--fa-slate-900);
        font-size: 0.875rem;
    }
    .fa-cr .select2-container--default .select2-selection--single .select2-selection__placeholder { color: var(--fa-slate-400); }
    .fa-cr .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: var(--fa-indigo-600);
        box-shadow: 0 0 0 3px oklch(0.511 0.230 272 / 0.12);
    }

    /* ── Fee preview panel ── */
    .fa-preview {
        background: var(--fa-slate-50);
        border: 1px solid var(--fa-slate-200);
        border-radius: 12px;
        padding: 1rem;
        margin-top: 1.25rem;
    }
    .fa-preview-stat {
        background: var(--fa-surface);
        border: 1px solid var(--fa-slate-200);
        border-radius: 10px;
        padding: 0.75rem 1rem;
    }
    .fa-preview-label {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--fa-slate-400);
    }
    .fa-preview-value {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--fa-slate-900);
        font-variant-numeric: tabular-nums;
        font-family: var(--fa-mono);
        margin-top: 0.25rem;
    }
    .fa-preview-value--rose { color: var(--fa-rose-600); }
    .fa-preview-value--emerald { color: var(--fa-emerald-600); }
    .fa-preview-delta { font-size: 0.8125rem; font-weight: 700; margin-top: 0.5rem; display: block; }

    /* ── Buttons ── */
    .fa-btn-primary { transition: transform 0.16s var(--fa-ease-out); }
    .fa-btn-primary:active { transform: scale(0.97); }
    .fa-btn-ghost {
        border-radius: 8px;
        font-weight: 600;
        color: var(--fa-slate-600);
        border: 1px solid var(--fa-slate-200);
        background: var(--fa-surface);
        transition: transform 0.16s var(--fa-ease-out),
                    background-color 0.16s var(--fa-ease-out);
    }
    .fa-btn-ghost:active { transform: scale(0.97); }
    @media (hover: hover) and (pointer: fine) {
        .fa-btn-ghost:hover { background: var(--fa-slate-100); }
    }

    @media (max-width: 768px) {
        .fa-head .d-flex { flex-direction: column; align-items: stretch !important; gap: 0.625rem; }
        .fa-head .btn { justify-content: center; }
    }

    @media (prefers-reduced-motion: reduce) {
        .fa-btn-primary, .fa-btn-ghost { transition: none; }
    }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">

<div class="fa-cr">
    <section class="content-header">
        <div class="container-fluid px-0">
            <div class="fa-head d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1>Request Fee Adjustment</h1>
                    <p class="fa-head-sub">Reduce, increase or waive an assigned student fee</p>
                </div>
                <a class="btn btn-outline-secondary" href="{{ route('fees.adjustments.index') }}" style="border-radius: 8px; font-weight: 600;">
                    <i class="fas fa-arrow-left mr-1" style="font-size: 0.8rem;"></i> Back
                </a>
            </div>
        </div>
    </section>

    <div class="content px-0">
        @include('adminlte-templates::common.errors')

        <div class="card fa-panel">
            <div class="card-header">
                <h3 class="fa-panel-title">
                    <span class="fa-panel-mark"><i class="fas fa-sliders-h"></i></span>
                    Adjustment Details
                </h3>
            </div>

            {!! Form::open(['route' => 'fees.adjustments.store', 'class' => 'needs-validation']) !!}
            <div class="card-body" style="padding: 1.5rem;">
                <div class="row" style="margin: 0 -0.625rem;">
                    <div class="col-md-6" style="padding: 0 0.625rem;">
                        <div class="form-group">
                            <label class="fa-label" for="student_id">Student</label>
                            <select name="student_id" id="student_id" class="form-control" required>
                                <option value="">Select Student</option>
                                @foreach($students ?? [] as $id => $name)
                                    <option value="{{ $id }}" {{ old('student_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6" style="padding: 0 0.625rem;">
                        <div class="form-group">
                            <label class="fa-label" for="academic_year_id">Academic Year</label>
                            <select name="academic_year_id" id="academic_year_id" class="form-control">
                                @foreach($academicYears as $id => $name)
                                    <option value="{{ $id }}" {{ (old('academic_year_id') ?? $currentYearId) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="fa-label" for="student_fee_assignment_id">Fee Category</label>
                    <select name="student_fee_assignment_id" id="student_fee_assignment_id" class="form-control" required>
                        <option value="">Select Fee Category</option>
                        @foreach($feeAssignments ?? [] as $af)
                            <option value="{{ $af->id }}"
                                    data-original="{{ $af->amount }}"
                                    data-current="{{ $af->final_amount }}"
                                    {{ old('student_fee_assignment_id') == $af->id ? 'selected' : '' }}>
                                {{ $af->feeStructure->category->name ?? 'Uncategorized' }} ({{ $af->term }}) — KES {{ number_format($af->amount, 2) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="fa-hint"><i class="fas fa-info-circle mr-1"></i>Select a student first to see their assigned fees</p>
                </div>

                <div id="fee_details" class="fa-preview" style="display: none;">
                    <div class="row" style="margin: 0 -0.5rem;">
                        <div class="col-md-4" style="padding: 0 0.5rem; margin-bottom: 0.625rem;">
                            <div class="fa-preview-stat">
                                <div class="fa-preview-label">Original Amount</div>
                                <div id="original_amount_display" class="fa-preview-value">-</div>
                            </div>
                        </div>
                        <div class="col-md-4" style="padding: 0 0.5rem; margin-bottom: 0.625rem;">
                            <div class="fa-preview-stat">
                                <div class="fa-preview-label">New Amount</div>
                                <div id="new_amount_display" class="fa-preview-value fa-preview-value--emerald">-</div>
                            </div>
                        </div>
                        <div class="col-md-4" style="padding: 0 0.5rem; margin-bottom: 0.625rem;">
                            <div class="fa-preview-stat">
                                <div class="fa-preview-label">Effect</div>
                                <div id="adjustment_delta" class="fa-preview-value fa-preview-value--rose" style="font-size: 0.95rem; line-height: 1.4;">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin: 1.25rem -0.625rem 0;">
                    <div class="col-md-6" style="padding: 0 0.625rem;">
                        <div class="form-group">
                            <label class="fa-label" for="adjustment_type">Adjustment Type</label>
                            <select name="adjustment_type" id="adjustment_type" class="form-control" required>
                                <option value="reduction" {{ old('adjustment_type') == 'reduction' ? 'selected' : '' }}>Reduction</option>
                                <option value="increase" {{ old('adjustment_type') == 'increase' ? 'selected' : '' }}>Increase</option>
                                <option value="waiver" {{ old('adjustment_type') == 'waiver' ? 'selected' : '' }}>Full Waiver</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6" style="padding: 0 0.625rem;">
                        <div class="form-group">
                            <label class="fa-label" for="new_amount">New Amount (KES)</label>
                            <input type="number" name="new_amount" id="new_amount" class="form-control" step="0.01" min="0"
                                   value="{{ old('new_amount') }}" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="fa-label" for="reason">Reason for Adjustment</label>
                    <textarea name="reason" class="form-control" rows="4" placeholder="Provide detailed justification for this adjustment…" required>{{ old('reason') }}</textarea>
                </div>
            </div>

            <div class="card-footer" style="display: flex; gap: 0.625rem; padding: 1rem 1.5rem;">
                <button type="submit" class="btn btn-primary fa-btn-primary">
                    <i class="fas fa-paper-plane mr-1"></i> Submit for Approval
                </button>
                <a href="{{ route('fees.adjustments.index') }}" class="btn fa-btn-ghost">Cancel</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {
    var studentSel = document.getElementById('student_id');
    var yearSel = document.getElementById('academic_year_id');
    var feeSel = document.getElementById('student_fee_assignment_id');
    var feeDetails = document.getElementById('fee_details');
    var originalEl = document.getElementById('original_amount_display');
    var newDisplayEl = document.getElementById('new_amount_display');
    var deltaEl = document.getElementById('adjustment_delta');
    var typeSel = document.getElementById('adjustment_type');
    var amountInput = document.getElementById('new_amount');

    if (!studentSel || !feeSel || !yearSel) return;

    var FEES_URL = {!! json_encode(route('fees.adjustments.ajax.student-fees')) !!};

    function fmt(n) {
        return 'KES ' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function reinitSelect2(sel, placeholder) {
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
            allowClear: false,
            minimumResultsForSearch: sel.id === 'student_id' ? 1 : 10
        });
    }

    function loadFees() {
        var studentId = studentSel.value;
        var academicYearId = yearSel.value;

        if (!studentId) {
            feeSel.innerHTML = '<option value="">Select Fee Category</option>';
            feeDetails.style.display = 'none';
            reinitSelect2(feeSel, 'Select Fee Category');
            return;
        }

        var url = FEES_URL + '?student_id=' + encodeURIComponent(studentId) +
                  '&academic_year_id=' + encodeURIComponent(academicYearId || '');

        feeDetails.style.display = 'none';

        fetch(url)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var html = '<option value="">Select Fee Category</option>';
                data.forEach(function (fee) {
                    html += '<option value="' + fee.id + '" data-original="' + fee.original_amount +
                            '" data-current="' + fee.current_final_amount + '">' +
                            fee.fee_name + ' (' + fee.term + ') — ' + fmt(fee.original_amount) + '</option>';
                });
                feeSel.innerHTML = html;
                reinitSelect2(feeSel, 'Select Fee Category');
            })
            .catch(function () {
                feeDetails.style.display = 'none';
                feeSel.innerHTML = '<option value="">Select Fee Category</option>';
                reinitSelect2(feeSel, 'Select Fee Category');
            });
    }

    function updatePreview() {
        var chosen = feeSel.options[feeSel.selectedIndex];
        if (!chosen || !chosen.value) {
            feeDetails.style.display = 'none';
            return;
        }

        feeDetails.style.display = 'block';
        var original = Number(chosen.dataset.original || 0);
        originalEl.textContent = fmt(original);

        if (amountInput.value === '' || amountInput.value === null) {
            amountInput.value = chosen.dataset.current;
        }

        var next = Number(amountInput.value || 0);
        var delta = original - next;
        var waiver = typeSel.value === 'waiver';

        newDisplayEl.textContent = fmt(next);

        if (waiver) {
            deltaEl.textContent = 'Fully waived';
            deltaEl.style.color = 'var(--fa-rose-600)';
        } else if (delta > 0.004) {
            deltaEl.textContent = 'Reduction of ' + fmt(delta);
            deltaEl.style.color = 'var(--fa-emerald-600)';
        } else if (delta < -0.004) {
            deltaEl.textContent = 'Increase of ' + fmt(Math.abs(delta));
            deltaEl.style.color = 'var(--fa-amber-600)';
        } else {
            deltaEl.textContent = 'No change';
            deltaEl.style.color = 'var(--fa-slate-400)';
        }
    }

    /* Native listeners — reliable with or without select2 */
    studentSel.addEventListener('change', loadFees);
    yearSel.addEventListener('change', loadFees);
    feeSel.addEventListener('change', updatePreview);
    amountInput.addEventListener('input', updatePreview);

    typeSel.addEventListener('change', function () {
        if (this.value === 'waiver') {
            amountInput.value = 0;
            amountInput.readOnly = true;
        } else {
            amountInput.readOnly = false;
        }
        updatePreview();
    });

    /*
     * Vite loads the app bundle (jQuery) as a deferred module, so it may not
     * exist when this classic script runs. Poll until jQuery + Select2 land,
     * then wire the searchable dropdowns. Native change listeners above keep
     * the cascade working even before select2 initializes.
     */
    function initSelect2() {
        var $ = window.jQuery;
        if (!($ && $.fn && $.fn.select2)) return false;

        reinitSelect2(studentSel);
        reinitSelect2(yearSel);
        reinitSelect2(feeSel, 'Select Fee Category');

        $(studentSel).on('change.select2', loadFees);
        $(yearSel).on('change.select2', loadFees);
        $(feeSel).on('change.select2', updatePreview);

        /* Pre-filled form (e.g. after a validation redirect) — show the panel. */
        if (feeSel.value) updatePreview();

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
@endsection