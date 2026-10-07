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
                                'Class Section' => 'Specific Class Section',
                                // 'Custom' => 'Custom List (Coming Soon)'
                            ], null, ['class' => 'form-control select2', 'id' => 'recipient_group']) !!}
                        </div>

                        <!-- Dynamic Class Selection -->
                        <div class="form-group d-none" id="class_selector">
                            <label>Select Class:</label>
                            {!! Form::select('class_id', $classes, null, ['class' => 'form-control select2', 'placeholder' => 'Select a Class']) !!}
                        </div>

                        <!-- Dynamic Class Section Selection -->
                        <div class="form-group d-none" id="class_section_selector">
                            <label>Select Class Section:</label>
                            {!! Form::select('class_section_id', $classSections ?? [], null, ['class' => 'form-control select2', 'placeholder' => 'Select a Class Section', 'id' => 'class_section_id']) !!}
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
                            <small class="text-info">Available placeholders: {school_name}, {student_name}, {student_class}</small>
                            <small class="form-text text-muted d-block">
                                {school_name} always fills. {student_name} and {student_class} fill for the
                                All Students, Class and Class Section groups only &mdash; the All Parents and
                                All Staff groups carry no per-person details, so a template using them will
                                reach those groups with a blank where the name should be. Any other token is
                                sent as literal text.
                            </small>
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
        // jQuery ships in the deferred Vite module, which executes after this
        // inline block. Poll until jQuery and select2 are both ready, then
        // wire the compose form — a plain document-ready handler here
        // would throw on the undefined $ and kill every behaviour below,
        // including the class selector toggles.
        (function () {
            function initCompose(attempt) {
                if (!(window.jQuery && window.jQuery.fn && window.jQuery.fn.select2)) {
                    // CDN blocked or bundle missing: give up quietly instead of
                    // polling forever.
                    if (attempt < 100) {
                        setTimeout(function () { initCompose(attempt + 1); }, 50);
                    }
                    return;
                }

                var $ = window.jQuery;

                // Select2 fields are initialised centrally by the layout
                // (guarded against double-init), so no per-view init here.

                // Show/hide the class and class-section pickers for the
                // chosen recipient group. Also run once at startup so a
                // re-rendered form (validation error, ?type= deep link)
                // keeps the right selector visible.
                function syncSelectorVisibility() {
                    var group = $('#recipient_group').val();
                    $('#class_selector').toggleClass('d-none', group !== 'Class');
                    $('#class_section_selector').toggleClass('d-none', group !== 'Class Section');
                }

                // Toggle Message Type
                $('input[name="message_type"]').change(function () {
                    var type = $(this).val();
                    if (type === 'SMS') {
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
                    updateRecipientCount();
                });

                // Recipient estimate (AJAX count + SMS cost)
                function updateRecipientCount() {
                    var group = $('#recipient_group').val();
                    var classId = $('select[name="class_id"]').val();
                    var classSectionId = $('select[name="class_section_id"]').val();
                    var type = $('input[name="message_type"]:checked').val();

                    if (!group) {
                        $('#recipient_estimate').text('Select a group to see count.');
                        return;
                    }

                    $('#recipient_estimate').html('<i class="fas fa-spinner fa-spin"></i> Calculating...');

                    $.ajax({
                        url: '{{ route('communication.api.recipients.count') }}',
                        method: 'GET',
                        data: {
                            recipient_group: group,
                            class_id: classId,
                            class_section_id: classSectionId,
                            message_type: type,
                        },
                        success: function (response) {
                            var cost = type === 'SMS' ? (response.count * 0.80).toFixed(2) : '0.00';
                            $('#recipient_estimate').html(
                                '<strong class="text-dark">' + response.count + ' recipients</strong>' +
                                (type === 'SMS' ? '<br><small class="text-muted">Est. cost: KES ' + cost + '</small>' : '')
                            );
                        },
                        error: function () {
                            $('#recipient_estimate').text('Could not calculate count.');
                        }
                    });
                }

                $('#recipient_group').change(function () {
                    syncSelectorVisibility();
                    updateRecipientCount();
                });

                $('select[name="class_id"]').change(updateRecipientCount);
                $('select[name="class_section_id"]').change(updateRecipientCount);

                // Load Template Content
                $('#template_id').change(function () {
                    var id = $(this).val();
                    var type = $('input[name="message_type"]:checked').val();
                    if (id) {
                        $.get('{{ url('communication/api/template') }}/' + type + '/' + id, function (data) {
                            if (type === 'SMS') {
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
                $('#message_content').on('keyup input', function () {
                    updatePreview();
                });

                function updatePreview() {
                    var content = $('#message_content').val();
                    $('#preview_area').html(content.replace(/\n/g, '<br>'));
                    $('#char_count').text(content.length + ' chars');
                }

                // Pre-select template if in URL
                var urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('type')) {
                    var type = urlParams.get('type');
                    if (type === 'Email') {
                        $('#type_email').prop('checked', true).trigger('change');
                        $('#type_email').parent().addClass('active');
                        $('#type_sms').parent().removeClass('active');
                    }
                }
                if (urlParams.has('template_id')) {
                    $('#template_id').val(urlParams.get('template_id')).trigger('change');
                }

                // Re-rendered form: restore selector visibility for the
                // currently chosen group without firing the AJAX estimate.
                syncSelectorVisibility();
            }

            document.addEventListener('DOMContentLoaded', function () {
                initCompose(0);
            });
        })();
    </script>
@endpush
