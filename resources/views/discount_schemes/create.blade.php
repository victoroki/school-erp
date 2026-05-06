@extends('layouts.app')

@section('content')
<div class="fee-wrap">
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="icon-box bg-amber-light text-amber">
                <i class="fas fa-percentage"></i>
            </div>
            <div>
                <h1 class="page-title mb-0">Create Discount Scheme</h1>
                <p class="page-subtitle mb-0">Define discount rules and criteria</p>
            </div>
        </div>
        <a href="{{ route('fees.discounts.index') }}" class="btn-ghost-custom">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    @include('adminlte-templates::common.errors')

    <div class="card custom-card">
        {!! Form::open(['route' => 'fees.discounts.store']) !!}

        <div class="card-body">
            <div class="row g-3">
                @include('discount_schemes.fields')
                
                <!-- Academic Year Field -->
                <div class="form-group col-sm-6">
                    {!! Form::label('academic_year_id', 'Academic Year:', ['class' => 'form-label fw-bold small text-uppercase text-muted mb-1']) !!}
                    {!! Form::select('academic_year_id', $academicYears ?? [], null, ['class' => 'form-control select2 rounded-3', 'placeholder' => 'Select Year']) !!}
                </div>

                <!-- Valid From Field -->
                <div class="form-group col-sm-6">
                    {!! Form::label('valid_from', 'Valid From:', ['class' => 'form-label fw-bold small text-uppercase text-muted mb-1']) !!}
                    {!! Form::date('valid_from', null, ['class' => 'form-control rounded-3']) !!}
                </div>

                <!-- Valid To Field -->
                <div class="form-group col-sm-6">
                    {!! Form::label('valid_to', 'Valid To:', ['class' => 'form-label fw-bold small text-uppercase text-muted mb-1']) !!}
                    {!! Form::date('valid_to', null, ['class' => 'form-control rounded-3']) !!}
                </div>

                <!-- Requires Approval Field -->
                <div class="form-group col-sm-6">
                    <div class="form-check">
                        {!! Form::checkbox('requires_approval', 1, null, ['class' => 'form-check-input']) !!}
                        {!! Form::label('requires_approval', 'Requires Approval', ['class' => 'form-check-label fw-600']) !!}
                    </div>
                </div>

                <!-- Auto Apply Field -->
                <div class="form-group col-sm-6">
                    <div class="form-check">
                        {!! Form::checkbox('auto_apply', 1, null, ['class' => 'form-check-input']) !!}
                        {!! Form::label('auto_apply', 'Auto Apply', ['class' => 'form-check-label fw-600']) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-light-soft border-top">
            {!! Form::submit('Save', ['class' => 'btn-primary-custom']) !!}
            <a href="{{ route('fees.discounts.index') }}" class="btn-ghost-custom ms-2"> Cancel </a>
        </div>

        {!! Form::close() !!}
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5;
    --amber: #f59e0b;
    --amber-light: #fffbeb;
    --emerald: #10b981;
    --slate: #475569;
    --slate-light: #f1f5f9;
    --text: #1e293b;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
}

.fee-wrap { padding: 1.5rem 2rem; background: #f9fafb; min-height: 100vh; }

.page-title { font-size: 1.25rem; font-weight: 900; color: var(--text); letter-spacing: -0.02em; }
.page-subtitle { color: var(--muted); font-size: 0.8rem; font-weight: 500; }

.icon-box { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }

.custom-card { border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.custom-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.12); }

.form-label { font-size: 0.7rem; }

.btn-primary-custom {
    display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 8px;
    font-size: 0.75rem; font-weight: 800; border: none; text-decoration: none !important;
    background: var(--indigo); color: #fff; transition: all 160ms var(--ease-out);
}
.btn-primary-custom:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }
.btn-primary-custom:active { transform: scale(0.97); }

.btn-ghost-custom {
    display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 8px;
    font-size: 0.75rem; font-weight: 700; text-decoration: none !important;
    background: #fff; border: 1px solid var(--border); color: var(--text); transition: all 160ms var(--ease-out);
}
.btn-ghost-custom:hover { background: var(--slate-light); }
.btn-ghost-custom:active { transform: scale(0.97); }

.bg-light-soft { background-color: #f8faff; }

/* Select2 Overrides */
.select2-container--default .select2-selection--single {
    height: 40px !important; border-radius: 8px !important;
    border: 1px solid var(--border) !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px !important; font-size: 0.85rem !important; font-weight: 600 !important;
    padding-left: 12px !important; color: var(--text) !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px !important;
}
.input-group { border-radius: 8px; overflow: hidden; }
.input-group .form-control { border-right: none; }
.input-group .btn { border: 1px solid var(--border); border-left: none; background: #fff; color: var(--slate); }
.input-group .btn:hover { background: var(--amber-light); color: var(--amber); }
</style>

@push('page_scripts')
<script>
$(document).ready(function() {
    $('.select2').select2({
        theme: 'default',
        width: '100%',
        placeholder: 'Select an option'
    });

    // Auto-generate code from name
    $('#generate-code-btn').click(function(e) {
        e.preventDefault();
        var name = $('input[name="name"]').val();
        if (name) {
            var code = name.toUpperCase()
                .replace(/[^A-Z0-9\s]/g, '')
                .split(/\s+/)
                .map(function(word) { return word.substring(0, 4); })
                .join('_')
                .substring(0, 20);
            $('#code-field').val(code);
        }
    });

    // Auto-generate on name blur
    $('input[name="name"]').on('blur', function() {
        var codeField = $('#code-field');
        if (!codeField.val()) {
            var name = $(this).val();
            if (name) {
                var code = name.toUpperCase()
                    .replace(/[^A-Z0-9\s]/g, '')
                    .split(/\s+/)
                    .map(function(word) { return word.substring(0, 4); })
                    .join('_')
                    .substring(0, 20);
                codeField.val(code);
            }
        }
    });
});
</script>
@endpush
@endsection
