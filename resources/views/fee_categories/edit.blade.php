@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>
                        Edit Fee Category
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">

            {!! Form::model($feeCategory, ['route' => ['fee-categories.update', $feeCategory->category_id], 'method' => 'patch']) !!}

            <div class="card-body">
                <div class="row">
                    @include('fee_categories.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('fee-categories.index') }}" class="btn btn-default"> Cancel </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const autoCodeBtn = document.getElementById('auto-code-btn');
            const codeField = document.getElementById('code-field');
            const nameField = document.querySelector('input[name="name"]');

            autoCodeBtn.addEventListener('click', function() {
                const name = nameField.value.trim();
                
                if (!name) {
                    alert('Please enter a name first to generate auto code');
                    nameField.focus();
                    return;
                }

                // Show loading state
                autoCodeBtn.disabled = true;
                autoCodeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

                // Make AJAX request to generate code
                fetch('{{ route("fee-categories.generate-code") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        name: name
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        codeField.value = data.code;
                        // Add a visual feedback
                        codeField.classList.add('is-valid');
                        setTimeout(() => {
                            codeField.classList.remove('is-valid');
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error generating code:', error);
                    alert('Error generating auto code. Please try again.');
                })
                .finally(() => {
                    // Reset button state
                    autoCodeBtn.disabled = false;
                    autoCodeBtn.innerHTML = '<i class="fas fa-magic"></i> Auto';
                });
            });
        });
    </script>
@endsection
