@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="font-weight-bold"><i class="fas fa-coins mr-2 text-warning"></i> Log Petty Cash Entry</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('petty-cash.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Ledger
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    @include('adminlte-templates::common.errors')

    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card card-warning card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">New Entry</h3>
                </div>

                <form action="{{ route('petty-cash.store') }}" method="POST">
                    @csrf
                    <div class="card-body">

                        {{-- Entry Type --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Entry Type <span class="text-danger">*</span></label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-success {{ old('type') !== 'debit' ? 'active' : '' }}">
                                    <input type="radio" name="type" value="credit"
                                           {{ old('type', 'credit') === 'credit' ? 'checked' : '' }}>
                                    <i class="fas fa-arrow-down mr-1"></i> Top-Up (Cash In)
                                </label>
                                <label class="btn btn-outline-danger {{ old('type') === 'debit' ? 'active' : '' }}">
                                    <input type="radio" name="type" value="debit"
                                           {{ old('type') === 'debit' ? 'checked' : '' }}>
                                    <i class="fas fa-arrow-up mr-1"></i> Disbursement (Cash Out)
                                </label>
                            </div>
                            @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            {{-- Date --}}
                            <div class="form-group col-sm-6">
                                <label class="font-weight-bold">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                       value="{{ old('date', date('Y-m-d')) }}" required>
                                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            {{-- Amount --}}
                            <div class="form-group col-sm-6">
                                <label class="font-weight-bold">Amount (KES) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text font-weight-bold">KES</span>
                                    </div>
                                    <input type="number" name="amount" step="0.01" min="0.01"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           value="{{ old('amount') }}" placeholder="0.00" required>
                                    @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Description <span class="text-danger">*</span></label>
                            <input type="text" name="description"
                                   class="form-control @error('description') is-invalid @enderror"
                                   value="{{ old('description') }}"
                                   placeholder="e.g. Office supplies, Transport fare, Tea/coffee..." required maxlength="255">
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Reference --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Reference / Receipt No. <span class="text-muted font-weight-normal">(optional)</span></label>
                            <input type="text" name="reference"
                                   class="form-control @error('reference') is-invalid @enderror"
                                   value="{{ old('reference') }}"
                                   placeholder="e.g. RCP-001, INV-2026-01" maxlength="100">
                            @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Receipt number, invoice, or any reference for this transaction.</small>
                        </div>

                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <a href="{{ route('petty-cash.index') }}" class="btn btn-default">
                            <i class="fas fa-times mr-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-warning font-weight-bold px-4">
                            <i class="fas fa-save mr-1"></i> Save Entry
                        </button>
                    </div>
                </form>
            </div>

            {{-- Quick Info --}}
            <div class="callout callout-warning">
                <h5><i class="fas fa-info-circle mr-1"></i> About Petty Cash</h5>
                <ul class="mb-0 small">
                    <li><strong>Top-Up (Credit)</strong> — when cash is added to the petty cash fund (e.g. replenishment from main account).</li>
                    <li><strong>Disbursement (Debit)</strong> — when cash is paid out for a small expense (e.g. stationery, transport, tea).</li>
                    <li>All entries are logged with your name and timestamp for audit purposes.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
