<!-- Staff Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('staff_id', 'Staff Member:') !!}
    {!! Form::select('staff_id', $staff ?? [], null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Salary Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('salary_id', 'Salary Structure:') !!}
    {!! Form::select('salary_id', $salaries ?? [], null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Month Field -->
<div class="form-group col-sm-6">
    {!! Form::label('month', 'Month:') !!}
    {!! Form::select('month', $months ?? [], null, ['class' => 'form-control', 'required', 'placeholder' => 'Select Month']) !!}
</div>

<!-- Year Field -->
<div class="form-group col-sm-6">
    {!! Form::label('year', 'Year:') !!}
    {!! Form::select('year', $years ?? [], null, ['class' => 'form-control', 'required', 'placeholder' => 'Select Year']) !!}
</div>

<!-- Working Days Field -->
<div class="form-group col-sm-6">
    {!! Form::label('working_days', 'Working Days:') !!}
    {!! Form::number('working_days', null, ['class' => 'form-control', 'required', 'min' => '1', 'max' => '31', 'step' => '1']) !!}
</div>

<!-- Paid Days Field -->
<div class="form-group col-sm-6">
    {!! Form::label('paid_days', 'Paid Days:') !!}
    {!! Form::number('paid_days', null, ['class' => 'form-control', 'required', 'min' => '0', 'max' => '31', 'step' => '1']) !!}
</div>

<!-- Absent Days Field -->
<div class="form-group col-sm-6">
    {!! Form::label('absent_days', 'Absent Days:') !!}
    {!! Form::number('absent_days', null, ['class' => 'form-control', 'required', 'min' => '0', 'max' => '31', 'step' => '1', 'value' => '0']) !!}
</div>

<!-- Leave Days Field -->
<div class="form-group col-sm-6">
    {!! Form::label('leave_days', 'Leave Days:') !!}
    {!! Form::number('leave_days', null, ['class' => 'form-control', 'required', 'min' => '0', 'max' => '31', 'step' => '1', 'value' => '0']) !!}
</div>

<!-- Basic Salary Field -->
<div class="form-group col-sm-6">
    {!! Form::label('basic_salary', 'Basic Salary:') !!}
    {!! Form::number('basic_salary', null, ['class' => 'form-control', 'required', 'min' => '0', 'step' => '0.01', 'placeholder' => '0.00']) !!}
</div>

<!-- Allowances Field -->
<div class="form-group col-sm-6">
    {!! Form::label('allowances', 'Allowances:') !!}
    {!! Form::number('allowances', null, ['class' => 'form-control', 'min' => '0', 'step' => '0.01', 'placeholder' => '0.00', 'value' => '0']) !!}
</div>

<!-- Overtime Field -->
<div class="form-group col-sm-6">
    {!! Form::label('overtime', 'Overtime Pay:') !!}
    {!! Form::number('overtime', null, ['class' => 'form-control', 'min' => '0', 'step' => '0.01', 'placeholder' => '0.00', 'value' => '0']) !!}
</div>

<!-- Gross Salary Field -->
<div class="form-group col-sm-6">
    {!! Form::label('gross_salary', 'Gross Salary:') !!}
    {!! Form::number('gross_salary', null, ['class' => 'form-control', 'required', 'min' => '0', 'step' => '0.01', 'placeholder' => '0.00', 'readonly']) !!}
    <small class="form-text text-muted">Automatically calculated from basic salary + allowances + overtime</small>
</div>

<!-- Deductions Field -->
<div class="form-group col-sm-6">
    {!! Form::label('deductions', 'Deductions:') !!}
    {!! Form::number('deductions', null, ['class' => 'form-control', 'min' => '0', 'step' => '0.01', 'placeholder' => '0.00', 'value' => '0']) !!}
</div>

<!-- Net Salary Field -->
<div class="form-group col-sm-6">
    {!! Form::label('net_salary', 'Net Salary:') !!}
    {!! Form::number('net_salary', null, ['class' => 'form-control', 'required', 'min' => '0', 'step' => '0.01', 'placeholder' => '0.00', 'readonly']) !!}
    <small class="form-text text-muted">Automatically calculated from gross salary - deductions</small>
</div>

<!-- Payment Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('payment_date', 'Payment Date:') !!}
    {!! Form::date('payment_date', null, ['class' => 'form-control', 'id' => 'payment_date']) !!}
</div>

<!-- Payment Method Field -->
<div class="form-group col-sm-6">
    {!! Form::label('payment_method', 'Payment Method:') !!}
    {!! Form::select('payment_method', $paymentMethods ?? [], null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Reference Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('reference_number', 'Reference Number:') !!}
    {!! Form::text('reference_number', null, ['class' => 'form-control', 'maxlength' => 100, 'placeholder' => 'Enter reference number (optional)']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::select('status', $statusOptions ?? [], null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Remarks Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('remarks', 'Remarks:') !!}
    {!! Form::textarea('remarks', null, ['class' => 'form-control', 'maxlength' => 65535, 'rows' => 3, 'placeholder' => 'Additional notes or comments (optional)']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            // Staff salary data for auto-population
            var staffSalaries = @json($staffWithSalaries ?? []);
            
            // Auto-populate salary details when staff is selected
            $('#staff_id').on('change', function() {
                var selectedStaffId = $(this).val();
                var staffData = staffSalaries.find(s => s.staff_id == selectedStaffId);
                
                if (staffData && staffData.current_salary) {
                    $('#salary_id').val(staffData.current_salary.salary_id);
                    $('#basic_salary').val(staffData.current_salary.basic_salary);
                    $('#allowances').val(staffData.current_salary.allowances || 0);
                    $('#deductions').val(staffData.current_salary.deductions || 0);
                    calculateGrossSalary();
                } else {
                    // Clear salary fields if no salary data found
                    $('#salary_id').val('');
                    $('#basic_salary').val('');
                    $('#allowances').val('0');
                    $('#deductions').val('0');
                    $('#gross_salary').val('');
                    $('#net_salary').val('');
                }
            });
            
            // Auto-calculate gross salary
            function calculateGrossSalary() {
                var basicSalary = parseFloat($('#basic_salary').val()) || 0;
                var allowances = parseFloat($('#allowances').val()) || 0;
                var overtime = parseFloat($('#overtime').val()) || 0;
                var grossSalary = basicSalary + allowances + overtime;
                $('#gross_salary').val(grossSalary.toFixed(2));
                calculateNetSalary();
            }
            
            // Auto-calculate net salary
            function calculateNetSalary() {
                var grossSalary = parseFloat($('#gross_salary').val()) || 0;
                var deductions = parseFloat($('#deductions').val()) || 0;
                var netSalary = grossSalary - deductions;
                $('#net_salary').val(netSalary.toFixed(2));
            }
            
            // Bind calculation events
            $('#basic_salary, #allowances, #overtime').on('input change', calculateGrossSalary);
            $('#deductions').on('input change', calculateNetSalary);
            
            // Set default payment date to today if empty
            if (!$('#payment_date').val()) {
                var today = new Date().toISOString().split('T')[0];
                $('#payment_date').val(today);
            }
            
            // Auto-calculate paid days based on working days, absent days, and leave days
            function calculatePaidDays() {
                var workingDays = parseInt($('#working_days').val()) || 0;
                var absentDays = parseInt($('#absent_days').val()) || 0;
                var leaveDays = parseInt($('#leave_days').val()) || 0;
                var paidDays = workingDays - absentDays - leaveDays;
                if (paidDays < 0) paidDays = 0;
                $('#paid_days').val(paidDays);
            }
            
            $('#working_days, #absent_days, #leave_days').on('input change', calculatePaidDays);
            
            // Set default month and year to current if empty
            if (!$('#month').val()) {
                $('#month').val(new Date().getMonth() + 1);
            }
            if (!$('#year').val()) {
                $('#year').val(new Date().getFullYear());
            }
        });
    </script>
@endpush