<!-- Student Field -->
<div class="form-group col-sm-6">
    {!! Form::label('student_id', 'Student:') !!}
    {!! Form::select('student_id', ['' => 'Select Student'] + $students, null, ['class' => 'form-control select2', 'required']) !!}
</div>

@push('page_css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            color: #495057 !important;
        }
        .select2-search__field {
            display: block !important;
        }
    </style>
@endpush

@push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            var select2InitInterval = setInterval(function() {
                if (window.jQuery && window.jQuery.fn.select2) {
                    clearInterval(select2InitInterval);
                    window.jQuery(function($) {
                        $('.select2').select2({
                            placeholder: "Search and select...",
                            allowClear: true,
                            width: '100%'
                        });
                        
                        // Force search box to show focus if needed
                        $(document).on('select2:open', () => {
                            let searchField = document.querySelector('.select2-search__field');
                            if(searchField) searchField.focus();
                        });
                    });
                }
            }, 100);
            
            setTimeout(function() { clearInterval(select2InitInterval); }, 5000);
        });
    </script>
@endpush

<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 255]) !!}
</div>

<!-- Relationship Field -->
<div class="form-group col-sm-6">
    {!! Form::label('relationship', 'Relationship:') !!}
    {!! Form::text('relationship', null, ['class' => 'form-control', 'required', 'maxlength' => 255]) !!}
</div>

<!-- Phone Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone', 'Phone:') !!}
    {!! Form::text('phone', null, ['class' => 'form-control', 'required', 'maxlength' => 255]) !!}
</div>

<!-- Phone 2 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone_2', 'Phone 2:') !!}
    {!! Form::text('phone_2', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Email:') !!}
    {!! Form::email('email', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Address Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('address', 'Address:') !!}
    {!! Form::textarea('address', null, ['class' => 'form-control', 'maxlength' => 65535]) !!}
</div>

<!-- Priority Field -->
<div class="form-group col-sm-6">
    {!! Form::label('priority', 'Priority:') !!}
    {!! Form::number('priority', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Is Authorized Pickup Field -->
<div class="form-group col-sm-6">
    <div class="form-check">
        {!! Form::hidden('is_authorized_pickup', 0, ['class' => 'form-check-input']) !!}
        {!! Form::checkbox('is_authorized_pickup', '1', null, ['class' => 'form-check-input']) !!}
        {!! Form::label('is_authorized_pickup', 'Is Authorized Pickup', ['class' => 'form-check-label']) !!}
    </div>
</div>