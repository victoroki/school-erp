@extends('layouts.app')

@section('content')
    <div class="collect-payment-wrap">
        @include('adminlte-templates::common.errors')

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-emerald-light text-emerald">
                <i class="fas fa-cash-register"></i>
            </div>
            <div>
                <h1 class="page-title mb-0">Collect Payment</h1>
                <p class="page-subtitle mb-0">Record a fee payment for this student</p>
            </div>
        </div>
        <a href="{{ route('fees.collect') }}" class="btn-ghost-custom">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="payment-grid">
        {{-- Student Summary --}}
        <div class="summary-card">
            <div class="summary-header">
                <i class="fas fa-user-graduate"></i>
                <span>Student</span>
            </div>
            <div class="summary-body">
                <div class="student-profile">
                    @if($student->photo_url)
                        <img src="{{ $student->photo_url }}" class="student-photo" alt="">
                    @else
                        <div class="student-photo-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                    <div class="student-info">
                        <h3 class="student-name">{{ $student->full_name }}</h3>
                        <span class="student-admission">{{ $student->admission_no }}</span>
                    </div>
                </div>

                <div class="summary-divider"></div>

                <div class="financial-row">
                    <span class="financial-label">Total Fee</span>
                    <span class="financial-value">KSh {{ number_format($student->total_fee, 2) }}</span>
                </div>
                <div class="financial-row">
                    <span class="financial-label">Amount Paid</span>
                    <span class="financial-value text-emerald">KSh {{ number_format($student->paid_fee, 2) }}</span>
                </div>
                <div class="financial-row financial-row-highlight">
                    <span class="financial-label">Current Balance</span>
                    <span class="financial-value text-rose">KSh {{ number_format($student->balance_fee, 2) }}</span>
                </div>

                @php
                    $progress = $student->total_fee > 0 ? ($student->paid_fee / $student->total_fee) * 100 : 0;
                @endphp
                <div class="progress-section">
                    <div class="progress-header">
                        <span class="progress-label">Payment Progress</span>
                        <span class="progress-percent">{{ number_format($progress, 1) }}%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ min($progress, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Form --}}
        <div class="payment-card">
            <div class="payment-header">
                <i class="fas fa-receipt"></i>
                <span>Payment Details</span>
            </div>
            <form action="{{ route('fee-management.store-payment', $student->student_id) }}" method="POST" class="payment-form">
                @csrf

                <div class="form-group">
                    <label for="student_fee_assignment_id" class="form-label-custom">Fee to Pay <span class="required">*</span></label>
                    <select name="student_fee_assignment_id" id="student_fee_assignment_id" class="form-select-custom" required>
                        <option value="total" data-balance="{{ $totalBalance }}" data-name="Total Balance">
                            All Fees — Total Balance: KSh {{ number_format($totalBalance, 2) }}
                        </option>
                        @foreach($student->feeAssignments as $fee)
                            <option value="{{ $fee->id }}" data-balance="{{ $fee->balance }}" data-name="{{ $fee->feeStructure->category->name }}">
                                {{ $fee->feeStructure->category->name }} — Balance: KSh {{ number_format($fee->balance, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="selected-fee-info" id="selected-fee-info">
                    <div class="info-row">
                        <span>Selected fee balance:</span>
                        <span class="info-amount" id="fee-balance-display">KSh {{ number_format($totalBalance, 2) }}</span>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="amount" class="form-label-custom">Payment Amount <span class="required">*</span></label>
                        <div class="input-with-prefix">
                            <span class="input-prefix">KSh</span>
                            <input type="number" step="0.01" name="amount" id="amount" class="form-input-custom" required placeholder="0.00">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="payment_date" class="form-label-custom">Payment Date <span class="required">*</span></label>
                        <input type="date" name="payment_date" id="payment_date" class="form-input-custom" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="payment_method" class="form-label-custom">Payment Method <span class="required">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-select-custom" required>
                            <option value="cash">Cash</option>
                            <option value="check">Check</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="online">Online / M-Pesa</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="transaction_id" class="form-label-custom">Transaction Reference</label>
                        <input type="text" name="transaction_id" id="transaction_id" class="form-input-custom" placeholder="E.g. TXN-12345">
                    </div>
                </div>

                <div class="form-group">
                    <label for="remarks" class="form-label-custom">Remarks</label>
                    <textarea name="remarks" id="remarks" class="form-input-custom form-textarea" rows="2" placeholder="Any additional notes..."></textarea>
                </div>

                <div class="form-footer">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-check-circle me-2"></i> Confirm Payment
                    </button>
                    <a href="{{ route('fee-management.show', $student->student_id) }}" class="btn-cancel">
                        View Student Details
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5;
    --indigo-light: #eef2ff;
    --amber: #f59e0b;
    --amber-600: #d97706;
    --amber-light: #fffbeb;
    --emerald: #10b981;
    --emerald-light: #ecfdf5;
    --rose: #f43f5e;
    --rose-light: #fff1f2;
    --slate-50: #f8fafc;
    --slate-100: #f1f5f9;
    --slate-200: #e2e8f0;
    --slate-300: #cbd5e1;
    --slate-400: #94a3b8;
    --slate-500: #64748b;
    --slate-600: #475569;
    --slate-700: #334155;
    --slate-800: #1e293b;
    --slate-900: #0f172a;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
}

.collect-payment-wrap { 
    padding: 1.5rem 2rem; 
    background: #f9fafb; 
}

.page-title { font-size: 1.25rem; font-weight: 900; color: var(--slate-900); letter-spacing: -0.02em; }
.page-subtitle { color: var(--slate-400); font-size: 0.8rem; font-weight: 500; }

.icon-box { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.bg-emerald-light { background: var(--emerald-light); }
.text-emerald { color: var(--emerald); }
.bg-indigo-light { background: var(--indigo-light); }
.text-indigo { color: var(--indigo); }
.bg-amber-light { background: var(--amber-light); }
.text-amber { color: var(--amber); }
.bg-rose-light { background: var(--rose-light); }
.text-rose { color: var(--rose); }

.btn-ghost-custom {
    display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 8px;
    font-size: 0.75rem; font-weight: 700; text-decoration: none !important; cursor: pointer;
    background: #fff; border: 1px solid var(--border); color: var(--slate-700); transition: all 160ms var(--ease-out);
}
.btn-ghost-custom:hover { background: var(--slate-100); }
.btn-ghost-custom:active { transform: scale(0.97); }

/* Grid Layout */
.payment-grid { display: grid; grid-template-columns: 340px 1fr; gap: 1.5rem; align-items: start; }

/* Summary Card */
.summary-card {
    background: #fff; border-radius: 12px; border: 1px solid var(--border); overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.summary-header {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.875rem 1.25rem;
    border-bottom: 1px solid var(--border); background: var(--slate-50);
    font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--slate-400);
}
.summary-header i { color: var(--indigo); font-size: 0.75rem; }
.summary-body { padding: 1.25rem; }

.student-profile { display: flex; align-items: center; gap: 1rem; margin-bottom: 0.25rem; }
.student-photo { width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 2px solid var(--slate-100); }
.student-photo-placeholder {
    width: 56px; height: 56px; border-radius: 50%; background: var(--slate-100);
    display: flex; align-items: center; justify-content: center; color: var(--slate-400); font-size: 1.25rem;
}
.student-info { display: flex; flex-direction: column; }
.student-name { font-size: 1rem; font-weight: 800; color: var(--slate-900); margin: 0; }
.student-admission { font-size: 0.75rem; color: var(--slate-400); font-weight: 600; font-family: monospace; }

.summary-divider { height: 1px; background: var(--slate-100); margin: 1rem 0; }

.financial-row { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; }
.financial-label { font-size: 0.78rem; font-weight: 600; color: var(--slate-500); }
.financial-value { font-size: 0.9rem; font-weight: 800; color: var(--slate-800); font-family: monospace; }
.financial-row-highlight {
    padding: 0.75rem 1rem; margin: 0.5rem -1rem 0; border-radius: 8px;
    background: var(--rose-light);
}
.financial-row-highlight .financial-label { color: var(--rose); font-weight: 700; }
.financial-row-highlight .financial-value { font-size: 1.1rem; }

/* Progress */
.progress-section { margin-top: 1rem; }
.progress-header { display: flex; justify-content: space-between; margin-bottom: 6px; }
.progress-label { font-size: 0.7rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.04em; }
.progress-percent { font-size: 0.75rem; font-weight: 800; color: var(--indigo); }
.progress-bar { height: 8px; background: var(--slate-100); border-radius: 4px; overflow: hidden; }
.progress-fill { height: 100%; background: linear-gradient(90deg, var(--indigo), #6366f1); border-radius: 4px; transition: width 400ms var(--ease-out); }

/* Payment Card */
.payment-card {
    background: #fff; border-radius: 12px; border: 1px solid var(--border); overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.payment-header {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.875rem 1.25rem;
    border-bottom: 1px solid var(--border); background: var(--slate-50);
    font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--slate-400);
}
.payment-header i { color: var(--emerald); font-size: 0.75rem; }
.payment-form { padding: 1.25rem; }

.form-group { margin-bottom: 1.25rem; }
.form-label-custom { display: block; font-size: 0.75rem; font-weight: 700; color: var(--slate-600); margin-bottom: 6px; }
.required { color: var(--rose); }

.form-select-custom {
    width: 100%; height: 40px; padding: 0 2.5rem 0 0.75rem; border-radius: 8px; border: 1px solid var(--border);
    font-size: 0.82rem; font-weight: 600; color: var(--slate-700); background: #fff;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 0.75rem center;
    transition: border-color 160ms var(--ease-out), box-shadow 160ms var(--ease-out);
}
.form-select-custom:focus { outline: none; border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }

.form-input-custom {
    width: 100%; height: 40px; padding: 0 0.75rem; border-radius: 8px; border: 1px solid var(--border);
    font-size: 0.82rem; font-weight: 600; color: var(--slate-700); background: #fff;
    transition: border-color 160ms var(--ease-out), box-shadow 160ms var(--ease-out);
}
.form-input-custom:focus { outline: none; border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
.form-input-custom::placeholder { color: var(--slate-300); }
.form-textarea { height: auto; padding: 0.625rem 0.75rem; resize: vertical; }

.input-with-prefix { position: relative; }
.input-prefix {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    font-size: 0.78rem; font-weight: 700; color: var(--slate-400); pointer-events: none;
}
.input-with-prefix .form-input-custom { padding-left: 3rem; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

.selected-fee-info {
    background: var(--indigo-light); border-radius: 8px; padding: 0.625rem 0.875rem; margin-bottom: 1.25rem;
}
.info-row { display: flex; justify-content: space-between; align-items: center; }
.info-row span:first-child { font-size: 0.75rem; font-weight: 600; color: var(--indigo); }
.info-amount { font-size: 0.85rem; font-weight: 800; color: var(--indigo); font-family: monospace; }

.form-footer { display: flex; align-items: center; gap: 1rem; padding-top: 0.5rem; }
.btn-submit {
    display: inline-flex; align-items: center; padding: 0.75rem 2rem; border-radius: 8px;
    font-size: 0.85rem; font-weight: 800; border: none; cursor: pointer;
    background: var(--emerald); color: #fff; transition: all 160ms var(--ease-out);
}
.btn-submit:hover { background: #059669; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
.btn-submit:active { transform: scale(0.97); }
.btn-cancel {
    font-size: 0.78rem; font-weight: 600; color: var(--slate-400); text-decoration: none;
    transition: color 160ms var(--ease-out);
}
.btn-cancel:hover { color: var(--indigo); }

@media (max-width: 1024px) {
    .payment-grid { grid-template-columns: 1fr; }
}
</style>

@push('page_scripts')
<script>
// Enhanced polling strategy to wait for jQuery and Select2
(function() {
    function initPaymentForm() {
        if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.select2 !== 'undefined') {
            var $ = window.jQuery;

            try {
                // Ensure AdminLTE sidebar is visible and properly initialized
                if (typeof window.jQuery.fn.PushMenu !== 'undefined') {
                    // Re-initialize AdminLTE push menu to ensure sidebar works
                    $('[data-widget="pushmenu"]').PushMenu();
                }

                // Force sidebar to be visible
                $('body').removeClass('sidebar-collapse').addClass('sidebar-mini');
                $('.main-sidebar').show();
                $('.content-wrapper').css('margin-left', '250px');

                // Initialize Select2
                $('#student_fee_assignment_id').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $('.payment-card') // Ensure dropdown appears within form
                });

                // Handle fee selection change
                $('#student_fee_assignment_id').on('change', function() {
                    var selectedOption = $(this).find(':selected');
                    var balance = parseFloat(selectedOption.data('balance')) || 0;
                    var feeName = selectedOption.data('name') || 'Selected Fee';
                    
                    // Update amount field
                    $('#amount').val(balance);
                    $('#amount').attr('max', balance);
                    
                    // Update display
                    $('#fee-balance-display').text('KSh ' + balance.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    
                    // Add validation
                    if (balance > 0) {
                        $('#amount').removeClass('is-invalid').addClass('is-valid');
                    } else {
                        $('#amount').removeClass('is-valid').addClass('is-invalid');
                    }
                }).trigger('change');

                // Add form validation
                $('.payment-form').on('submit', function(e) {
                    var amount = parseFloat($('#amount').val()) || 0;
                    var maxAmount = parseFloat($('#amount').attr('max')) || 0;
                    
                    if (amount <= 0) {
                        e.preventDefault();
                        alert('Payment amount must be greater than 0');
                        $('#amount').focus();
                        return false;
                    }
                    
                    if (amount > maxAmount) {
                        e.preventDefault();
                        alert('Payment amount cannot exceed balance of KSh ' + maxAmount.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }));
                        $('#amount').focus();
                        return false;
                    }
                    
                    return true;
                });


            } catch (error) {
                console.error('Error initializing payment form:', error);
                // Fallback to basic functionality
                $('#student_fee_assignment_id').on('change', function() {
                    var balance = parseFloat($(this).find(':selected').data('balance')) || 0;
                    $('#amount').val(balance);
                    $('#fee-balance-display').text('KSh ' + balance.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                }).trigger('change');
            }
        } else {
            // Keep polling
            setTimeout(initPaymentForm, 100);
        }
    }

    // Start polling after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initPaymentForm, 200);
        });
    } else {
        setTimeout(initPaymentForm, 200);
    }
})();
</script>
@endpush
@endsection
