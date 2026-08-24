@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Assign Fees</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('fees.assignments.index') }}">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card" style="border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header bg-white" style="border-bottom: 1px solid #e5e7eb; padding: 16px 24px;">
                <h3 class="card-title mb-0" style="font-weight: 600; color: #1f2937; font-size: 1.1rem;">
                    <span style="color: #0073e7;">✦</span> Fee Assignment Wizard
                </h3>
            </div>
            {!! Form::open(['route' => 'fees.assignments.store', 'id' => 'feeAssignmentForm']) !!}
            <div class="card-body" style="padding: 24px;">
                
                {{-- Step 1: Assignment Type --}}
                <div class="mb-5">
                    <label class="d-block mb-3" style="font-weight: 600; color: #374151; font-size: 0.95rem;">
                        <span class="badge" style="background: #0073e7; color: white; border-radius: 50%; width: 24px; height: 24px; padding: 0; line-height: 24px; font-size: 12px; margin-right: 8px;">1</span>
                        How do you want to assign fees?
                    </label>
                    <div class="row g-3" id="assignmentTypeCards">
                        <div class="col-md-4">
                            <div class="type-card type-card--selected" data-value="bulk_class" style="
                                cursor: pointer;
                                border: 2px solid #0073e7;
                                background: #f0f7ff;
                                border-radius: 8px;
                                transition: transform 160ms ease-out, box-shadow 160ms ease-out, background 200ms ease-out;
                            ">
                                <input type="radio" name="assignment_type" value="bulk_class" checked style="position: absolute; opacity: 0;">
                                <div class="card-body text-center py-3 px-3">
                                    <i class="fas fa-users fa-lg mb-2" style="color: #0073e7; display: block;"></i>
                                    <h6 class="font-weight-bold mb-1" style="color: #0073e7;">By Class</h6>
                                    <small class="text-muted d-block">Assign to all students in a class</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="type-card" data-value="auto_all_classes" style="
                                cursor: pointer;
                                border: 2px solid #dee2e6;
                                background: white;
                                border-radius: 8px;
                                transition: transform 160ms ease-out, box-shadow 160ms ease-out, background 200ms ease-out;
                            ">
                                <input type="radio" name="assignment_type" value="auto_all_classes" style="position: absolute; opacity: 0;">
                                <div class="card-body text-center py-3 px-3">
                                    <i class="fas fa-magic fa-lg mb-2" style="color: #6c757d; display: block;"></i>
                                    <h6 class="font-weight-bold mb-1" style="color: #495057;">Auto Assign All</h6>
                                    <small class="text-muted d-block">Auto-detect class & assign fees</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="type-card" data-value="individual" style="
                                cursor: pointer;
                                border: 2px solid #dee2e6;
                                background: white;
                                border-radius: 8px;
                                transition: transform 160ms ease-out, box-shadow 160ms ease-out, background 200ms ease-out;
                            ">
                                <input type="radio" name="assignment_type" value="individual" style="position: absolute; opacity: 0;">
                                <div class="card-body text-center py-3 px-3">
                                    <i class="fas fa-user fa-lg mb-2" style="color: #6c757d; display: block;"></i>
                                    <h6 class="font-weight-bold mb-1" style="color: #495057;">Individual</h6>
                                    <small class="text-muted d-block">Assign to a specific student</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr style="border-top: 1px solid #e5e7eb; margin: 24px 0;">

                {{-- Step 2: Academic Year & Term --}}
                <div class="mb-4">
                    <label class="d-block mb-3" style="font-weight: 600; color: #374151; font-size: 0.95rem;">
                        <span class="badge" style="background: #0073e7; color: white; border-radius: 50%; width: 24px; height: 24px; padding: 0; line-height: 24px; font-size: 12px; margin-right: 8px;">2</span>
                        Academic Details
                    </label>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px;">
                                <label style="font-weight: 600; color: #475569; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.025em; margin-bottom: 8px; display: block;">Academic Year</label>
                                <div class="input-group" style="background: white; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; height: 44px;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="background: transparent; border: none; color: #94a3b8;">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <select name="academic_year_id" id="academic_year_id" class="form-control" style="border: none; background: transparent; height: 100%; font-weight: 500; color: #1e293b;" required>
                                        @if($currentYear)
                                            <option value="{{ $currentYear->academic_year_id }}">{{ $currentYear->name }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px;">
                                <label style="font-weight: 600; color: #475569; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.025em; margin-bottom: 8px; display: block;">Billing Term</label>
                                <div class="input-group" style="background: white; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; height: 44px;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="background: transparent; border: none; color: #94a3b8;">
                                            <i class="fas fa-layer-group"></i>
                                        </span>
                                    </div>
                                    <select name="term" id="term" class="form-control" style="border: none; background: transparent; height: 100%; font-weight: 500; color: #1e293b;" required>
                                        <option value="Term 1">Term 1</option>
                                        <option value="Term 2">Term 2</option>
                                        <option value="Term 3">Term 3</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Selection Fields --}}
                <div id="bulk_fields" class="selection-section mb-4">
                    <label class="d-block mb-3" style="font-weight: 600; color: #374151; font-size: 0.95rem;">
                        <span class="badge" style="background: #0073e7; color: white; border-radius: 50%; width: 24px; height: 24px; padding: 0; line-height: 24px; font-size: 12px; margin-right: 8px;">3</span>
                        Select Class
                    </label>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="p-3" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px;">
                                <label style="font-weight: 600; color: #475569; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.025em; margin-bottom: 8px; display: block;">Primary Class</label>
                                <div class="input-group" style="background: white; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; height: 44px;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="background: transparent; border: none; color: #94a3b8;">
                                            <i class="fas fa-graduation-cap"></i>
                                        </span>
                                    </div>
                                    <select name="class_id" id="class_id" class="form-control" style="border: none; background: transparent; height: 100%; font-weight: 500; color: #1e293b;">
                                        <option value="">Select a Class</option>
                                        @foreach($classes as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="auto_all_fields" class="selection-section mb-4" style="display: none;">
                    <label class="d-block mb-3" style="font-weight: 600; color: #374151; font-size: 0.95rem;">
                        <span class="badge" style="background: #0073e7; color: white; border-radius: 50%; width: 24px; height: 24px; padding: 0; line-height: 24px; font-size: 12px; margin-right: 8px;">3</span>
                        Auto Assignment Preview
                    </label>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="p-4" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1.5px solid #86efac; border-radius: 12px;">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fas fa-info-circle mr-2" style="color: #16a34a; font-size: 1.25rem;"></i>
                                    <h6 class="mb-0 font-weight-bold" style="color: #166534;">Automatic Fee Assignment</h6>
                                </div>
                                <p class="mb-3" style="color: #15803d; font-size: 0.875rem;">
                                    The system will automatically detect each student's class and assign the appropriate fees. 
                                    No manual class or fee selection required. Duplicates will be skipped.
                                </p>
                                
                                <div id="auto_preview_content" class="mb-3">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="p-2" style="background: white; border-radius: 8px;">
                                                <div id="preview_students" class="font-weight-bold" style="color: #0073e7; font-size: 1.5rem;">-</div>
                                                <small class="text-muted">Active Students</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-2" style="background: white; border-radius: 8px;">
                                                <div id="preview_fees" class="font-weight-bold" style="color: #0073e7; font-size: 1.5rem;">-</div>
                                                <small class="text-muted">Fee Structures</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-2" style="background: white; border-radius: 8px;">
                                                <div id="preview_assigned" class="font-weight-bold" style="color: #6b7280; font-size: 1.5rem;">-</div>
                                                <small class="text-muted">Already Assigned</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="p-2" style="background: white; border-radius: 8px;">
                                                <div id="preview_new" class="font-weight-bold" style="color: #16a34a; font-size: 1.5rem;">-</div>
                                                <small class="text-muted">New Assignments</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="auto_loading" class="d-flex align-items-center" style="display: none;">
                                    <div class="spinner-border spinner-border-sm mr-2" role="status" style="color: #16a34a;">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <span style="color: #15803d; font-size: 0.875rem;">Calculating preview...</span>
                                </div>

                                <div class="alert mb-0" style="background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; border-radius: 8px; font-size: 0.8125rem;">
                                    <i class="fas fa-lightbulb mr-1"></i>
                                    <strong>Tip:</strong> New students will automatically receive fees when enrolled via the StudentClassEnrollmentObserver.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="individual_fields" class="selection-section mb-4" style="display: none;">
                    <label class="d-block mb-3" style="font-weight: 600; color: #374151; font-size: 0.95rem;">
                        <span class="badge" style="background: #0073e7; color: white; border-radius: 50%; width: 24px; height: 24px; padding: 0; line-height: 24px; font-size: 12px; margin-right: 8px;">3</span>
                        Select Student
                    </label>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="p-3" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px;">
                                <label style="font-weight: 600; color: #475569; font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.025em; margin-bottom: 8px; display: block;">Search Student</label>
                                <div class="input-group" id="studentContainer" style="background: white; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; height: 44px;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" style="background: transparent; border: none; color: #94a3b8;">
                                            <i class="fas fa-search"></i>
                                        </span>
                                    </div>
                                    <select name="student_id" id="student_id" class="form-control select2-student" data-placeholder="Start typing name or admission number..." style="border: none; background: transparent; height: 100%;">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 4: Fee Selection --}}
                <div id="fee_selection_area" class="selection-section" style="display: none; margin-top: 24px;">
                    <div class="d-flex align-items-center mb-3">
                        <label class="mb-0" style="font-weight: 600; color: #374151; font-size: 0.95rem;">
                            <span class="badge" style="background: #0073e7; color: white; border-radius: 50%; width: 24px; height: 24px; padding: 0; line-height: 24px; font-size: 12px; margin-right: 8px;">4</span>
                            Select Fees to Assign
                        </label>
                        <span class="badge ml-2" id="selected_count" style="background: #dbeafe; color: #1d4ed8; font-size: 12px; padding: 4px 10px; border-radius: 12px;">0 selected</span>
                    </div>

                    <div id="loading_fees" class="d-flex align-items-center p-3 mb-3" style="background: #f9fafb; border-radius: 8px; display: none;">
                        <div class="spinner-border spinner-border-sm mr-2" role="status" style="color: #0073e7;">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <span style="color: #6b7280; font-size: 0.875rem;">Loading available fees...</span>
                    </div>

                    <div id="no_fees_msg" class="alert mb-3" style="background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; border-radius: 8px; display: none;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        No fees found for the selected configuration.
                    </div>

                    <div id="bulk_actions_bar" class="mb-3 d-flex justify-content-between align-items-center p-2" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; display: none !important;">
                        <div class="d-flex gap-2">
                            <button type="button" id="btn_select_all" class="btn btn-sm btn-white" style="background: white; border: 1px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 0.75rem; border-radius: 6px; padding: 6px 12px;">
                                <i class="far fa-check-square mr-1"></i> SELECT ALL
                            </button>
                            <button type="button" id="btn_deselect_all" class="btn btn-sm btn-white" style="background: white; border: 1px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 0.75rem; border-radius: 6px; padding: 6px 12px;">
                                <i class="far fa-square mr-1"></i> DESELECT ALL
                            </button>
                        </div>
                    <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: white;">
                        <table class="table mb-0 impeccable-table" id="fees_table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                    <th style="width: 48px; padding: 12px 16px;"></th>
                                    <th style="padding: 12px 16px; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Fee Details</th>
                                    <th style="padding: 12px 16px; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Class</th>
                                    <th style="padding: 12px 16px; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="fees_table_body">
                                {{-- Rows injected here --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                </div>

            </div>

            <div class="card-footer bg-white" style="border-top: 1px solid #e5e7eb; padding: 16px 24px;">
                <button type="submit" class="btn btn-primary" id="submitBtn" style="
                    background: linear-gradient(135deg, #0073e7 0%, #0056b3 100%);
                    border: none;
                    border-radius: 8px;
                    padding: 10px 24px;
                    font-weight: 500;
                    transition: transform 160ms ease-out, box-shadow 160ms ease-out;
                ">
                    <i class="fas fa-check mr-2"></i> Assign Fees
                </button>
                <a href="{{ route('fees.assignments.index') }}" class="btn btn-default ml-2" style="border: 1px solid #d1d5db; border-radius: 8px; color: #4b5563;">Cancel</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @push('third_party_styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1-beta.0/select2-bootstrap.min.css">
    @endpush

    @push('page_styles')
    <style>
        .selection-section {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 200ms ease-out, transform 200ms ease-out;
        }
        .selection-section.hidden {
            opacity: 0;
            transform: translateY(-8px);
        }
        .type-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,115,231,0.15);
        }
        .type-card:active {
            transform: scale(0.98);
        }
        .input-group {
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .input-group:focus-within {
            border-color: #0073e7;
            box-shadow: 0 0 0 3px rgba(0,115,231,0.1);
        }
        .input-group .form-control:focus {
            box-shadow: none !important;
        }
        #submitBtn:hover {
            box-shadow: 0 4px 12px rgba(0,115,231,0.3);
        }
        #submitBtn:active {
            transform: scale(0.97);
        }
        .form-control:focus {
            border-color: #0073e7 !important;
            box-shadow: 0 0 0 3px rgba(0,115,231,0.1) !important;
        }
        .table-hover tbody tr {
            transition: background-color 150ms ease;
        }
        .table-hover tbody tr:hover {
            background-color: #f9fafb !important;
        }
        .fee-row {
            cursor: pointer;
            transition: background-color 150ms ease;
        }
        .fee-row.selected {
            background-color: #eff6ff !important;
        }
        #class_ids, #student_id {
            width: 100% !important;
        }
        .select2-container--bootstrap .select2-selection {
            border: none !important;
            background: transparent !important;
        }
        .select2-container--bootstrap .select2-selection--multiple {
            min-height: 26px;
        }
        .select2-container--bootstrap .select2-selection--multiple .select2-selection__choice {
            background-color: #0073e7;
            border-color: #0056b3;
            color: white;
            border-radius: 4px;
            padding: 2px 8px;
        }
        .select2-container--bootstrap .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
        }
        .select2-container--bootstrap .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 8px;
        }
        @keyframes slideUpFade {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .selection-section {
            animation: slideUpFade 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
    @endpush

    @push('third_party_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js" defer></script>
    @endpush

    @push('page_scripts')
    <script>
        (function() {
            var checkInterval;
            
            function initFeeAssignment() {
                if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {

                    return;
                }
                
                clearInterval(checkInterval);

                
                // Initialize Select2
                jQuery('.select2').select2({
                    theme: 'bootstrap',
                    placeholder: 'Select classes',
                    allowClear: true
                });

                jQuery('.select2-student').select2({
                    theme: 'bootstrap',
                    placeholder: 'Search for a student...',
                    allowClear: true,
                    ajax: {
                        url: '{{ route("students.ajax.search") }}',
                        dataType: 'json',
                        delay: 250,
                        processResults: function(data) {
                            return {
                                results: data.map(function(student) {
                                    return {
                                        id: student.student_id,
                                        text: student.first_name + ' ' + student.last_name + ' (' + student.admission_no + ')'
                                    };
                                })
                            };
                        }
                    }
                });

                // Type card click handler
                jQuery('#assignmentTypeCards .type-card').on('click', function(e) {
                    e.preventDefault();
                    
                    var $card = jQuery(this);
                    var $radio = $card.find('input[type="radio"]');
                    var value = $card.data('value');
                    
                    // Update visual state
                    jQuery('#assignmentTypeCards .type-card').each(function() {
                        var $c = jQuery(this);
                        var $innerCard = $c.find('.card-body');
                        
                        if ($c.data('value') === value) {
                            $c.css('border-color', '#0073e7');
                            $c.css('background', 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%)');
                            $c.find('h6').css('color', '#1e40af');
                            $c.find('i').css('color', '#0073e7');
                            $c.find('.badge').css('background', '#dbeafe').css('color', '#1d4ed8');
                            $c.find('.badge + div').css('background', 'white');
                        } else {
                            $c.css('border-color', '#e5e7eb');
                            $c.css('background', 'white');
                            $c.find('h6').css('color', '#374151');
                            $c.find('i').css('color', '#6b7280');
                            $c.find('.badge').css('background', '#f3f4f6').css('color', '#4b5563');
                            $c.find('.card-body > div:first-child').css('background', '#f3f4f6');
                        }
                    });
                    
                    // Check the radio
                    $radio.prop('checked', true).trigger('change');
                });

                // Handle radio change
                jQuery('input[name="assignment_type"]').change(function() {
                    var val = jQuery(this).val();

                    if (val === 'bulk_class') {
                        jQuery('#bulk_fields').show().removeClass('hidden');
                        jQuery('#bulk_all_fields').hide().addClass('hidden');
                        jQuery('#auto_all_fields').hide().addClass('hidden');
                        jQuery('#individual_fields').hide().addClass('hidden');
                        jQuery('#fee_selection_area').hide().addClass('hidden');
                        jQuery('#class_id').prop('required', true);
                        jQuery('#class_ids').prop('required', false);
                        jQuery('#student_id').prop('required', false);
                        jQuery('#class_ids').val(null).trigger('change');
                    } else if (val === 'bulk_all') {
                        jQuery('#bulk_fields').hide().addClass('hidden');
                        jQuery('#bulk_all_fields').show().removeClass('hidden');
                        jQuery('#auto_all_fields').hide().addClass('hidden');
                        jQuery('#individual_fields').hide().addClass('hidden');
                        jQuery('#fee_selection_area').hide().addClass('hidden');
                        jQuery('#class_id').prop('required', false);
                        jQuery('#class_ids').prop('required', true);
                        jQuery('#student_id').prop('required', false);
                        jQuery('#class_id').val('');
                    } else if (val === 'auto_all_classes') {
                        jQuery('#bulk_fields').hide().addClass('hidden');
                        jQuery('#bulk_all_fields').hide().addClass('hidden');
                        jQuery('#auto_all_fields').show().removeClass('hidden');
                        jQuery('#individual_fields').hide().addClass('hidden');
                        jQuery('#fee_selection_area').hide().addClass('hidden');
                        jQuery('#class_id').prop('required', false);
                        jQuery('#class_ids').prop('required', false);
                        jQuery('#student_id').prop('required', false);
                        jQuery('#class_id').val('');
                        jQuery('#class_ids').val(null).trigger('change');
                        loadAutoAssignmentPreview();
                    } else {
                        jQuery('#bulk_fields').hide().addClass('hidden');
                        jQuery('#bulk_all_fields').hide().addClass('hidden');
                        jQuery('#auto_all_fields').hide().addClass('hidden');
                        jQuery('#individual_fields').show().removeClass('hidden');
                        jQuery('#fee_selection_area').hide().addClass('hidden');
                        jQuery('#class_id').prop('required', false);
                        jQuery('#class_ids').prop('required', false);
                        jQuery('#student_id').prop('required', true);
                        jQuery('#class_id').val('');
                        jQuery('#class_ids').val(null).trigger('change');
                        loadFeesForIndividual();
                    }
                });

                function loadFees() {
                    var classId = jQuery('#class_id').val();
                    var yearId = jQuery('#academic_year_id').val();

                    if (classId && yearId) {
                        jQuery('#fee_selection_area').show().removeClass('hidden');
                        jQuery('#loading_fees').show();
                        jQuery('#fees_table_body').empty();
                        jQuery('#no_fees_msg').hide();

                        jQuery.ajax({
                            url: "{{ route('fees.assignments.ajax.class-fees') }}",
                            type: "GET",
                            data: {
                                class_id: classId,
                                academic_year_id: yearId
                            },
                            success: function(response) {
                                jQuery('#loading_fees').hide();
                                if (response.length > 0) {
                                    renderFeesTable(response);
                                } else {
                                    jQuery('#no_fees_msg').show();
                                }
                            },
                            error: function() {
                                jQuery('#loading_fees').hide();
                                alert('Error loading fees.');
                            }
                        });
                    }
                }

                function loadFeesForAll() {
                    var classIds = jQuery('#class_ids').val();
                    var yearId = jQuery('#academic_year_id').val();

                    if (classIds && classIds.length > 0 && yearId) {
                        jQuery('#fee_selection_area').show().removeClass('hidden');
                        jQuery('#loading_fees').show();
                        jQuery('#fees_table_body').empty();
                        jQuery('#no_fees_msg').hide();

                        jQuery.ajax({
                            url: "{{ route('fees.assignments.ajax.classes-fees') }}",
                            type: "GET",
                            data: {
                                class_ids: classIds,
                                academic_year_id: yearId
                            },
                            success: function(response) {
                                jQuery('#loading_fees').hide();
                                if (response.length > 0) {
                                    renderFeesTable(response);
                                } else {
                                    jQuery('#no_fees_msg').show();
                                }
                            },
                            error: function() {
                                jQuery('#loading_fees').hide();
                                alert('Error loading fees.');
                            }
                        });
                    }
                }

                function loadFeesForIndividual() {
                    var yearId = jQuery('#academic_year_id').val();

                    if (yearId) {
                        jQuery('#fee_selection_area').show().removeClass('hidden');
                        jQuery('#loading_fees').show();
                        jQuery('#fees_table_body').empty();
                        jQuery('#no_fees_msg').hide();

                        jQuery.ajax({
                            url: "{{ route('fees.assignments.ajax.classes-fees') }}",
                            type: "GET",
                            data: {
                                class_ids: [],
                                academic_year_id: yearId
                            },
                            success: function(response) {
                                jQuery('#loading_fees').hide();
                                if (response.length > 0) {
                                    renderFeesTable(response);
                                } else {
                                    jQuery('#no_fees_msg').show();
                                }
                            },
                            error: function() {
                                jQuery('#loading_fees').hide();
                                alert('Error loading fees.');
                            }
                        });
                    }
                }

                function loadAutoAssignmentPreview() {
                    var yearId = jQuery('#academic_year_id').val();
                    var term = jQuery('#term').val();

                    if (yearId && term) {
                        jQuery('#auto_loading').show();
                        jQuery('#preview_students').text('-');
                        jQuery('#preview_fees').text('-');
                        jQuery('#preview_assigned').text('-');
                        jQuery('#preview_new').text('-');

                        jQuery.ajax({
                            url: "{{ route('fees.assignments.ajax.auto-preview') }}",
                            type: "GET",
                            data: {
                                academic_year_id: yearId,
                                term: term
                            },
                            success: function(response) {
                                jQuery('#auto_loading').hide();
                                jQuery('#preview_students').text(response.students_with_enrollment);
                                jQuery('#preview_fees').text(response.fee_structures_count);
                                jQuery('#preview_assigned').text(response.already_assigned_students);
                                jQuery('#preview_new').text(response.potential_new_assignments);
                            },
                            error: function() {
                                jQuery('#auto_loading').hide();
                                alert('Error loading preview.');
                            }
                        });
                    }
                }

                function renderFeesTable(fees) {
                    jQuery('#fees_table_body').empty();
                    if (fees.length > 0) {
                        jQuery('#bulk_actions_bar').attr('style', 'background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; display: flex !important;');
                        jQuery('#grid_total_count').text(fees.length + ' ITEMS TOTAL');
                    } else {
                        jQuery('#bulk_actions_bar').hide();
                    }
                    
                    jQuery.each(fees, function(index, fee) {
                        var isMandatory = fee.category && fee.category.type === 'mandatory';
                        var badgeColor = isMandatory ? '#0ea5e9' : '#64748b';
                        var badgeBg = isMandatory ? '#f0f9ff' : '#f8fafc';
                        var feeName = fee.category ? fee.category.name : 'Uncategorized';
                        var className = fee.school_class ? fee.school_class.name : 'Global';
                        var amount = parseFloat(fee.amount).toLocaleString();
                        
                        var row = `
                        <tr class="fee-item-row" data-fee-id="${fee.fee_structure_id}" style="border-bottom: 1px solid #e2e8f0; cursor: pointer; transition: background-color 150ms ease;">
                            <td style="padding: 16px; vertical-align: middle;">
                                <div class="custom-control custom-checkbox" style="pointer-events: none;">
                                    <input type="checkbox" class="custom-control-input fee-checkbox" name="fees[]" value="${fee.fee_structure_id}" id="fee_${fee.fee_structure_id}" checked>
                                    <label class="custom-control-label" for="fee_${fee.fee_structure_id}"></label>
                                </div>
                            </td>
                            <td style="padding: 16px; vertical-align: middle;">
                                <div style="font-weight: 600; color: #1e293b; font-size: 0.95rem; margin-bottom: 4px;">${feeName}</div>
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <span style="font-size: 0.75rem; font-weight: 500; color: #64748b;">${fee.payment_frequency}</span>
                                    <span style="background: ${badgeBg}; color: ${badgeColor}; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase;">${isMandatory ? 'Mandatory' : 'Optional'}</span>
                                </div>
                            </td>
                            <td style="padding: 16px; vertical-align: middle; color: #475569; font-size: 0.875rem; font-weight: 500;">
                                ${className}
                            </td>
                            <td style="padding: 16px; vertical-align: middle; text-align: right;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 1.05rem;">KES ${amount}</div>
                            </td>
                        </tr>`;
                        jQuery('#fees_table_body').append(row);
                    });
                    
                    // Update selection visuals for initially checked items
                    jQuery('.fee-item-row').each(function() {
                        if (jQuery(this).find('.fee-checkbox').is(':checked')) {
                            applyRowSelectedStyle(jQuery(this));
                        }
                    });
                    
                    updateSelectedCount();
                }

                function applyRowSelectedStyle($row) {
                    $row.css('background-color', '#eff6ff');
                }

                function removeRowSelectedStyle($row) {
                    $row.css('background-color', 'transparent');
                }

                function updateSelectedCount() {
                    var count = jQuery('.fee-checkbox:checked').length;
                    jQuery('#selected_count').text(count + ' selected');
                }

                // Fee loading triggers
                jQuery('#class_id, #academic_year_id, #term').change(function() {
                    var type = jQuery('input[name="assignment_type"]:checked').val();
                    if (type === 'bulk_class') {
                        loadFees();
                    } else if (type === 'bulk_all') {
                        loadFeesForAll();
                    } else if (type === 'individual') {
                        loadFeesForIndividual();
                    } else if (type === 'auto_all_classes') {
                        loadAutoAssignmentPreview();
                    }
                });

                jQuery('#class_ids').change(function() {
                    var type = jQuery('input[name="assignment_type"]:checked').val();
                    if (type === 'bulk_all') {
                        loadFeesForAll();
                    }
                });

                jQuery('#student_id').change(function() {
                    var type = jQuery('input[name="assignment_type"]:checked').val();
                    if (type === 'individual' && jQuery(this).val()) {
                        loadFeesForIndividual();
                    }
                });

                // Bulk select actions
                jQuery(document).on('click', '#btn_select_all', function() {
                    jQuery('.fee-checkbox').prop('checked', true).trigger('change');
                });

                jQuery(document).on('click', '#btn_deselect_all', function() {
                    jQuery('.fee-checkbox').prop('checked', false).trigger('change');
                });

                // Individual fee checkbox
                jQuery(document).on('change', '.fee-checkbox', function() {
                    updateSelectedCount();
                    var $row = jQuery(this).closest('.fee-item-row');
                    if (this.checked) {
                        applyRowSelectedStyle($row);
                    } else {
                        removeRowSelectedStyle($row);
                    }
                });

                // Row click to toggle checkbox
                jQuery(document).on('click', '.fee-item-row', function(e) {
                    if (!jQuery(e.target).closest('.custom-checkbox').length) {
                        var checkbox = jQuery(this).find('.fee-checkbox');
                        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
                    }
                });

                // Hover effects
                jQuery(document).on('mouseenter', '.fee-item-row', function() {
                    if (!jQuery(this).find('.fee-checkbox').is(':checked')) {
                        jQuery(this).css('background-color', '#f8fafc');
                    }
                }).on('mouseleave', '.fee-item-row', function() {
                    if (!jQuery(this).find('.fee-checkbox').is(':checked')) {
                        jQuery(this).css('background-color', 'transparent');
                    }
                });

                // Form validation
                jQuery('#feeAssignmentForm').on('submit', function(e) {
                    var type = jQuery('input[name="assignment_type"]:checked').val();
                    
                    if (type === 'auto_all_classes') {
                        var $btn = jQuery('#submitBtn');
                        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span> Assigning Fees to All Students...');
                        return true;
                    }

                    var selectedFees = jQuery('.fee-checkbox:checked').length;
                    if (selectedFees === 0) {
                        e.preventDefault();
                        alert('Please select at least one fee to assign.');
                        return false;
                    }

                    if (type === 'bulk_all') {
                        var classIds = jQuery('#class_ids').val();
                        if (!classIds || classIds.length === 0) {
                            e.preventDefault();
                            alert('Please select at least one class.');
                            return false;
                        }
                    }

                    var $btn = jQuery('#submitBtn');
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span> Assigning...');
                });

                // Set initial state
                jQuery('#bulk_fields').show();
                jQuery('#assignmentTypeCards .type-card').first().trigger('click');
                

            }
            
            // Poll for dependencies
            checkInterval = setInterval(function() {
                if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                    initFeeAssignment();
                }
            }, 50);
            
            // Timeout after 5 seconds
            setTimeout(function() {
                clearInterval(checkInterval);
            }, 5000);
        })();
    </script>
    @endpush
@endsection