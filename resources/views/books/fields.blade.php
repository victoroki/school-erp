<!-- Title Field -->
<div class="form-group col-sm-6">
    {!! Form::label('title', 'Title:') !!}
    {!! Form::text('title', null, ['class' => 'form-control', 'required', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>

<!-- Author Field -->
<div class="form-group col-sm-6">
    {!! Form::label('author', 'Author:') !!}
    {!! Form::text('author', null, ['class' => 'form-control', 'required', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>

<!-- Category Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('category_id', 'Category :') !!}
    {!! Form::select('category_id', $bkCategory, null, ['class' => 'form-control', 'placeholder' => 'Select Book Category']) !!}
</div>

<!-- Isbn Field -->
<div class="form-group col-sm-6">
    {!! Form::label('isbn', 'Isbn:') !!}
    {!! Form::text('isbn', null, ['class' => 'form-control', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- Publisher Field -->
<div class="form-group col-sm-6">
    {!! Form::label('publisher', 'Publisher:') !!}
    {!! Form::text('publisher', null, ['class' => 'form-control', 'maxlength' => 100, 'maxlength' => 100]) !!}
</div>

<!-- Publication Year Field -->
<div class="form-group col-sm-6">
    {!! Form::label('publication_year', 'Publication Year:') !!}
    {!! Form::number('publication_year', null, ['class' => 'form-control']) !!}
</div>

<!-- Edition Field -->
<div class="form-group col-sm-6">
    {!! Form::label('edition', 'Edition:') !!}
    {!! Form::text('edition', null, ['class' => 'form-control', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Price Field -->
<div class="form-group col-sm-6">
    {!! Form::label('price', 'Price:') !!}
    {!! Form::number('price', null, ['class' => 'form-control']) !!}
</div>

<!-- Pages Field -->
<div class="form-group col-sm-6">
    {!! Form::label('pages', 'Pages:') !!}
    {!! Form::number('pages', null, ['class' => 'form-control']) !!}
</div>

<!-- Quantity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('quantity', 'Quantity:') !!}
    {!! Form::number('quantity', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Available Quantity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('available_quantity', 'Available Quantity:') !!}
    {!! Form::number('available_quantity', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Shelf Location Field -->
<div class="form-group col-sm-6">
    {!! Form::label('shelf_location', 'Shelf Location:') !!}
    {!! Form::text('shelf_location', null, ['class' => 'form-control', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Added Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('added_date', 'Added Date:') !!}
    {!! Form::text('added_date', null, ['class' => 'form-control','id'=>'added_date']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#added_date').datepicker()
    </script>
@endpush

<!-- Description Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('description', 'Description:') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'maxlength' => 65535, 'maxlength' => 65535]) !!}
</div>