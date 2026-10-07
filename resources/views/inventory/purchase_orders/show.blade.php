@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-file-invoice-dollar text-primary mr-2"></i>PO: {{ $purchaseOrder->po_number }}
                        @if($purchaseOrder->requisition)
                            <a href="{{ route('inventory.requisitions.show', $purchaseOrder->requisition->requisition_id) }}"
                               class="badge badge-light border ml-2" style="vertical-align: middle;"
                               title="This order was generated from an approved requisition">
                                <i class="fas fa-file-alt text-muted mr-1"></i> from {{ $purchaseOrder->requisition->requisition_number }}
                            </a>
                        @endif
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('inventory.purchase-orders.index') }}" class="btn btn-default shadow-sm border mr-2">
                        <i class="fas fa-chevron-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <!-- Order Summary -->
            <div class="col-md-9">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="font-weight-bold mb-0">Order Details</h6>
                            <span>{!! $purchaseOrder->status_badge !!}</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light small uppercase text-muted">
                                    <tr>
                                        <th class="pl-4">Item Name</th>
                                        <th class="text-center">Qty Ordered</th>
                                        <th class="text-center">Unit Price</th>
                                        <th class="text-right pr-4">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseOrder->items as $item)
                                        <tr>
                                            <td class="pl-4">
                                                <div class="font-weight-bold text-dark">{{ $item->item->name }}</div>
                                                <div class="small text-muted">{{ $item->item->item_code }}</div>
                                            </td>
                                            <td class="text-center">{{ $item->quantity }} {{ $item->item->unit }}</td>
                                            <td class="text-center">KES {{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-right pr-4 font-weight-bold text-dark">KES {{ number_format($item->total_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer Stats -->
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <table class="table mb-0">
                                    <tr>
                                        <td class="border-top-0 text-muted">Subtotal:</td>
                                        <td class="border-top-0 text-right font-weight-bold">KES {{ number_format($purchaseOrder->sub_total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tax (16% VAT):</td>
                                        <td class="text-right font-weight-bold text-info">KES {{ number_format($purchaseOrder->tax_amount, 2) }}</td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td class="font-weight-bold h6">Grand Total:</td>
                                        <td class="text-right font-weight-bold h5 text-primary">KES {{ number_format($purchaseOrder->grand_total, 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supplier & Receiving -->
            <div class="col-md-3">
                <!-- Supplier Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="font-weight-bold mb-0"><i class="fas fa-truck mr-2 text-muted"></i>Supplier Information</h6>
                    </div>
                    <div class="card-body py-3">
                        <h6 class="font-weight-bold text-dark mb-1">{{ $purchaseOrder->supplier->name }}</h6>
                        <div class="small text-muted mb-2">{{ $purchaseOrder->supplier->code }}</div>
                        <div class="small text-muted mb-1"><i class="fas fa-phone mr-1"></i> {{ $purchaseOrder->supplier->phone }}</div>
                        <div class="small text-muted mb-1"><i class="fas fa-envelope mr-1"></i> {{ $purchaseOrder->supplier->email }}</div>
                    </div>
                </div>

                <!-- Dates Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body py-1">
                        <ul class="list-group list-group-unbordered mb-0">
                            <li class="list-group-item d-flex justify-content-between border-top-0 px-0">
                                <b class="text-muted small">Order Date</b>
                                <span class="small">{{ $purchaseOrder->order_date->format('d M Y') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between border-bottom-0 px-0">
                                <b class="text-muted small">Delivery Expected</b>
                                <span class="small font-weight-bold text-info">{{ $purchaseOrder->expected_delivery_date->format('d M Y') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Payment / Receiving -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="font-weight-bold mb-0"><i class="fas fa-money-bill-wave mr-2 text-success"></i>Payment</h6>
                    </div>
                    <div class="card-body py-3">
                        @php $paymentStatus = $purchaseOrder->derivedPaymentStatus(); @endphp
                        <ul class="list-group list-group-unbordered mb-2">
                            <li class="list-group-item d-flex justify-content-between border-top-0 px-0">
                                <b class="text-muted small">Arrangement</b>
                                <span class="small font-weight-bold">{{ $purchaseOrder->payment_arrangement ? \App\Models\PurchaseOrder::ARRANGEMENTS[$purchaseOrder->payment_arrangement] : 'Not set (legacy)' }}</span>
                            </li>
                            @if($purchaseOrder->payment_due_date)
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <b class="text-muted small">Due Date</b>
                                    <span class="small font-weight-bold {{ $paymentStatus === 'overdue' ? 'text-danger' : '' }}">{{ $purchaseOrder->payment_due_date->format('d M Y') }}</span>
                                </li>
                            @endif
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <b class="text-muted small">Paid</b>
                                <span class="small">KES {{ number_format($purchaseOrder->paidAmount(), 2) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between border-bottom-0 px-0">
                                <b class="text-muted small">Outstanding</b>
                                <span class="small font-weight-bold {{ $purchaseOrder->outstandingBalance() > 0 ? 'text-danger' : 'text-success' }}">KES {{ number_format($purchaseOrder->outstandingBalance(), 2) }}</span>
                            </li>
                        </ul>
                        <div class="mb-2">
                            <span class="badge badge-{{ ['paid' => 'success', 'partially_paid' => 'info', 'overdue' => 'danger'][$paymentStatus] ?? 'warning' }}">
                                {{ ['paid' => 'Paid', 'partially_paid' => 'Partially Paid', 'overdue' => 'Overdue'][$paymentStatus] ?? 'Unpaid' }}
                            </span>
                        </div>

                        {{-- Same gate as PurchaseOrderController::arrange (finance.manage).
                             Showing this form on inventory.manage alone would offer a button
                             the server answers with 403. --}}
                        @can('finance.manage')
                            @if(! $purchaseOrder->payment_arrangement && $purchaseOrder->paidAmount() == 0)
                                <form action="{{ route('inventory.purchase-orders.arrange', $purchaseOrder->po_id) }}" method="POST" class="border-top pt-2 mt-2">
                                    @csrf
                                    <label class="small font-weight-bold">Payment Arrangement</label>
                                    <select name="payment_arrangement" class="form-control form-control-sm mb-2" required>
                                        <option value="immediate">Immediate Payment</option>
                                        <option value="credit">Credit / Pay Later</option>
                                    </select>
                                    <input type="date" name="payment_due_date" class="form-control form-control-sm mb-2" placeholder="Due date (credit)">
                                    <button type="submit" class="btn btn-sm btn-outline-primary btn-block">Save Arrangement</button>
                                </form>
                            @endif
                        @endcan

                        @can('finance.manage')
                            @if($purchaseOrder->outstandingBalance() > 0 && $purchaseOrder->isReceived())
                                <form action="{{ route('inventory.purchase-orders.pay', $purchaseOrder->po_id) }}" method="POST" class="border-top pt-2 mt-2">
                                    @csrf
                                    <label class="small font-weight-bold">Record Supplier Payment</label>
                                    <input type="number" name="amount" class="form-control form-control-sm mb-2" step="0.01" min="0.01" max="{{ $purchaseOrder->outstandingBalance() }}" value="{{ $purchaseOrder->outstandingBalance() }}" required>
                                    <select name="payment_method" class="form-control form-control-sm mb-2" required>
                                        @foreach(\App\Models\PurchaseOrderPayment::PAYMENT_METHODS as $method)
                                            <option value="{{ $method }}">{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
                                        @endforeach
                                    </select>
                                    <select name="bank_account_id" class="form-control form-control-sm mb-2">
                                        <option value="">Cash (no bank movement)</option>
                                        @foreach(\App\Models\BankAccount::where('status', 'active')->get() as $account)
                                            <option value="{{ $account->account_id }}">{{ $account->account_name }} — KES {{ number_format($account->current_balance, 2) }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="reference_number" class="form-control form-control-sm mb-2" placeholder="Reference / cheque no.">
                                    <button type="submit" class="btn btn-success btn-block btn-sm" onclick="return confirm('Record this supplier payment and deduct the bank account?')">
                                        <i class="fas fa-hand-holding-usd mr-1"></i> Pay Supplier
                                    </button>
                                </form>
                            @elseif($purchaseOrder->outstandingBalance() > 0 && ! $purchaseOrder->isReceived())
                                <p class="small text-muted mb-0"><i class="fas fa-info-circle mr-1"></i> Payment opens once the goods are received.</p>
                            @endif
                        @endcan

                        @can('finance.view')
                            @cannot('finance.manage')
                                @if($purchaseOrder->outstandingBalance() > 0)
                                    <p class="small text-muted mb-0">Payments are recorded by the finance office.</p>
                                @endif
                            @endcannot
                        @endcan
                    </div>
                </div>

                @if($purchaseOrder->payments->count())
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3"><h6 class="font-weight-bold mb-0">Payment History</h6></div>
                        <div class="card-body py-2">
                            @foreach($purchaseOrder->payments as $payment)
                                <div class="d-flex justify-content-between border-bottom py-2 small">
                                    <div>
                                        <strong>{{ \App\Support\Money::format($payment->amount) }}</strong>
                                        <span class="text-muted">{{ $payment->payment_date->format('d M Y') }}</span>
                                    </div>
                                    <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}{{ $payment->bankAccount ? ' · ' . $payment->bankAccount->account_name : '' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Receive Action -->
                @if(in_array($purchaseOrder->status, ['Approved', 'Sent']))
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-header bg-light border-bottom-0 pt-3">
                            <h6 class="font-weight-bold mb-0">Quick Action</h6>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted italic">Once the shipment arrives, use the button below to update inventory.</p>
                            @can('inventory.approve')
                                <form action="{{ route('inventory.purchase-orders.receive', $purchaseOrder->po_id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-block shadow-sm" onclick="return confirm('Confirm all items have been received at the warehouse?')">
                                        <i class="fas fa-check-double mr-2"></i> Receive Stock
                                    </button>
                                </form>
                            @elsecan('inventory.view')
                                <button class="btn btn-success btn-block shadow-sm" disabled title="Requires receiving permission">Receive Stock</button>
                            @endcan
                        </div>
                    </div>
                @elseif($purchaseOrder->status == 'Pending_Approval')
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body">
                            <p class="small text-muted italic mb-0">This order is awaiting approval from management.</p>
                        </div>
                    </div>
                @elseif($purchaseOrder->status == 'Fully_Received')
                    <div class="card border-0 shadow-sm border-left border-success bg-white">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle text-success fa-lg mr-2"></i>
                                <span class="font-weight-bold">Stock Received</span>
                            </div>
                            <div class="small text-muted mb-1">Processed by:</div>
                            <div class="font-weight-bold mb-1">{{ $purchaseOrder->receivedBy->name ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $purchaseOrder->received_date ? $purchaseOrder->received_date->format('d M Y H:i') : '' }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .list-group-item { padding: 0.75rem 0; border-left: 0; border-right: 0; }
    </style>
@endsection
