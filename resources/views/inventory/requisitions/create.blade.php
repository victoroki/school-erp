@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-file-signature text-primary mr-2"></i>New Requisition</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card border-0 shadow-sm">
            {!! Form::open(['route' => 'inventory.requisitions.store']) !!}
            <div class="card-body">
                <div class="row">
                    <!-- Department -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('department_id', 'Requesting Department:') !!}
                        {!! Form::select('department_id', $departments, null, ['class' => 'form-control', 'placeholder' => 'Select Department', 'required']) !!}
                    </div>

                    <!-- Date Needed -->
                    <div class="form-group col-sm-3">
                        {!! Form::label('date_needed', 'Date Needed By:') !!}
                        {!! Form::date('date_needed', \Carbon\Carbon::today()->addDays(2), ['class' => 'form-control', 'required']) !!}
                    </div>

                    <!-- Priority -->
                    <div class="form-group col-sm-3">
                        {!! Form::label('priority', 'Priority Level:') !!}
                        {!! Form::select('priority', ['Low' => 'Low', 'Medium' => 'Medium', 'High' => 'High', 'Urgent' => 'Urgent'], 'Medium', ['class' => 'form-control']) !!}
                    </div>

                    <!-- Justification -->
                    <div class="form-group col-sm-12">
                        {!! Form::label('justification', 'Reason for Request / Justification:') !!}
                        {!! Form::textarea('justification', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Describe why these items are needed...']) !!}
                    </div>

                    <div class="col-12"><hr></div>

                    <!-- Items Selection -->
                    <div class="col-12 mb-3">
                        <h5 class="font-weight-bold">Requested Items</h5>
                        <div id="items-container">
                            <div class="row item-row mb-2">
                                <div class="col-md-6">
                                    <select name="items[0][item_id]" class="form-control" required>
                                        <option value="">Select Item</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->item_id }}">{{ $item->name }} (In Stock: {{ $item->quantity }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" name="items[0][quantity]" class="form-control" placeholder="Quantity" min="1" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-block remove-item"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-info btn-sm mt-2" id="add-item">
                            <i class="fas fa-plus mr-1"></i> Add Another Item
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white text-right">
                <a href="{{ route('inventory.requisitions.index') }}" class="btn btn-default">Cancel</a>
                {!! Form::submit('Submit Requisition', ['class' => 'btn btn-primary px-4 shadow-sm']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @push('page_scripts')
    <script>
        $(document).ready(function() {
            let itemIndex = 1;
            $('#add-item').click(function() {
                const newRow = $('.item-row:first').clone();
                newRow.find('select').attr('name', `items[${itemIndex}][item_id]`).val('');
                newRow.find('input').attr('name', `items[${itemIndex}][quantity]`).val('');
                $('#items-container').append(newRow);
                itemIndex++;
            });

            $(document).on('click', '.remove-item', function() {
                if ($('.item-row').length > 1) {
                    $(this).closest('.item-row').remove();
                } else {
                    alert('At least one item is required.');
                }
            });
        });
    </script>
    @endpush
@endsection
