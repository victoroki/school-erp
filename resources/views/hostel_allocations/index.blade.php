@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="mb-1">Hostel Allocations</h1>
                    <p class="text-dark mb-0">
                        <span class="badge badge-info">{{ $hostelAllocations->total() }}</span>
                        <small>allocation{{ $hostelAllocations->total() === 1 ? '' : 's' }} match the filters</small>
                    </p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('hostel-allocations.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Allocate a Bed
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card card-outline card-primary mb-3">
            <div class="card-body">
                <form action="{{ route('hostel-allocations.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <div class="form-group mb-0">
                                <label>Search</label>
                                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                                       class="form-control" placeholder="Student name or admission no.">
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="form-group mb-0">
                                <label>Hostel</label>
                                {!! Form::select('hostel_id', ['' => 'All Hostels'] + $hostels, $filters['hostel_id'] ?? '', ['class' => 'form-control select2', 'style' => 'width: 100%']) !!}
                            </div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="form-group mb-0">
                                <label>Class</label>
                                {!! Form::select('class_id', ['' => 'All Classes'] + $classes, $filters['class_id'] ?? '', ['class' => 'form-control select2', 'id' => 'class_select', 'style' => 'width: 100%']) !!}
                            </div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="form-group mb-0">
                                <label>Stream</label>
                                <select name="section_id" id="section_select" class="form-control select2" style="width: 100%">
                                    <option value="">All Streams</option>
                                    @foreach($sections as $sectionId => $sectionName)
                                        <option value="{{ $sectionId }}"
                                                data-class="{{ $sectionClasses[$sectionId] ?? '' }}"
                                                @selected((string) ($filters['section_id'] ?? '') === (string) $sectionId)>
                                            {{ $sectionName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 mb-2">
                            <div class="form-group mb-0">
                                <label>Status</label>
                                {!! Form::select('status', [
                                    '' => 'All Statuses',
                                    'active' => 'Active',
                                    'pending' => 'Pending',
                                    'vacated' => 'Vacated',
                                ], $filters['status'] ?? '', ['class' => 'form-control select2', 'style' => 'width: 100%']) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row align-items-end">
                        <div class="col-md-3 mb-2">
                            <div class="form-group mb-0">
                                <label>Academic Year</label>
                                {!! Form::select('academic_year_id', ['' => 'All Years'] + $academicYears, $filters['academic_year_id'] ?? '', ['class' => 'form-control select2', 'style' => 'width: 100%']) !!}
                            </div>
                        </div>
                        <div class="col-md-5 mb-2">
                            <button type="submit" class="btn btn-primary mr-1"><i class="fas fa-search mr-1"></i> Filter</button>
                            <a href="{{ route('hostel-allocations.index') }}" class="btn btn-default mr-1">Reset</a>
                            <a href="{{ route('hostel-allocations.bulk-form') }}" class="btn btn-info">
                                <i class="fas fa-users mr-1"></i> Bulk Allocation
                            </a>
                        </div>
                        <div class="col-md-4 mb-2 text-md-right">
                            @php $exportQuery = array_filter($filters); @endphp
                            <a href="{{ route('hostel-allocations.export', $exportQuery) }}" class="btn btn-outline-danger btn-sm">
                                <i class="fas fa-file-pdf mr-1"></i> Export these results (PDF)
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            @include('hostel_allocations.table')
        </div>
    </div>

@endsection

@push('page_scripts')
    <script>
        // Streams belong to a class, so narrow the stream list to the chosen class.
        (function () {
            var classSelect = document.getElementById('class_select');
            var sectionSelect = document.getElementById('section_select');
            if (!classSelect || !sectionSelect) { return; }

            function apply() {
                var chosen = classSelect.value;
                var invalid = false;

                Array.prototype.forEach.call(sectionSelect.options, function (option) {
                    if (!option.value) { return; }
                    var matches = !chosen || option.getAttribute('data-class') === chosen;
                    option.hidden = !matches;
                    option.disabled = !matches;
                    if (!matches && option.selected) { invalid = true; }
                });

                if (invalid) { sectionSelect.value = ''; }

                if (window.jQuery && window.jQuery.fn.select2) {
                    var $section = window.jQuery(sectionSelect);
                    if ($section.data('select2')) { $section.trigger('change.select2'); }
                }
            }

            classSelect.addEventListener('change', apply);
            apply();
        })();
    </script>
@endpush
