<div class="row g-4">
    {{-- Academic Info --}}
    <div class="col-md-6">
        <div class="field-group p-3 border rounded-3 bg-light-soft h-100">
            <span class="label-title"><i class="fas fa-calendar-alt me-2 text-indigo"></i> Academic Period</span>
            <span class="value-text">{{ optional($feeStructure->academicYear)->name ?? 'N/A' }}</span>
            <small class="text-muted">Academic Term: {{ $feeStructure->term ?? 'Standard' }}</small>
        </div>
    </div>

    <div class="col-md-6">
        <div class="field-group p-3 border rounded-3 bg-light-soft h-100">
            <span class="label-title"><i class="fas fa-graduation-cap me-2 text-indigo"></i> Target Class</span>
            <span class="value-text">{{ optional($feeStructure->schoolClass)->name ?? 'All Classes' }}</span>
            <small class="text-muted">Section: {{ optional($feeStructure->schoolClass)->section ?? 'N/A' }}</small>
        </div>
    </div>

    {{-- Billing Details --}}
    <div class="col-md-6">
        <div class="field-group p-3 border rounded-3 bg-light-soft h-100">
            <span class="label-title"><i class="fas fa-tags me-2 text-amber"></i> Fee Category</span>
            <span class="value-text">{{ optional($feeStructure->category)->name ?? 'N/A' }}</span>
            <small class="text-muted">Category ID: {{ $feeStructure->category_id }}</small>
        </div>
    </div>

    <div class="col-md-6">
        <div class="field-group p-3 border rounded-3 bg-light-soft h-100">
            <span class="label-title"><i class="fas fa-clock me-2 text-rose"></i> Payment Frequency</span>
            <span class="value-text text-capitalize">{{ str_replace('-', ' ', $feeStructure->payment_frequency) }}</span>
            <small class="text-muted">Due Date: {{ $feeStructure->due_date ? $feeStructure->due_date->format('M d, Y') : 'N/A' }}</small>
        </div>
    </div>

    {{-- Extra Options --}}
    <div class="col-12 mt-4">
        <div class="border-top pt-4">
            <h6 class="fw-bold mb-3">Additional Rules & Settings</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="dot {{ $feeStructure->pro_rata_enabled ? 'bg-emerald' : 'bg-slate' }}"></div>
                        <span class="small fw-bold">Pro-rata Billing:</span>
                        <span class="badge {{ $feeStructure->pro_rata_enabled ? 'bg-emerald-light text-emerald' : 'bg-slate-light text-slate' }}">
                            {{ $feeStructure->pro_rata_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-triangle text-rose small"></i>
                        <span class="small fw-bold">Late Fee:</span>
                        <span class="text-dark small fw-bold">
                            {{ $feeStructure->late_fee_amount > 0 ? number_format($feeStructure->late_fee_amount, 2) . ' (' . ucfirst($feeStructure->late_fee_type) . ')' : 'None' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    @if($feeStructure->notes)
    <div class="col-12">
        <div class="bg-amber-light border-amber-subtle border p-3 rounded-3 mt-3">
            <span class="label-title text-amber"><i class="fas fa-sticky-note me-2"></i> Administrative Notes</span>
            <p class="mb-0 small text-dark">{{ $feeStructure->notes }}</p>
        </div>
    </div>
    @endif
</div>

<style>
.bg-light-soft { background: #f9fbff; border-color: #edf2f7 !important; }
.dot { width: 8px; height: 8px; border-radius: 50%; }
.bg-emerald { background-color: var(--emerald); }
.border-amber-subtle { border-color: #fde68a !important; }
</style>
