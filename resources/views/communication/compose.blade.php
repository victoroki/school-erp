@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Compose Message</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('communication.dashboard') }}" class="btn btn-default">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="row">
            <div class="col-md-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">New Message</h3>
                    </div>
                    {!! Form::open(['route' => 'communication.send']) !!}
                    <div class="card-body">
                        <!-- Message Type -->
                        <div class="form-group">
                            <label>Message Type:</label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-primary active">
                                    <input type="radio" name="message_type" id="type_sms" value="SMS" checked> <i class="fas fa-sms"></i> SMS
                                </label>
                                <label class="btn btn-outline-primary">
                                    <input type="radio" name="message_type" id="type_email" value="Email"> <i class="fas fa-envelope"></i> Email
                                </label>
                            </div>
                        </div>

                        <!-- Recipient Group -->
                        <div class="form-group">
                            <label>Recipients:</label>
                            {!! Form::select('recipient_group', [
                                'All Students' => 'All Students (Active)',
                                'All Parents' => 'All Parents',
                                'All Staff' => 'All Staff Members',
                                'Class' => 'Specific Class',
                                // 'Custom' => 'Custom List (Coming Soon)'
                            ], null, ['class' => 'form-control select2', 'id' => 'recipient_group']) !!}
                        </div>

                        <!-- Dynamic Class Selection -->
                        <div class="form-group d-none" id="class_selector">
                            <label>Select Class:</label>
                            {!! Form::select('class_id', $classes, null, ['class' => 'form-control select2', 'placeholder' => 'Select a Class']) !!}
                        </div>

                        <!-- Template Selection -->
                        <div class="form-group">
                            <label>Use Template (Optional):</label>
                            <select name="template_id" id="template_id" class="form-control select2">
                                <option value="">Select a Template</option>
                                <optgroup label="SMS Templates" id="sms_templates_opt">
                                    @foreach($smsTemplates as $template)
                                        <option value="{{ $template->template_id }}">{{ $template->title }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Email Templates" id="email_templates_opt" style="display:none;">
                                    @foreach($emailTemplates as $template)
                                        <option value="{{ $template->template_id }}">{{ $template->title }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        <!-- Subject (Email Only) -->
                        <div class="form-group d-none" id="subject_group">
                            {!! Form::label('subject', 'Subject:') !!}
                            {!! Form::text('subject', null, ['class' => 'form-control', 'placeholder' => 'Email Subject']) !!}
                        </div>

                        <!-- Content -->
                        <div class="form-group">
                            {!! Form::label('content', 'Message Content:') !!}
                            {!! Form::textarea('content', null, ['class' => 'form-control', 'rows' => 5, 'id' => 'message_content']) !!}
                            <small class="text-muted float-right" id="char_count">0 chars</small>
                            <small class="text-info">Available placeholders: {name}, {class}, {fee_balance}</small>
                        </div>

                    </div>
                    <div class="card-footer">
                        <div class="float-right">
                            <button type="submit" class="btn btn-primary"><i class="far fa-paper-plane"></i> Send Now</button>
                        </div>
                        <button type="reset" class="btn btn-default"><i class="fas fa-times"></i> Discard</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>

            <!-- Preview Card -->
            <div class="col-md-4">
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Preview</h3>
                    </div>
                <div class="card-body">
                        <div id="preview_area" class="alert alert-light border">
                            <p class="text-muted">Select a template or type content to preview.</p>
                        </div>
                        <div class="callout callout-info mt-3">
                            <h5>Recipient Estimate</h5>
                            <p id="recipient_estimate">Select a group to see count.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page_scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap4' });

            // Toggle Message Type
            $('input[name="message_type"]').change(function() {
                var type = $(this).val();
                if(type === 'SMS') {
                    $('#subject_group').addClass('d-none');
                    $('#sms_templates_opt').show();
                    $('#email_templates_opt').hide();
                } else {
                    $('#subject_group').removeClass('d-none');
                    $('#sms_templates_opt').hide();
                    $('#email_templates_opt').show();
                }
                // Reset template selection
                $('#template_id').val('').trigger('change');
            });

            // Toggle Class Selector
            function updateRecipientCount() {
                var group = $('#recipient_group').val();
                var classId = $('select[name="class_id"]').val();
                var type = $('input[name="message_type"]:checked').val();

                if (!group) {
                    $('#recipient_estimate').text('Select a group to see count.');
                    return;
                }

                $('#recipient_estimate').html('<i class="fas fa-spinner fa-spin"></i> Calculating...');

                $.ajax({
                    url: '{{ route("communication.api.recipients.count") }}',
                    method: 'GET',
                    data: {
                        recipient_group: group,
                        class_id: classId,
                        message_type: type,
                    },
                    success: function(response) {
                        var cost = type === 'SMS' ? (response.count * 0.80).toFixed(2) : '0.00';
                        $('#recipient_estimate').html(
                            '<strong class="text-dark">' + response.count + ' recipients</strong>' +
                            (type === 'SMS' ? '<br><small class="text-muted">Est. cost: KES ' + cost + '</small>' : '')
                        );
                    },
                    error: function() {
                        $('#recipient_estimate').text('Could not calculate count.');
                    }
                });
            }

            $('#recipient_group').change(function() {
                if($(this).val() === 'Class') {
                    $('#class_selector').removeClass('d-none');
                } else {
                    $('#class_selector').addClass('d-none');
                }
                updateRecipientCount();
            });

            $('select[name="class_id"]').change(function() {
                updateRecipientCount();
            });

            $('input[name="message_type"]').change(function() {
                updateRecipientCount();
            });

            // Load Template Content
            $('#template_id').change(function() {
                var id = $(this).val();
                var type = $('input[name="message_type"]:checked').val();
                if(id) {
                    $.get('/communication/api/template/' + type + '/' + id, function(data) {
                        if(type === 'SMS') {
                            $('#message_content').val(data.content);
                        } else {
                            $('input[name="subject"]').val(data.subject);
                            $('#message_content').val(data.content);
                        }
                        updatePreview();
                    });
                }
            });

            // Live Preview & Char Count
            $('#message_content').on('keyup input', function() {
                updatePreview();
            });

            function updatePreview() {
                var content = $('#message_content').val();
                $('#preview_area').html(content.replace(/\n/g, '<br>'));
                $('#char_count').text(content.length + ' chars');
            }

            // Pre-select template if in URL
            var urlParams = new URLSearchParams(window.location.search);
            if(urlParams.has('type')) {
                var type = urlParams.get('type');
                if(type === 'Email') {
                    $('#type_email').prop('checked', true).trigger('change'); 
                    $('#type_email').parent().addClass('active');
                    $('#type_sms').parent().removeClass('active');
                }
            }
            if(urlParams.has('template_id')) {
                $('#template_id').val(urlParams.get('template_id')).trigger('change');
            }
        });
    </script>
@endpush
