@extends('layouts.app')

@section('content')
<div class="fee-detail-wrap">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-indigo-light text-indigo">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h1 class="page-title mb-0">Fee Details</h1>
                <p class="page-subtitle mb-0">{{ $student->full_name }} <span class="mx-1">&middot;</span> <span class="mono-sm">{{ $student->admission_no }}</span></p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('fee-management.print', $student->student_id) }}" class="btn-ghost-custom" target="_blank">
                <i class="fas fa-print me-1"></i> Print
            </a>
            <a href="{{ route('fee-management.collect-payment', $student->student_id) }}" class="btn-primary-custom">
                <i class="fas fa-cash-register me-1"></i> Collect Payment
            </a>
            <a href="{{ route('fee-management.index') }}" class="btn-ghost-custom">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="detail-grid">
        {{-- Student Summary --}}
        <div class="summary-column">
            <div class="student-card">
                <div class="student-card-body">
                    @if($student->photo_url)
                        <img src="{{ $student->photo_url }}" class="student-photo-lg" alt="">
                    @else
                        <div class="student-photo-lg-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                    <h2 class="student-name-lg">{{ $student->full_name }}</h2>
                    <span class="student-admission-lg">{{ $student->admission_no }}</span>

                    @php
                        $classInfo = $student->studentClassEnrollments->first();
                        $className = $classInfo ? ($classInfo->classSection->schoolClass->name ?? 'N/A') : 'N/A';
                        $sectionName = $classInfo ? ($classInfo->classSection->section->name ?? '') : '';
                    @endphp
                    <div class="student-class-badge">{{ $className }}{{ $sectionName ? ' - ' . $sectionName : '' }}</div>

                    @php
                        $progress = $student->total_fee > 0 ? ($student->paid_fee / $student->total_fee) * 100 : 0;
                    @endphp
                    <div class="progress-section mt-3">
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

            <div class="financial-card">
                <div class="financial-card-header">
                    <i class="fas fa-wallet"></i>
                    <span>Financial Summary</span>
                </div>
                <div class="financial-card-body">
                    <div class="fin-row">
                        <span class="fin-label">Total Assigned</span>
                        <span class="fin-value">KSh {{ number_format($student->total_fee, 2) }}</span>
                    </div>
                    <div class="fin-row">
                        <span class="fin-label">Total Paid</span>
                        <span class="fin-value text-emerald">KSh {{ number_format($student->paid_fee, 2) }}</span>
                    </div>
                    <div class="fin-row fin-row-highlight">
                        <span class="fin-label">Outstanding Balance</span>
                        <span class="fin-value text-rose">KSh {{ number_format($student->balance_fee, 2) }}</span>
                    </div>
                    <div class="fin-row">
                        <span class="fin-label">Status</span>
                        <span class="fin-value">
                            @php
                                $status = $student->payment_status;
                                $statusClass = match($status) {
                                    'Paid' => 'status-paid',
                                    'Partial' => 'status-partial',
                                    'Unpaid' => 'status-unpaid',
                                    default => 'status-none'
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
                        </span>
                    </div>
                </div>
            </div>

            @if($student->parents && $student->parents->count() > 0)
            <div class="parents-card">
                <div class="parents-card-header">
                    <i class="fas fa-users"></i>
                    <span>Parents / Guardians</span>
                </div>
                <div class="parents-card-body">
                    @foreach($student->parents as $parent)
                        <div class="parent-item">
                            <div class="parent-name">{{ $parent->first_name }} {{ $parent->last_name }}</div>
                            <div class="parent-contacts">
                                <span class="parent-contact"><i class="fas fa-phone"></i> {{ $parent->phone ?? 'N/A' }}</span>
                                <span class="parent-contact"><i class="fas fa-envelope"></i> {{ $parent->user->email ?? 'N/A' }}</span>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <div class="parent-divider"></div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column: Tabs --}}
        <div class="main-column">
            <div class="tabs-card">
                <div class="tabs-nav">
                    <button class="tab-btn active" data-tab="fees">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Assigned Fees</span>
                        <span class="tab-count">{{ $student->feeAssignments->count() }}</span>
                    </button>
                    <button class="tab-btn" data-tab="payments">
                        <i class="fas fa-history"></i>
                        <span>Payment History</span>
                        <span class="tab-count">{{ $student->payments->count() }}</span>
                    </button>
                </div>

                <div class="tab-content">
                    {{-- Fees Tab --}}
                    <div class="tab-pane active" id="tab-fees">
                        <div class="table-wrap">
                            <table class="detail-table">
                                <thead>
                                    <tr>
                                        <th>Fee Type</th>
                                        <th>Due Date</th>
                                        <th class="text-right">Base Amount</th>
                                        <th class="text-right">Discount</th>
                                        <th class="text-right">Final Amount</th>
                                        <th class="text-right">Balance</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($student->feeAssignments as $fee)
                                        <tr>
                                            <td class="font-semibold">{{ $fee->feeStructure->category->name ?? 'N/A' }}</td>
                                            <td class="text-muted-sm">{{ $fee->assigned_date ? $fee->assigned_date->format('d M Y') : 'N/A' }}</td>
                                            <td class="text-right mono">KSh {{ number_format($fee->amount, 2) }}</td>
                                            <td class="text-right mono text-emerald">-KSh {{ number_format($fee->discount_amount, 2) }}</td>
                                            <td class="text-right mono font-semibold">KSh {{ number_format($fee->final_amount, 2) }}</td>
                                            <td class="text-right mono {{ $fee->balance > 0 ? 'text-rose font-semibold' : 'text-muted' }}">KSh {{ number_format($fee->balance, 2) }}</td>
                                            <td class="text-center">
                                                @php
                                                    $feeStatusClass = match($fee->payment_status) {
                                                        'paid' => 'status-paid',
                                                        'partial' => 'status-partial',
                                                        default => 'status-unpaid'
                                                    };
                                                @endphp
                                                <span class="status-badge-sm {{ $feeStatusClass }}">{{ ucfirst($fee->payment_status) }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="empty-cell">
                                                <div class="empty-mini">
                                                    <i class="fas fa-inbox"></i>
                                                    <p>No fees assigned</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Payments Tab --}}
                    <div class="tab-pane" id="tab-payments">
                        <div class="table-wrap">
                            <table class="detail-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Receipt No</th>
                                        <th>Method</th>
                                        <th class="text-right">Amount</th>
                                        <th>Applied To</th>
                                        <th>Collected By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($student->payments as $payment)
                                        <tr>
                                            <td class="text-muted-sm">{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</td>
                                            <td><span class="receipt-badge">{{ $payment->receipt_number }}</span></td>
                                            <td>
                                                <span class="method-badge">
                                                    <i class="fas fa-{{ $payment->payment_method === 'Cash' ? 'money-bill-wave' : ($payment->payment_method === 'Mobile Money' ? 'mobile-alt' : ($payment->payment_method === 'Bank Transfer' ? 'university' : ($payment->payment_method === 'Cheque' ? 'money-check' : 'credit-card'))) }}"></i>
                                                    {{ $payment->payment_method }}
                                                </span>
                                            </td>
                                            <td class="text-right mono text-emerald font-semibold">KSh {{ number_format($payment->amount, 2) }}</td>
                                            <td class="text-muted-sm">{{ $payment->studentFeeAssignment->feeStructure->category->name ?? 'N/A' }}</td>
                                            <td class="text-muted-sm">{{ $payment->collectedBy->name ?? 'System' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="empty-cell">
                                                <div class="empty-mini">
                                                    <i class="fas fa-receipt"></i>
                                                    <p>No payments recorded</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
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

.fee-detail-wrap { padding: 1.5rem 2rem; background: #f9fafb; min-height: 100vh; }

.page-title { font-size: 1.25rem; font-weight: 900; color: var(--slate-900); letter-spacing: -0.02em; }
.page-subtitle { color: var(--slate-400); font-size: 0.8rem; font-weight: 500; }
.mono-sm { font-family: monospace; font-size: 0.75rem; font-weight: 600; }

.icon-box { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.bg-indigo-light { background: var(--indigo-light); }
.text-indigo { color: var(--indigo); }
.bg-emerald-light { background: var(--emerald-light); }
.text-emerald { color: var(--emerald); }
.bg-amber-light { background: var(--amber-light); }
.text-amber { color: var(--amber); }
.bg-rose-light { background: var(--rose-light); }
.text-rose { color: var(--rose); }

.btn-primary-custom {
    display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 8px;
    font-size: 0.75rem; font-weight: 800; border: none; text-decoration: none !important; cursor: pointer;
    background: var(--emerald); color: #fff; transition: all 160ms var(--ease-out);
}
.btn-primary-custom:hover { background: #059669; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
.btn-primary-custom:active { transform: scale(0.97); }

.btn-ghost-custom {
    display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 8px;
    font-size: 0.75rem; font-weight: 700; text-decoration: none !important; cursor: pointer;
    background: #fff; border: 1px solid var(--border); color: var(--slate-700); transition: all 160ms var(--ease-out);
}
.btn-ghost-custom:hover { background: var(--slate-100); }
.btn-ghost-custom:active { transform: scale(0.97); }

/* Layout */
.detail-grid { display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; align-items: start; }
.summary-column { display: flex; flex-direction: column; gap: 1rem; }

/* Student Card */
.student-card {
    background: #fff; border-radius: 12px; border: 1px solid var(--border); overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.student-card-body { padding: 1.5rem; text-align: center; }
.student-photo-lg { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid var(--slate-100); }
.student-photo-lg-placeholder {
    width: 80px; height: 80px; border-radius: 50%; background: var(--slate-100);
    display: inline-flex; align-items: center; justify-content: center; color: var(--slate-400); font-size: 1.75rem;
}
.student-name-lg { font-size: 1.1rem; font-weight: 900; color: var(--slate-900); margin: 0.75rem 0 0.25rem; }
.student-admission-lg { font-size: 0.75rem; color: var(--slate-400); font-weight: 600; font-family: monospace; }
.student-class-badge {
    display: inline-block; padding: 4px 12px; border-radius: 5px; margin-top: 0.75rem;
    background: var(--indigo-light); color: var(--indigo); font-size: 0.72rem; font-weight: 700;
}

/* Progress */
.progress-section { text-align: left; }
.progress-header { display: flex; justify-content: space-between; margin-bottom: 6px; }
.progress-label { font-size: 0.7rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.04em; }
.progress-percent { font-size: 0.75rem; font-weight: 800; color: var(--indigo); }
.progress-bar { height: 8px; background: var(--slate-100); border-radius: 4px; overflow: hidden; }
.progress-fill { height: 100%; background: linear-gradient(90deg, var(--indigo), #6366f1); border-radius: 4px; transition: width 400ms var(--ease-out); }

/* Financial Card */
.financial-card {
    background: #fff; border-radius: 12px; border: 1px solid var(--border); overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.financial-card-header {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.875rem 1.25rem;
    border-bottom: 1px solid var(--border); background: var(--slate-50);
    font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--slate-400);
}
.financial-card-header i { color: var(--amber); font-size: 0.75rem; }
.financial-card-body { padding: 0.5rem 0; }

.fin-row { display: flex; justify-content: space-between; align-items: center; padding: 0.625rem 1.25rem; }
.fin-label { font-size: 0.78rem; font-weight: 600; color: var(--slate-500); }
.fin-value { font-size: 0.88rem; font-weight: 800; color: var(--slate-800); font-family: monospace; }
.fin-row-highlight {
    padding: 0.75rem 1.25rem; margin: 0.25rem 0.75rem; border-radius: 8px; background: var(--rose-light);
}
.fin-row-highlight .fin-label { color: var(--rose); font-weight: 700; }
.fin-row-highlight .fin-value { font-size: 1rem; }

/* Status */
.status-badge { display: inline-block; padding: 3px 10px; border-radius: 5px; font-size: 0.7rem; font-weight: 700; text-transform: capitalize; }
.status-paid { background: var(--emerald-light); color: var(--emerald); }
.status-partial { background: var(--amber-light); color: var(--amber-600); }
.status-unpaid { background: var(--rose-light); color: var(--rose); }
.status-none { background: var(--slate-100); color: var(--slate-400); }

.status-badge-sm { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.68rem; font-weight: 700; text-transform: capitalize; }

/* Parents Card */
.parents-card {
    background: #fff; border-radius: 12px; border: 1px solid var(--border); overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.parents-card-header {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.875rem 1.25rem;
    border-bottom: 1px solid var(--border); background: var(--slate-50);
    font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--slate-400);
}
.parents-card-header i { color: var(--indigo); font-size: 0.75rem; }
.parents-card-body { padding: 0.25rem 0; }
.parent-item { padding: 0.75rem 1.25rem; }
.parent-name { font-size: 0.82rem; font-weight: 700; color: var(--slate-800); margin-bottom: 4px; }
.parent-contacts { display: flex; flex-direction: column; gap: 2px; }
.parent-contact { font-size: 0.72rem; color: var(--slate-400); }
.parent-contact i { width: 14px; text-align: center; margin-right: 4px; }
.parent-divider { height: 1px; background: var(--slate-100); margin: 0 1.25rem; }

/* Tabs Card */
.tabs-card {
    background: #fff; border-radius: 12px; border: 1px solid var(--border); overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.tabs-nav { display: flex; border-bottom: 1px solid var(--border); padding: 0 0.75rem; }
.tab-btn {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.875rem 1rem; border: none; background: none;
    font-size: 0.78rem; font-weight: 700; color: var(--slate-400); cursor: pointer; position: relative;
    transition: color 160ms var(--ease-out);
}
.tab-btn:hover { color: var(--slate-600); }
.tab-btn.active { color: var(--indigo); }
.tab-btn.active::after {
    content: ''; position: absolute; bottom: -1px; left: 0; right: 0; height: 2px;
    background: var(--indigo); border-radius: 1px;
}
.tab-btn i { font-size: 0.72rem; }
.tab-count {
    display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px;
    padding: 0 6px; border-radius: 10px; background: var(--slate-100); color: var(--slate-500);
    font-size: 0.65rem; font-weight: 800;
}
.tab-btn.active .tab-count { background: var(--indigo-light); color: var(--indigo); }

.tab-content { padding: 0; }
.tab-pane { display: none; }
.tab-pane.active { display: block; }

/* Detail Table */
.table-wrap { overflow-x: auto; }
.detail-table { width: 100%; border-collapse: collapse; }
.detail-table thead { background: var(--slate-50); }
.detail-table th {
    padding: 0.75rem 1rem; font-size: 0.7rem; font-weight: 800; color: var(--slate-400);
    text-transform: uppercase; letter-spacing: 0.05em; text-align: left; border-bottom: 1px solid var(--border);
}
.detail-table td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--slate-100); vertical-align: middle; }
.detail-table tbody tr { transition: background 120ms var(--ease-out); }
.detail-table tbody tr:hover { background: var(--slate-50); }
.detail-table tbody tr:last-child td { border-bottom: none; }

.text-right { text-align: right; }
.text-center { text-align: center; }
.mono { font-family: 'SF Mono', 'Cascadia Code', 'Consolas', monospace; font-size: 0.8rem; }
.font-semibold { font-weight: 700; }
.text-muted { color: var(--slate-400); }
.text-muted-sm { font-size: 0.78rem; color: var(--slate-400); }
.text-emerald { color: var(--emerald); }
.text-rose { color: var(--rose); }

/* Receipt Badge */
.receipt-badge {
    display: inline-block; padding: 2px 8px; border-radius: 4px;
    background: var(--slate-50); border: 1px solid var(--slate-200);
    font-size: 0.72rem; font-weight: 700; font-family: monospace; color: var(--slate-600);
}

/* Method Badge */
.method-badge {
    display: inline-flex; align-items: center; gap: 4px; font-size: 0.78rem; font-weight: 600; color: var(--slate-600);
}
.method-badge i { color: var(--slate-400); font-size: 0.7rem; }

/* Empty */
.empty-cell { padding: 2.5rem 1rem !important; }
.empty-mini { text-align: center; color: var(--slate-300); }
.empty-mini i { font-size: 1.5rem; margin-bottom: 0.5rem; display: block; }
.empty-mini p { font-size: 0.82rem; font-weight: 600; color: var(--slate-400); margin: 0; }

@media (max-width: 1024px) {
    .detail-grid { grid-template-columns: 1fr; }
}
</style>

@push('page_scripts')
<script>
// Enhanced tab functionality with proper jQuery initialization
(function() {
    function initTabs() {
        if (typeof window.jQuery !== 'undefined') {
            var $ = window.jQuery;
            
            try {
                // Remove any existing event handlers to prevent duplicates
                $('.tab-btn').off('click');
                
                // Add click event handlers for tabs
                $('.tab-btn').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var tab = $(this).data('tab');
                    
                    // Remove active class from all tabs and panes
                    $('.tab-btn').removeClass('active');
                    $('.tab-pane').removeClass('active');
                    
                    // Add active class to clicked tab and corresponding pane
                    $(this).addClass('active');
                    $('#tab-' + tab).addClass('active');
                    
                    console.log('Tab switched to:', tab);
                });
                
                // Ensure first tab is active by default
                if (!$('.tab-btn.active').length) {
                    $('.tab-btn:first').addClass('active');
                    $('.tab-pane:first').addClass('active');
                }
                
                console.log('Tabs initialized successfully');
            } catch (error) {
                console.error('Error initializing tabs:', error);
                
                // Fallback: basic tab switching without jQuery
                var tabButtons = document.querySelectorAll('.tab-btn');
                var tabPanes = document.querySelectorAll('.tab-pane');
                
                tabButtons.forEach(function(button) {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        var tab = this.getAttribute('data-tab');
                        
                        // Remove active classes
                        tabButtons.forEach(function(btn) { btn.classList.remove('active'); });
                        tabPanes.forEach(function(pane) { pane.classList.remove('active'); });
                        
                        // Add active classes
                        this.classList.add('active');
                        var targetPane = document.getElementById('tab-' + tab);
                        if (targetPane) {
                            targetPane.classList.add('active');
                        }
                    });
                });
            }
        } else {
            // Keep polling for jQuery
            setTimeout(initTabs, 100);
        }
    }

    // Start initialization after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initTabs, 200);
        });
    } else {
        setTimeout(initTabs, 200);
    }
})();
</script>
@endpush
@endsection
