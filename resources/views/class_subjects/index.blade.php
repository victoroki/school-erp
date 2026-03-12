@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Class Subjects</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('class-subjects.create') }}">
                        Add New
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="clearfix"></div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" id="classSubjectSearch" class="form-control" placeholder="Search subjects or classes...">
                    </div>
                    <div class="col-md-6 text-right">
                        <button class="btn btn-default btn-sm" onclick="expandAll()">
                            <i class="fas fa-expand-arrows-alt"></i> Expand All
                        </button>
                        <button class="btn btn-default btn-sm" onclick="collapseAll()">
                            <i class="fas fa-compress-arrows-alt"></i> Collapse All
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @include('class_subjects.table')
    </div>

@endsection

@push('page_scripts')
<script>
    $(document).ready(function() {
        // Search Functionality
        $('#classSubjectSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();

            $('.class-group').each(function() {
                var group = $(this);
                var groupName = group.data('group-name');
                var groupMatches = groupName.indexOf(value) > -1;
                var hasVisibleRows = false;

                var rows = group.find('.subject-row');
                
                if (groupMatches) {
                    rows.show();
                    hasVisibleRows = true;
                } else {
                    rows.each(function() {
                        var row = $(this);
                        var text = row.text().toLowerCase();
                        if (text.indexOf(value) > -1) {
                            row.show();
                            hasVisibleRows = true;
                        } else {
                            row.hide();
                        }
                    });
                }

                if (hasVisibleRows) {
                    group.show();
                } else {
                    group.hide();
                }
            });
        });

        // Expand/Collapse All
        window.expandAll = function() {
            $('.class-group .card').removeClass('collapsed-card');
            $('.class-group .card-body').show();
            $('.class-group .btn-tool i').removeClass('fa-plus').addClass('fa-minus');
        };

        window.collapseAll = function() {
            $('.class-group .card').addClass('collapsed-card');
            $('.class-group .card-body').hide();
            $('.class-group .btn-tool i').removeClass('fa-minus').addClass('fa-plus');
        };
    });
</script>
@endpush
