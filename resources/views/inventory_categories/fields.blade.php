<div class="row">
    <!-- Category Name -->
    <div class="form-group col-sm-6">
        {!! Form::label('name', 'Category Name:') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 100, 'placeholder' => 'e.g. Science Lab Equipment']) !!}
    </div>

    <!-- Category Code -->
    <div class="form-group col-sm-6">
        {!! Form::label('code', 'Category Code:') !!}
        {!! Form::text('code', null, ['class' => 'form-control', 'placeholder' => 'e.g. CAT-LAB']) !!}
    </div>

    <!-- Category Type -->
    <div class="form-group col-sm-6">
        {!! Form::label('category_type', 'Category Type:') !!}
        <div class="d-flex mt-2">
            <div class="custom-control custom-radio mr-3">
                {!! Form::radio('category_type', 'consumable', true, ['class' => 'custom-control-input', 'id' => 'type_consumable']) !!}
                <label class="custom-control-label font-weight-normal" for="type_consumable">Consumable (Uses up)</label>
            </div>
            <div class="custom-control custom-radio">
                {!! Form::radio('category_type', 'asset', false, ['class' => 'custom-control-input', 'id' => 'type_asset']) !!}
                <label class="custom-control-label font-weight-normal" for="type_asset">Asset (Reusable equipment)</label>
            </div>
        </div>
    </div>

    <!-- Icon -->
    <div class="form-group col-sm-6">
        {!! Form::label('icon', 'Icon Class (FontAwesome):') !!}
        {!! Form::text('icon', null, ['class' => 'form-control', 'placeholder' => 'fa-laptop, fa-pencil-alt, etc.']) !!}
    </div>

    <!-- Default Location -->
    <div class="form-group col-sm-6">
        {!! Form::label('default_location', 'Default Storage Location:') !!}
        {!! Form::text('default_location', null, ['class' => 'form-control', 'placeholder' => 'e.g. Main Store Shelf B']) !!}
    </div>

    <!-- Trackable -->
    <div class="form-group col-sm-6">
        <label>Trackable?</label>
        <div class="custom-control custom-checkbox mt-2">
            {!! Form::hidden('trackable', 0) !!}
            {!! Form::checkbox('trackable', '1', null, ['class' => 'custom-control-input', 'id' => 'trackable']) !!}
            <label class="custom-control-label font-weight-normal" for="trackable">Yes, track stock levels for this category</label>
        </div>
    </div>

    <!-- Description -->
    <div class="form-group col-sm-12 col-lg-12">
        {!! Form::label('description', 'Description:') !!}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3]) !!}
    </div>
</div>