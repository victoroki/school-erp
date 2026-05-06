@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>
                    Create Fee Structures
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">

            {!! Form::open(['route' => 'fee-structures.store']) !!}

            <div class="card-body">

                <div class="row">
                    @include('fee_structures.fields')
                </div>

            </div>

            <div class="card-footer">
                {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('fee-structures.index') }}" class="btn btn-default"> Cancel </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection

@push('page_scripts')
<script>
    // Polling strategy to ensure jQuery and Select2 are loaded before initialization
    function initializeFeeForm() {
        if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.select2 !== 'undefined') {
            // Initialize Select2
            window.jQuery('.select2').select2({
                theme: 'bootstrap-5'
            });
        } else {
            // Poll every 50ms until dependencies are ready
            setTimeout(initializeFeeForm, 50);
        }
    }

    // Start initialization after DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initializeFeeForm, 100);
    });
</script>
@endpush
