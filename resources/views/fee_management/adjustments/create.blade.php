@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Request Fee Adjustment</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('fees.adjustments.index') }}" style="border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card" style="border: 1px solid #e5e7eb; border-radius: 12px;">
            <div class="card-header bg-white" style="border-bottom: 1px solid #e5e7eb; padding: 16px 24px;">
                <h3 class="card-title mb-0" style="font-weight: 600; color: #1f2937;">
                    <span style="color: #0073e7;">✦</span> Adjustment Details
                </h3>
            </div>

            {!! Form::open(['route' => 'fees.adjustments.store']) !!}
            <div class="card-body" style="padding: 24px;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #374151; font-size: 0.875rem;">Student</label>
                            <select name="student_id" id="student_id" class="form-control" style="border-radius: 8px; border: 1px solid #d1d5db;" required>
                                <option value="">Select Student</option>
                                @foreach($students ?? [] as $id => $name)
                                    <option value="{{ $id }}" {{ old('student_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #374151; font-size: 0.875rem;">Academic Year</label>
                            <select name="academic_year_id" id="academic_year_id" class="form-control" style="border-radius: 8px; border: 1px solid #d1d5db;">
                                @foreach($academicYears as $id => $name)
                                    <option value="{{ $id }}" {{ old('academic_year_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #374151; font-size: 0.875rem;">Fee Category</label>
                            <select name="student_fee_assignment_id" id="student_fee_assignment_id" class="form-control" style="border-radius: 8px; border: 1px solid #d1d5db;" required>
                                <option value="">Select Fee Category</option>
                            </select>
                            <small class="text-muted">Select student first to see available fees</small>
                        </div>
                    </div>
                </div>

                <div id="fee_details" style="display: none;" class="mt-3 p-3" style="background: #f8fafc; border-radius: 8px;">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-2" style="background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <small style="color: #6b7280;">Original Amount</small>
                                <div id="original_amount_display" style="font-size: 1.25rem; font-weight: 700; color: #1f2937;">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #374151; font-size: 0.875rem;">Adjustment Type</label>
                            <select name="adjustment_type" id="adjustment_type" class="form-control" style="border-radius: 8px; border: 1px solid #d1d5db;" required>
                                <option value="reduction" {{ old('adjustment_type') == 'reduction' ? 'selected' : '' }}>Reduction</option>
                                <option value="increase" {{ old('adjustment_type') == 'increase' ? 'selected' : '' }}>Increase</option>
                                <option value="waiver" {{ old('adjustment_type') == 'waiver' ? 'selected' : '' }}>Full Waiver</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #374151; font-size: 0.875rem;">New Amount (KES)</label>
                            <input type="number" name="new_amount" id="new_amount" class="form-control" style="border-radius: 8px; border: 1px solid #d1d5db;" step="0.01" min="0" value="{{ old('new_amount') }}" required>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-3">
                    <label style="font-weight: 600; color: #374151; font-size: 0.875rem;">Reason for Adjustment</label>
                    <textarea name="reason" class="form-control" rows="4" style="border-radius: 8px; border: 1px solid #d1d5db;" placeholder="Provide detailed justification for this adjustment..." required>{{ old('reason') }}</textarea>
                </div>
            </div>

            <div class="card-footer bg-white" style="border-top: 1px solid #e5e7eb; padding: 16px 24px;">
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #0073e7 0%, #0056b3 100%); border: none; border-radius: 8px; padding: 10px 24px;">
                    <i class="fas fa-paper-plane mr-2"></i> Submit for Approval
                </button>
                <a href="{{ route('fees.adjustments.index') }}" class="btn btn-default ml-2" style="border: 1px solid #d1d5db; border-radius: 8px;">Cancel</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @push('page_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const studentSelect = document.getElementById('student_id');
            const yearSelect = document.getElementById('academic_year_id');
            const feeSelect = document.getElementById('student_fee_assignment_id');
            const feeDetails = document.getElementById('fee_details');
            const originalAmountDisplay = document.getElementById('original_amount_display');
            const adjustmentType = document.getElementById('adjustment_type');
            const newAmountInput = document.getElementById('new_amount');

            function loadFees() {
                const studentId = studentSelect.value;
                const academicYearId = yearSelect.value;

                if (!studentId) {
                    feeSelect.innerHTML = '<option value="">Select Fee Category</option>';
                    feeDetails.style.display = 'none';
                    return;
                }

                fetch(`{{ route('fees.adjustments.ajax.student-fees') }}?student_id=${studentId}&academic_year_id=${academicYearId || ''}`)
                    .then(response => response.json())
                    .then(data => {
                        feeSelect.innerHTML = '<option value="">Select Fee Category</option>';
                        data.forEach(fee => {
                            const option = document.createElement('option');
                            option.value = fee.id;
                            option.textContent = `${fee.fee_name} (${fee.term}) - KES ${fee.original_amount.toLocaleString()}`;
                            option.dataset.original = fee.original_amount;
                            option.dataset.current = fee.current_final_amount;
                            feeSelect.appendChild(option);
                        });
                    });
            }

            studentSelect.addEventListener('change', loadFees);
            yearSelect.addEventListener('change', loadFees);

            feeSelect.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                if (selected.value) {
                    feeDetails.style.display = 'block';
                    originalAmountDisplay.textContent = 'KES ' + parseFloat(selected.dataset.original).toLocaleString();
                    newAmountInput.value = selected.dataset.current;
                } else {
                    feeDetails.style.display = 'none';
                }
            });

            adjustmentType.addEventListener('change', function() {
                if (this.value === 'waiver') {
                    newAmountInput.value = 0;
                    newAmountInput.readOnly = true;
                } else {
                    newAmountInput.readOnly = false;
                }
            });
        });
    </script>
    @endpush
@endsection
