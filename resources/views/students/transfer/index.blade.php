@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Student Transfer</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')
        @include('adminlte-templates::common.errors')

        <div class="card card-warning shadow-sm">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold"><i class="fas fa-exchange-alt mr-2"></i> Process Student Transfer</h3>
            </div>
            {!! Form::open(['route' => 'student-transfer.store']) !!}
            <div class="card-body">
                <div class="row">
                    <!-- Student Selection -->
                    <div class="form-group col-md-6">
                        {!! Form::label('student_id', 'Select Student:') !!}
                        <select name="student_id" id="student_id" class="form-control select2" required>
                            <option value="">Search and select student...</option>
                            @foreach($students as $student)
                                <option value="{{ $student->student_id }}">{{ $student->admission_no }} - {{ $student->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Transfer Date -->
                    <div class="form-group col-md-6">
                        {!! Form::label('transfer_date', 'Transfer Date:') !!}
                        {!! Form::date('transfer_date', \Carbon\Carbon::now()->format('Y-m-d'), ['class' => 'form-control', 'required']) !!}
                    </div>

                    <!-- Transfer Reason -->
                    <div class="form-group col-md-12">
                        {!! Form::label('transfer_reason', 'Reason for Transfer:') !!}
                        {!! Form::textarea('transfer_reason', null, ['class' => 'form-control', 'rows' => 3, 'required', 'placeholder' => 'Enter the reason for transferring...']) !!}
                    </div>

                    <!-- Transfer Certificate Number -->
                    <div class="form-group col-md-6">
                        {!! Form::label('transfer_certificate_no', 'Transfer Certificate No:') !!}
                        {!! Form::text('transfer_certificate_no', null, ['class' => 'form-control', 'placeholder' => 'Optional TC number']) !!}
                    </div>
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('Submit Transfer', ['class' => 'btn btn-warning font-weight-bold']) !!}
                <a href="{{ route('students.index') }}" class="btn btn-default"> Cancel </a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

@endsection

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
    <script>
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
