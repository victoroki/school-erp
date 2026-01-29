<!-- General Information Section -->
<div class="col-12">
    <h5 class="text-primary border-bottom mb-3 pb-2"><i class="fas fa-book mr-2"></i> General Information</h5>
</div>

<!-- Title Field -->
<div class="form-group col-sm-6">
    {!! Form::label('title', 'Book Title:') !!}
    {!! Form::text('title', null, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. Harry Potter and the Philosopher\'s Stone']) !!}
</div>

<!-- Category Field -->
<div class="form-group col-sm-6">
    {!! Form::label('category_id', 'Category:') !!}
    {!! Form::select('category_id', $bkCategory, null, ['class' => 'form-control select2', 'placeholder' => 'Select Category', 'required']) !!}
</div>

<!-- Author Field -->
<div class="form-group col-sm-6">
    {!! Form::label('author', 'Author(s):') !!}
    {!! Form::text('author', null, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. J.K. Rowling']) !!}
</div>

<!-- Publisher Field -->
<div class="form-group col-sm-6">
    {!! Form::label('publisher', 'Publisher:') !!}
    {!! Form::text('publisher', null, ['class' => 'form-control', 'placeholder' => 'e.g. Bloomsbury']) !!}
</div>

<!-- Publication Details Section -->
<div class="col-12 mt-3">
    <h5 class="text-primary border-bottom mb-3 pb-2"><i class="fas fa-info-circle mr-2"></i> Publication Details</h5>
</div>

<!-- ISBN and Barcode -->
<div class="form-group col-sm-4">
    {!! Form::label('isbn', 'ISBN:') !!}
    {!! Form::text('isbn', null, ['class' => 'form-control', 'placeholder' => 'International Standard Book Number']) !!}
</div>

<div class="form-group col-sm-4">
    {!! Form::label('barcode', 'Barcode/Accession No:') !!}
    {!! Form::text('barcode', null, ['class' => 'form-control', 'placeholder' => 'Scan or enter barcode']) !!}
</div>

<div class="form-group col-sm-4">
    {!! Form::label('edition', 'Edition:') !!}
    {!! Form::text('edition', null, ['class' => 'form-control', 'placeholder' => 'e.g. First Edition']) !!}
</div>

<!-- Year and Pages -->
<div class="form-group col-sm-4">
    {!! Form::label('publication_year', 'Publication Year:') !!}
    {!! Form::number('publication_year', null, ['class' => 'form-control', 'min' => '1900', 'max' => date('Y')]) !!}
</div>

<div class="form-group col-sm-4">
    {!! Form::label('pages', 'Pages:') !!}
    {!! Form::number('pages', null, ['class' => 'form-control']) !!}
</div>

<div class="form-group col-sm-4">
    {!! Form::label('condition', 'Condition:') !!}
    {!! Form::select('condition', ['new' => 'New', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor'], null, ['class' => 'form-control']) !!}
</div>

<!-- Inventory & Location Section -->
<div class="col-12 mt-3">
    <h5 class="text-primary border-bottom mb-3 pb-2"><i class="fas fa-boxes mr-2"></i> Inventory & Location</h5>
</div>

<!-- Quantity -->
<div class="form-group col-sm-4">
    {!! Form::label('quantity', 'Total Quantity:') !!}
    {!! Form::number('quantity', null, ['class' => 'form-control', 'required', 'min' => 0]) !!}
</div>

<div class="form-group col-sm-4">
    {!! Form::label('available_quantity', 'Available Copies:') !!}
    {!! Form::number('available_quantity', null, ['class' => 'form-control', 'required', 'min' => 0]) !!}
    <small class="text-muted">Usually same as Total Quantity for new books.</small>
</div>

<div class="form-group col-sm-4">
    {!! Form::label('shelf_location', 'Shelf Location:') !!}
    {!! Form::text('shelf_location', null, ['class' => 'form-control', 'placeholder' => 'e.g. A-12, Science Section']) !!}
</div>

<div class="form-group col-sm-4">
    {!! Form::label('price', 'Price (KSh):') !!}
    {!! Form::number('price', null, ['class' => 'form-control', 'step' => '0.01']) !!}
</div>

<div class="form-group col-sm-4">
    {!! Form::label('added_date', 'Date Added:') !!}
    {!! Form::text('added_date', null, ['class' => 'form-control', 'id'=>'added_date', 'required']) !!}
</div>

<div class="form-group col-sm-4">
    {!! Form::label('cover_url', 'Cover Image URL:') !!}
    {!! Form::text('cover_url', null, ['class' => 'form-control', 'placeholder' => 'http://...']) !!}
</div>

<!-- Description -->
<div class="form-group col-sm-12">
    {!! Form::label('description', 'Description/Summary:') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3]) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#added_date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: true,
            sideBySide: true
        });
        
        // Auto-fill available quantity when total quantity changes (only for create)
        @if(!isset($book))
        $('input[name="quantity"]').on('input', function() {
            $('input[name="available_quantity"]').val($(this).val());
        });
        @endif
    </script>
@endpush