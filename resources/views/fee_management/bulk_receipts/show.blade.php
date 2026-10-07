@extends('layouts.app')

@section('content')
    @php
        $allocated = $receipt->allocatedAmount();
        $remaining = $receipt->remainingAmount();
        $percent = $receipt->amount > 0 ? min(100, round(($allocated / $receipt->amount) * 100, 1)) : 0;
    @endphp

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1><i class="fas fa-hand-holding-usd text-success mr-2"></i>{{ $receipt->sponsor_name }}</h1>
                    <p class="text-muted mb-0">
                        {{ ucfirst(str_replace('_', ' ', $receipt->sponsor_type)) }}
                        @if($receipt->reference_number) &middot; Ref {{ $receipt->reference_number }} @endif
                        &middot; Received {{ $receipt->received_date->format('d M, Y') }}
                        @if($receipt->term) &middot; {{ $receipt->term->name }} @endif
                        @if($receipt->academicYear) &middot; {{ $receipt->academicYear->name }} @endif
                    </p>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ route('fees.bulk-receipts.receipt', $receipt->id) }}" class="btn btn-outline-secondary shadow-sm">
                        <i class="fas fa-print mr-1"></i> Print Receipt
                    </a>
                    <a href="{{ route('fees.bulk-receipts.index') }}" class="btn btn-default border shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @if($receipt->isReversed())
            <div class="alert alert-danger">
                <i class="fas fa-ban mr-1"></i>
                <strong>This receipt was reversed.</strong> {{ $receipt->reversal_reason }}
                @if($receipt->reversedBy) — by {{ $receipt->reversedBy->name }} on {{ $receipt->reversed_at->format('d M Y') }}. @endif
            </div>
        @endif

        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small font-weight-bold text-uppercase">Amount Received</div>
                        <div class="h3 font-weight-bold mb-0">{{ \App\Support\Money::format($receipt->amount) }}</div>
                        <div class="small text-muted mt-1">{{ ucfirst(str_replace('_', ' ', $receipt->payment_method)) }} @if($receipt->bankAccount) &middot; {{ $receipt->bankAccount->account_name }} @endif</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small font-weight-bold text-uppercase">Allocated to Students</div>
                        <div class="h3 font-weight-bold mb-0 text-info">{{ \App\Support\Money::format($allocated) }}</div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: {{ $percent }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 {{ $remaining > 0 ? 'border-left border-warning' : '' }}">
                    <div class="card-body">
                        <div class="text-muted small font-weight-bold text-uppercase">Remaining (Unallocated)</div>
                        <div class="h3 font-weight-bold mb-0 {{ $remaining > 0 ? 'text-warning' : 'text-muted' }}">{{ \App\Support\Money::format($remaining) }}</div>
                        <div class="small text-muted mt-1">Stays traceable on this receipt until allocated.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            @can('fees.manage')
            <div class="col-lg-5 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="font-weight-bold mb-0"><i class="fas fa-user-plus mr-2 text-success"></i>Allocate to Students</h6>
                    </div>
                    <div class="card-body">
                        @if($receipt->isReversed())
                            <p class="text-muted small mb-0"><i class="fas fa-ban mr-1"></i> Reversed receipts cannot be allocated.</p>
                        @elseif($remaining <= 0)
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-check-circle fa-2x text-success d-block mb-2"></i>
                                This receipt is fully allocated.
                            </div>
                        @else
                            <p class="small text-muted">Each student receives an ordinary fee payment allocated to their oldest outstanding charges. Money is <strong>not</strong> posted twice — this only distributes the receipt.</p>
                            <form id="allocation-form" action="{{ route('fees.bulk-receipts.allocate', $receipt->id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label class="small font-weight-bold">Class filter (optional)</label>
                                    <select name="class_filter" id="class_filter" class="form-control form-control-sm select2" onchange="window.location = '{{ route('fees.bulk-receipts.show', $receipt->id) }}?class_id=' + this.value;">
                                        <option value="">All classes</option>
                                        @foreach($classes as $classId => $className)
                                            <option value="{{ $classId }}" {{ $classFilter == $classId ? 'selected' : '' }}>{{ $className }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="table-responsive" style="max-height: 340px; overflow-y: auto;">
                                    <table class="table table-sm table-hover mb-2">
                                        <thead class="bg-light small">
                                            <tr>
                                                <th style="width: 28px;"></th>
                                                <th>Student</th>
                                                <th class="text-right">Balance</th>
                                                <th style="width: 130px;">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($students as $student)
                                                <tr>
                                                    <td><input type="checkbox" class="student-check" value="{{ $student->student_id }}" data-balance="{{ $student->balance }}"></td>
                                                    <td class="small">
                                                        <div class="font-weight-bold">{{ $student->name }}</div>
                                                        <div class="text-muted">{{ $student->admission_no }}</div>
                                                    </td>
                                                    <td class="text-right small">{{ \App\Support\Money::format($student->balance) }}</td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm alloc-amount"
                                                               name="allocations[{{ $student->student_id }}]"
                                                               data-student="{{ $student->student_id }}"
                                                               min="0" step="0.01" max="{{ $remaining }}"
                                                               placeholder="0.00" disabled>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-center text-muted py-4">No students with an outstanding balance @if($classFilter) in this class @endif.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                    <div>
                                        <small class="text-muted">Selected total: <strong id="alloc-total">KES 0.00</strong></small>
                                        <div><small class="text-warning font-weight-bold" id="alloc-warning"></small></div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mr-1" id="distribute-equal">
                                            <i class="fas fa-equals mr-1"></i> Distribute equally
                                        </button>
                                        <button type="submit" class="btn btn-success" id="alloc-submit" disabled>
                                            <i class="fas fa-check mr-1"></i> Apply Allocation
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            @endcan

            <div class="{{ \Illuminate\Support\Facades\Gate::allows('fees.manage') ? 'col-lg-7' : 'col-12' }} mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold mb-0"><i class="fas fa-users mr-2 text-info"></i>Allocations ({{ $receipt->allocations->count() }})</h6>
                        @can('fees.manage')
                            @if($receipt->canBeReversed())
                                <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#reverse-receipt-modal">
                                    <i class="fas fa-ban mr-1"></i> Reverse Receipt
                                </button>
                            @endif
                        @endcan
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light small text-muted uppercase">
                                <tr>
                                    <th class="pl-4">Student</th>
                                    <th>Receipt No.</th>
                                    <th class="text-right">Amount</th>
                                    <th>Status</th>
                                    <th class="pr-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($receipt->allocations as $payment)
                                    @php $student = $payment->studentFeeAssignment->student ?? null; @endphp
                                    <tr class="{{ $payment->isReversed() ? 'text-muted' : '' }}">
                                        <td class="pl-4">
                                            <div class="font-weight-bold {{ $payment->isReversed() ? '' : 'text-dark' }}">
                                                {{ $student ? trim($student->first_name . ' ' . $student->last_name) : 'Student #' . ($student->student_id ?? '?') }}
                                            </div>
                                            <div class="small text-muted">{{ $student->admission_no ?? '' }}</div>
                                        </td>
                                        <td class="small">{{ $payment->receipt_number }}</td>
                                        <td class="text-right font-weight-bold">{{ \App\Support\Money::format($payment->amount) }}</td>
                                        <td>
                                            @if($payment->isReversed())
                                                <span class="badge badge-secondary">Reversed</span>
                                            @else
                                                <span class="badge badge-success">Active</span>
                                            @endif
                                        </td>
                                        <td class="pr-4 text-right">
                                            @can('fees.manage')
                                                @if(! $payment->isReversed() && ! $receipt->isReversed())
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                            data-toggle="modal" data-target="#reverse-allocation-modal-{{ $payment->payment_id }}"
                                                            title="Reverse this allocation and return the money to the receipt">
                                                        <i class="fas fa-undo mr-1"></i> Reverse
                                                    </button>
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-users fa-2x d-block mb-2 opacity-50"></i>
                                            No allocations yet.@can('fees.manage') Use the form to distribute this receipt to students. @endcan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($receipt->allocations->count() > 10)
                        <div class="card-footer bg-white small text-muted">
                            Showing all {{ $receipt->allocations->count() }} allocations.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @can('fees.manage')
    {{-- Reverse allocation modals --}}
    @foreach($receipt->allocations->where('reversed_at', null) as $payment)
        <div class="modal fade" id="reverse-allocation-modal-{{ $payment->payment_id }}" tabindex="-1">
            <div class="modal-dialog">
                <form action="{{ route('fees.bulk-receipts.allocations.reverse', [$receipt->id, $payment->payment_id]) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reverse Allocation</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>Reverse <strong>{{ \App\Support\Money::format($payment->amount) }}</strong> for
                           <strong>{{ $payment->studentFeeAssignment->student->full_name ?? 'this student' }}</strong>?</p>
                        <p class="small text-muted">The student's balance is restored and the amount returns to this receipt's remaining balance. The payment stays on record for audit.</p>
                        <input type="text" name="reason" class="form-control" placeholder="Reason for reversal" required maxlength="500">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reverse Allocation</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- Reverse receipt modal --}}
    @if($receipt->canBeReversed())
        <div class="modal fade" id="reverse-receipt-modal" tabindex="-1">
            <div class="modal-dialog">
                <form action="{{ route('fees.bulk-receipts.reverse', $receipt->id) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reverse Bulk Receipt</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>Reverse the receipt of <strong>{{ \App\Support\Money::format($receipt->amount) }}</strong> from <strong>{{ $receipt->sponsor_name }}</strong>?</p>
                        <p class="small text-muted">Only possible while nothing is allocated. @if($receipt->bankAccount) The recorded bank deposit will be undone. @endif</p>
                        <input type="text" name="reason" class="form-control" placeholder="Reason for reversal" required maxlength="500">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reverse Receipt</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    @endcan

    @push('page_scripts')
    <script>
        (function () {
            const remaining = {{ (float) $remaining }};
            const fmt = new Intl.NumberFormat('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            function selectedRows() {
                return Array.from(document.querySelectorAll('.student-check:checked'));
            }

            function refreshTotals() {
                let total = 0;
                selectedRows().forEach(function (box) {
                    const row = box.closest('tr');
                    const input = row.querySelector('.alloc-amount');
                    let value = parseFloat(input.value) || 0;
                    // Clamp to the receipt's remaining balance.
                    if (value > remaining) {
                        value = remaining;
                        input.value = value.toFixed(2);
                    }
                    total += value;
                });

                document.getElementById('alloc-total').textContent = 'KES ' + fmt.format(total);

                const warning = document.getElementById('alloc-warning');
                if (total > remaining) {
                    warning.textContent = 'Total exceeds the remaining ' + fmt.format(remaining) + '.';
                } else {
                    warning.textContent = '';
                }

                document.getElementById('alloc-submit').disabled = total <= 0 || total > remaining;
            }

            document.querySelectorAll('.student-check').forEach(function (box) {
                box.addEventListener('change', function () {
                    const row = box.closest('tr');
                    const input = row.querySelector('.alloc-amount');
                    input.disabled = !box.checked;
                    if (!box.checked) input.value = '';
                    refreshTotals();
                });
            });

            document.querySelectorAll('.alloc-amount').forEach(function (input) {
                input.addEventListener('input', refreshTotals);
            });

            const distribute = document.getElementById('distribute-equal');
            if (distribute) {
                distribute.addEventListener('click', function () {
                    const boxes = selectedRows();
                    if (boxes.length === 0) return;
                    const share = Math.floor((remaining / boxes.length) * 100) / 100;
                    let leftover = remaining - share * boxes.length;
                    boxes.forEach(function (box, index) {
                        const input = box.closest('tr').querySelector('.alloc-amount');
                        // Give the residue cents to the first students.
                        const amount = share + (index < Math.round(leftover * 100) ? 0.01 : 0);
                        input.value = amount.toFixed(2);
                    });
                    refreshTotals();
                });
            }

            // Default each ticked student to their outstanding balance (capped at remaining).
            document.querySelectorAll('.student-check').forEach(function (box) {
                box.addEventListener('change', function () {
                    if (box.checked) {
                        const input = box.closest('tr').querySelector('.alloc-amount');
                        const balance = parseFloat(box.dataset.balance) || 0;
                        input.value = Math.min(balance, remaining).toFixed(2);
                        refreshTotals();
                    }
                });
            });
        })();
    </script>
    @endpush
@endsection
