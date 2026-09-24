<!-- Financial Year Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('financial_year_id', 'Financial Year:') !!}

    @if($financialYears->isEmpty())
        <div class="alert alert-danger mb-0" id="no-open-fy-warning">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            No open financial year. <a href="{{ route('financial-years.create') }}">Open one</a> before creating a budget.
        </div>
    @else
        {!! Form::select('financial_year_id', $financialYears, null, ['class' => 'form-control custom-select', 'placeholder' => 'Select Year']) !!}
    @endif
</div>

<!-- Category Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('category_type', 'Category Type:') !!}
    {!! Form::select('category_type', ['income' => 'Income', 'expense' => 'Expense'], null, ['class' => 'form-control custom-select', 'id' => 'category_type']) !!}
</div>

<!-- Category Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('category_id', 'Category:') !!}
    <select name="category_id" id="category_id" class="form-control custom-select" required>
        <option value="">Select Category</option>
    </select>
</div>

<!-- Include Fees Field (income budgets only) -->
<div class="form-group col-sm-6 d-none" id="include_fees_div">
    <div class="custom-control custom-checkbox mt-4 pt-1">
        {!! Form::checkbox('include_fees', 1, null, ['class' => 'custom-control-input', 'id' => 'include_fees']) !!}
        {!! Form::label('include_fees', 'Include fee payments in actuals', ['class' => 'custom-control-label font-weight-bold text-muted']) !!}
    </div>
    <small class="form-text text-muted">Count student fee collections alongside recorded income for this budget line.</small>
</div>

<!-- Amount Field -->
<div class="form-group col-sm-6">
    {!! Form::label('amount', 'Amount:') !!}
    {!! Form::number('amount', null, ['class' => 'form-control', 'step' => '0.01']) !!}
</div>

<!-- Alert Threshold Field -->
<div class="form-group col-sm-6">
    {!! Form::label('alert_threshold', 'Alert Threshold (%):') !!}
    {!! Form::number('alert_threshold', !isset($budget) ? old('alert_threshold', 80) : null, ['class' => 'form-control', 'min' => '1', 'max' => '100', 'placeholder' => 'e.g. 80']) !!}
    <small class="form-text text-muted">You'll be warned when actual spending reaches this % of the budget.</small>
</div>

@push('page_scripts')
    <script>
        @if($financialYears->isEmpty())
            window.jQuery(function($) {
                $('form').on('submit', function (e) {
                    e.preventDefault();
                    alert('Please open a financial year before saving a budget.');
                });
            });
        @endif

        const incomeCategories = @json($incomeCategories);
        const expenseCategories = @json($expenseCategories);

        function selectCategory($select, value) {
            if (value && $select.find(`option[value="${value}"]`).length) {
                $select.val(value);
            }
        }

        function updateCategories() {
            const type = $('#category_type').val();
            const $select = $('#category_id');
            const previous = $select.val();
            $select.empty().append('<option value="">Select Category</option>');
            
            const categories = type === 'income' ? incomeCategories : expenseCategories;
            
            Object.entries(categories).forEach(([id, name]) => {
                $select.append(`<option value="${id}">${name}</option>`);
            });

            $('#include_fees_div').toggleClass('d-none', type !== 'income');

            @if(isset($budget))
                selectCategory($select, '{{ $budget->category_id }}');
            @elseif(old('category_id'))
                selectCategory($select, '{{ old('category_id') }}');
            @else
                selectCategory($select, previous);
            @endif
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof window.jQuery === 'undefined') {
                console.error('jQuery is not loaded; category dropdown cannot be updated.');
                return;
            }

            window.jQuery(function($) {
                $('#category_type').change(updateCategories);
                updateCategories();
            });
        });
    </script>
@endpush
