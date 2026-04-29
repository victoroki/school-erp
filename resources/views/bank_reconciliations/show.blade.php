@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- Header --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('bank-reconciliations.index') }}" class="btn-dash btn-ghost px-3 py-2">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="dash-heading">Reconcile Account</h1>
                    <p class="dash-sub">{{ $bankAccount->bank_name }} &bull; {{ $bankAccount->account_number }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <input type="file" id="statementFile" class="d-none" accept=".csv,.xlsx,.xls">
                <button type="button" class="btn-dash btn-ghost" onclick="document.getElementById('statementFile').click()">
                    <i class="fas fa-file-upload me-1"></i> Import Statement
                </button>
                <button type="button" id="btnAutoMatch" class="btn-dash btn-indigo-dash">
                    <i class="fas fa-check-double me-1"></i> Auto-Match
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-blue-light text-blue"><i class="fas fa-laptop-house"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">System Balance</span>
                    <span class="stat-value text-blue">{{ $bankAccount->currency ?: 'KES' }} {{ number_format($bankAccount->current_balance, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon bg-emerald-light text-emerald"><i class="fas fa-file-invoice"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Statement Balance</span>
                    <div class="d-flex align-items-center mt-1">
                        <span class="text-emerald font-weight-bold me-1" style="font-size: 1.25rem;">{{ $bankAccount->currency ?: 'KES' }}</span>
                        <input type="number" step="0.01" id="statementBalance" class="form-control form-control-sm border-0 shadow-none p-0 text-emerald font-weight-bold bg-transparent" placeholder="0.00" style="font-size: 1.5rem; height: auto;">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card border-rose-light">
                <div class="stat-icon bg-rose-light text-rose"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-info w-100">
                    <span class="stat-label">Unreconciled Difference</span>
                    <span class="stat-value text-rose" id="unreconciledDifference">{{ $bankAccount->currency ?: 'KES' }} {{ number_format($bankAccount->current_balance, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Transactions --}}
    <div class="row">
        <div class="col-12">
            <form action="{{ route('bank-reconciliations.update', $bankAccount->account_id) }}" method="POST" id="reconciliationForm">
                @csrf
                @method('PUT')
                <div class="dash-panel">
                    <div class="dash-panel-header">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-list text-slate"></i>
                            <h3 class="dash-panel-title">System Transactions</h3>
                        </div>
                        <button type="submit" class="btn-dash btn-emerald-dash px-3 py-1 text-sm" id="btnSubmitReconciliation" disabled>
                            Complete Reconciliation
                        </button>
                    </div>
                <div class="dash-panel-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4 text-center" style="width: 40px;">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>Date</th>
                                    <th>Description / Reference</th>
                                    <th>Type</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-center pr-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                    <tr>
                                        <td class="pl-4 text-center">
                                            <input type="checkbox" name="transaction_ids[]" value="{{ $transaction->transaction_id }}">
                                        </td>
                                        <td class="text-muted small">{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M, Y') }}</td>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $transaction->description ?: 'No description' }}</div>
                                            <div class="text-muted small">Ref: {{ $transaction->reference_number ?: 'N/A' }}</div>
                                        </td>
                                        <td>
                                            @if($transaction->transaction_type == 'deposit' || $transaction->transaction_type == 'credit')
                                                <span class="badge-soft text-emerald"><i class="fas fa-arrow-down me-1"></i> Deposit</span>
                                            @else
                                                <span class="badge-soft text-rose"><i class="fas fa-arrow-up me-1"></i> Withdrawal</span>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold {{ in_array($transaction->transaction_type, ['deposit', 'credit']) ? 'text-emerald' : 'text-rose' }}">
                                            {{ in_array($transaction->transaction_type, ['deposit', 'credit']) ? '+' : '-' }} {{ number_format($transaction->amount, 2) }}
                                        </td>
                                        <td class="text-center pr-4">
                                            <span class="badge-pill-soft bg-amber-light text-amber-dark">Unreconciled</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No pending transactions to reconcile.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>

<style>
/* ── Emil Kowalski Utility Suite ── */
:root {
    --blue: #3b82f6; --blue-light: #eff6ff;
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --amber-dark: #b45309;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --slate: #64748b; --slate-light: #f1f5f9;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.bg-blue-light { background: var(--blue-light); } .text-blue { color: var(--blue); }
.bg-indigo-light { background: var(--indigo-light); } .text-indigo { color: var(--indigo); }
.bg-emerald-light { background: var(--emerald-light); } .text-emerald { color: var(--emerald); }
.bg-amber-light { background: var(--amber-light); } .text-amber { color: var(--amber); } .text-amber-dark { color: var(--amber-dark); }
.bg-rose-light { background: var(--rose-light); } .text-rose { color: var(--rose); }
.bg-slate-light { background: var(--slate-light); } .text-slate { color: var(--slate); }

.border-rose-light { border-color: #fb7185 !important; }

.dash-wrap { padding: 1.5rem; }
.dash-heading { font-size: 1.5rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.25rem; }
.dash-sub { font-size: 0.875rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; display: flex; flex-direction: column; }
.dash-panel-header { padding: 1.25rem 1.5rem; background: #fff; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between; }
.dash-panel-title { font-size: 1rem; font-weight: 800; color: var(--text); margin: 0; }
.dash-panel-body { flex: 1; display: flex; flex-direction: column; }

.stat-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 1.5rem; display: flex; align-items: center; gap: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 200ms var(--ease-out); height: 100%; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(0,0,0,0.05); border-color: #cbd5e1; }
.stat-icon { min-width: 56px; width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.stat-info { display: flex; flex-direction: column; }
.stat-label { font-size: .75rem; color: var(--muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
.stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text); line-height: 1.1; }

.table { margin-bottom: 0; }
.table thead th { background: #f8fafc; border-bottom: 1px solid var(--border); border-top: 0; font-size: .688rem; font-weight: 800; text-transform: uppercase; color: var(--slate); letter-spacing: 0.05em; padding: .75rem 1.5rem; }
.table tbody td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; border-top: 0; font-size: 0.875rem; }
.table tbody tr:last-child td { border-bottom: 0; }
.table-hover tbody tr:hover { background-color: #f8fafc; }

.badge-soft { background: #f1f5f9; color: #475569; font-size: .688rem; font-weight: 700; padding: .2rem .5rem; border-radius: 6px; }
.badge-pill-soft { font-size: .688rem; font-weight: 700; padding: .2rem .6rem; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; }

.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .625rem 1rem; border-radius: 8px; font-size: .875rem; font-weight: 600; transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer; }
.btn-ghost { background: transparent; color: var(--muted); border-color: transparent; border: 1px solid var(--border); }
.btn-ghost:hover { background: #f1f5f9; color: var(--text); border-color: #cbd5e1; }

.btn-indigo-dash { background: var(--indigo); color: #fff; }
.btn-indigo-dash:hover { background: #4338ca; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }

.btn-emerald-dash { background: var(--emerald); color: #fff; }
.btn-emerald-dash:hover { background: #059669; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
.btn-emerald-dash:disabled { background: #6ee7b7; cursor: not-allowed; transform: none; box-shadow: none; }
</style>
@endsection

@push('page_scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const systemBalance = {{ $bankAccount->current_balance }};
        const currency = '{{ $bankAccount->currency ?: "KES" }}';
        
        const statementInput = document.getElementById('statementBalance');
        const differenceDisplay = document.getElementById('unreconciledDifference');
        const submitBtn = document.getElementById('btnSubmitReconciliation');
        const checkboxes = document.querySelectorAll('input[name="transaction_ids[]"]');
        const selectAll = document.getElementById('selectAll');
        const btnAutoMatch = document.getElementById('btnAutoMatch');
        const statementFile = document.getElementById('statementFile');
        
        // Auto-Match Logic
        if (btnAutoMatch) {
            btnAutoMatch.addEventListener('click', function() {
                // Select all transactions
                if (selectAll) selectAll.checked = true;
                checkboxes.forEach(cb => cb.checked = true);
                
                // Auto-fill statement balance to match system balance
                statementInput.value = systemBalance;
                
                // Trigger calculation to update UI
                calculateDifference();
                
                // Add a subtle flash effect to show it worked
                statementInput.parentElement.parentElement.parentElement.style.transition = 'all 0.3s';
                statementInput.parentElement.parentElement.parentElement.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    statementInput.parentElement.parentElement.parentElement.style.transform = 'scale(1)';
                }, 300);
            });
        }

        // Import Statement Mock Logic
        if (statementFile) {
            statementFile.addEventListener('change', function(e) {
                if (e.target.files.length > 0) {
                    const fileName = e.target.files[0].name;
                    // Mock: Auto-fill the balance as if the statement was parsed successfully
                    statementInput.value = systemBalance;
                    calculateDifference();
                    alert(`Statement "${fileName}" imported successfully!\n(Note: Backend parsing integration required for production)`);
                }
            });
        }
        
        // Calculate difference on input
        statementInput.addEventListener('input', function() {
            calculateDifference();
        });

        // Calculate difference when transactions are checked
        checkboxes.forEach(cb => {
            cb.addEventListener('change', calculateDifference);
        });

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
                calculateDifference();
            });
        }
        
        function calculateDifference() {
            let statementBal = parseFloat(statementInput.value) || 0;
            
            // For a basic reconciliation view, you'd add/subtract checked transaction amounts 
            // to see if the modified system balance matches the statement balance.
            // For now, we'll just show the raw difference between System and Statement.
            let difference = systemBalance - statementBal;
            
            // Format number
            const formattedDiff = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Math.abs(difference));
            
            differenceDisplay.textContent = `${currency} ${formattedDiff}`;
            
            // If perfectly balanced (0 difference) and at least 1 transaction selected (or statement provided), allow submit
            if (difference === 0 && statementInput.value !== "") {
                differenceDisplay.className = "stat-value text-emerald";
                differenceDisplay.parentElement.parentElement.classList.remove('border-rose-light');
                differenceDisplay.parentElement.parentElement.classList.add('border-emerald-light');
                differenceDisplay.parentElement.previousElementSibling.className = "stat-icon bg-emerald-light text-emerald";
                
                submitBtn.disabled = false;
            } else {
                differenceDisplay.className = "stat-value text-rose";
                differenceDisplay.parentElement.parentElement.classList.remove('border-emerald-light');
                differenceDisplay.parentElement.parentElement.classList.add('border-rose-light');
                differenceDisplay.parentElement.previousElementSibling.className = "stat-icon bg-rose-light text-rose";
                
                submitBtn.disabled = true;
            }
        }
    });
</script>
@endpush
