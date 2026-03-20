@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Sections</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('sections.create') }}">
                        Add New
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
            <h5 class="text-secondary font-weight-bold mb-3 mb-md-0">Manage Class Sections</h5>
            <div class="d-flex flex-column flex-sm-row" style="gap: 10px;">
                <div class="input-group input-group-sm mb-2 mb-sm-0" style="width: 100%; max-width: 250px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" id="sectionSearch" class="form-control border-left-0 pl-0" placeholder="Search sections or class..." style="box-shadow: none;">
                </div>
                <div class="d-flex" style="gap: 10px; width: 100%;">
                    <button class="btn btn-sm flex-fill" onclick="expandAll()" style="background-color: #f1f5f9; color: #475569; font-weight: 600;">
                        <i class="fas fa-expand-alt mr-1"></i> Expand
                    </button>
                    <button class="btn btn-sm flex-fill" onclick="collapseAll()" style="background-color: #f1f5f9; color: #475569; font-weight: 600;">
                        <i class="fas fa-compress-alt mr-1"></i> Collapse
                    </button>
                </div>
            </div>
        </div>

        @include('sections.table')
    </div>

@endsection

@push('page_scripts')
<script>
    $(document).ready(function() {
        // Search Functionality
        $('#sectionSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();

            $('.section-group').each(function() {
                var group = $(this);
                var groupName = group.data('group-name');
                var groupMatches = groupName.indexOf(value) > -1;
                var hasVisibleRows = false;

                var rows = group.find('.section-row');
                
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
            $('.section-group .card').removeClass('collapsed-card');
            $('.section-group .card-body').show();
            $('.section-group .btn-tool i').removeClass('fa-plus').addClass('fa-minus');
        };

        window.collapseAll = function() {
            $('.section-group .card').addClass('collapsed-card');
            $('.section-group .card-body').hide();
            $('.section-group .btn-tool i').removeClass('fa-minus').addClass('fa-plus');
        };
    });
</script>
@endpush
