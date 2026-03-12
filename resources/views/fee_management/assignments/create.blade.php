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

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Fee Assignment Wizard</h3>
            </div>
            {!! Form::open(['route' => 'fees.assignments.store']) !!}
            <div class="card-body">
                <!-- Step 1: Assignment Type -->
                <div class="form-group">
                    <label>How do you want to assign fees?</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="assignment_type" id="type_bulk" value="bulk_class" checked>
                        <label class="form-check-label font-weight-bold" for="type_bulk">
                            Bulk Assign by Class (All students in a class)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="assignment_type" id="type_individual" value="individual">
                        <label class="form-check-label font-weight-bold" for="type_individual">
                            Individual Student Assignment
                        </label>
                    </div>
                </div>

                <hr>

                <!-- Step 2: Selection -->
                <div class="row">
                    <!-- Academic Year & Term (Common) -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Academic Year <span class="text-danger">*</span></label>
                             <select name="academic_year_id" id="academic_year_id" class="form-control" required>
                                 @if($currentYear)
                                     <option value="{{ $currentYear->academic_year_id }}">{{ $currentYear->name }}</option>
                                 @endif
                                 <!-- Logic to load others if needed -->
                             </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                         <div class="form-group">
                            <label>Term <span class="text-danger">*</span></label>
                            <select name="term" id="term" class="form-control" required>
                                <option value="Term 1">Term 1</option>
                                <option value="Term 2">Term 2</option>
                                <option value="Term 3">Term 3</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Conditional Fields -->
                <div id="bulk_fields">
                    <div class="row">
                         <div class="col-md-4">
                            <div class="form-group">
                                <label>Select Class <span class="text-danger">*</span></label>
                                {!! Form::select('class_id', $classes, null, ['class' => 'form-control', 'placeholder' => 'Select a Class', 'id' => 'class_id']) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div id="individual_fields" style="display: none;">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Student ID/Name <span class="text-danger">*</span></label>
                                <!-- This should ideally be an autocomplete or select2 -->
                                <input type="number" name="student_id" class="form-control" placeholder="Enter Student ID">
                                <small class="text-muted">Enter the Student ID directly for now.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Select Fees -->
                <div id="fee_selection_area" style="display: none;">
                    <h5 class="mt-4 text-primary"><i class="fas fa-list"></i> Select Fees to Assign</h5>
                    <div class="alert alert-info" id="loading_fees" style="display: none;">Loading available fees...</div>
                    <div id="no_fees_msg" class="alert alert-warning" style="display: none;">No fees found for this class/year configuration.</div>
                    
                    <table class="table table-bordered" id="fees_table">
                         <thead>
                            <tr>
                                <th width="50"><input type="checkbox" id="select_all"></th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Frequency</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody id="fees_table_body">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="card-footer">
                {!! Form::submit('Assign Fees', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('fees.assignments.index') }}" class="btn btn-default">Cancel</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>

    @push('page_scripts')
    <script>
        $(document).ready(function() {
            // Toggle Logic
            $('input[name="assignment_type"]').change(function() {
                if ($(this).val() == 'bulk_class') {
                    $('#bulk_fields').show();
                    $('#individual_fields').hide();
                    $('#class_id').prop('required', true);
                    $('input[name="student_id"]').prop('required', false);
                } else {
                    $('#bulk_fields').hide();
                    $('#individual_fields').show();
                    $('#class_id').prop('required', false);
                    $('input[name="student_id"]').prop('required', true);
                    // For individual, we might need to select class first to load fees, or just load all fees? 
                    // To keep it simple, let's ask for manual class selection even for individual to filter fees, 
                    // or better, show the class dropdown in individual mode too to "Load Fees Structure for Class...".
                    // But typically individual fee might differ. 
                    // Let's assume for individual, we still pick fees based on a "Reference Class" or show all.
                    // For simplicity in this wizard, let's enforce selecting a class to pull the structure from.
                    $('#bulk_fields').show(); // Keep class visible to filter fees
                     $('#class_id').prop('required', true); // Still required to find the fees
                }
            });

            // AJAX to fetch fees when Class and Year are selected
            function loadFees() {
                var classId = $('#class_id').val();
                var yearId = $('#academic_year_id').val();

                if (classId && yearId) {
                    $('#fee_selection_area').show();
                    $('#loading_fees').show();
                    $('#fees_table tbody').empty();
                    $('#no_fees_msg').hide();

                    $.ajax({
                        url: "{{ route('fees.assignments.ajax.class-fees') }}",
                        type: "GET",
                        data: {
                            class_id: classId,
                            academic_year_id: yearId
                        },
                        success: function(response) {
                            $('#loading_fees').hide();
                            if (response.length > 0) {
                                $.each(response, function(index, fee) {
                                    var row = `<tr>
                                        <td><input type="checkbox" name="fees[]" value="${fee.fee_structure_id}" checked class="fee-checkbox"></td>
                                        <td>${fee.category.name}</td>
                                        <td>${fee.amount}</td>
                                        <td>${fee.payment_frequency}</td>
                                        <td><span class="badge badge-${fee.category.type == 'mandatory' ? 'info' : 'secondary'}">${fee.category.type}</span></td>
                                    </tr>`;
                                    $('#fees_table tbody').append(row);
                                });
                            } else {
                                $('#no_fees_msg').show();
                            }
                        },
                        error: function() {
                            $('#loading_fees').hide();
                            alert('Error loading fees.');
                        }
                    });
                }
            }

            $('#class_id, #academic_year_id').change(loadFees);

            $('#select_all').click(function() {
                $('.fee-checkbox').prop('checked', this.checked);
            });
        });
    </script>
    @endpush
@endsection
