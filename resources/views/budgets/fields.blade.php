<!-- Financial Year Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('financial_year_id', 'Financial Year:') !!}
    {!! Form::select('financial_year_id', $financialYears, null, ['class' => 'form-control custom-select', 'placeholder' => 'Select Year']) !!}
</div>

<!-- Category Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('category_type', 'Category Type:') !!}
    {!! Form::select('category_type', ['income' => 'Income', 'expense' => 'Expense'], null, ['class' => 'form-control custom-select', 'id' => 'category_type']) !!}
</div>

<!-- Category Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('category_id', 'Category:') !!}
    <select name="category_id" id="category_id" class="form-control custom-select">
        <option value="">Select Category</option>
    </select>
</div>

<!-- Amount Field -->
<div class="form-group col-sm-6">
    {!! Form::label('amount', 'Amount:') !!}
    {!! Form::number('amount', null, ['class' => 'form-control', 'step' => '0.01']) !!}
</div>

<!-- Alert Threshold Field -->
<div class="form-group col-sm-6">
    {!! Form::label('alert_threshold', 'Alert Threshold (%):') !!}
    {!! Form::number('alert_threshold', null, ['class' => 'form-control', 'placeholder' => 'e.g. 90']) !!}
</div>

@push('page_scripts')
    <script>
        const incomeCategories = @json($incomeCategories);
        const expenseCategories = @json($expenseCategories);

        function updateCategories() {
            const type = $('#category_type').val();
            const $select = $('#category_id');
            $select.empty().append('<option value="">Select Category</option>');
            
            const categories = type === 'income' ? incomeCategories : expenseCategories;
            
            Object.entries(categories).forEach(([id, name]) => {
                $select.append(`<option value="${id}">${name}</option>`);
            });

            @if(isset($budget))
                $select.val('{{ $budget->category_id }}');
            @endif
        }

        $('#category_type').change(updateCategories);
        updateCategories();
    </script>
@endpush
