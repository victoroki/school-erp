@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="mb-1">Hostel Allocation Register</h1>
                    <p class="text-muted mb-0">
                        {{ $hostel->name ?? 'All hostels' }} &middot;
                        <span class="badge badge-info">{{ $allocations->total() }}</span>
                        <small>{{ $allocations->total() === 1 ? 'resident' : 'residents' }}</small>
                    </p>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ route('hostel.reports') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left mr-1"></i> Reports
                    </a>
                    <a href="{{ route('hostel.student-list.pdf', $filters) }}" class="btn btn-outline-danger">
                        <i class="fas fa-file-pdf mr-1"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        <div class="card card-outline card-primary mb-3">
            <div class="card-body">
                <form action="{{ route('hostel.student-list') }}" method="GET">
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
                                    '' => 'Current residents',
                                    'active' => 'Active',
                                    'pending' => 'Pending',
                                    'vacated' => 'Vacated',
                                ], $filters['status'] ?? '', ['class' => 'form-control select2', 'style' => 'width: 100%']) !!}
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="form-group mb-0">
                                <label>Academic Year</label>
                                {!! Form::select('academic_year_id', ['' => 'All Years'] + $academicYears, $filters['academic_year_id'] ?? '', ['class' => 'form-control select2', 'style' => 'width: 100%']) !!}
                            </div>
                        </div>
                        <div class="col-md-9 mb-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Apply Filters</button>
                            <a href="{{ route('hostel.student-list') }}" class="btn btn-default">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Admission No</th>
                                <th>Student</th>
                                <th>Gender</th>
                                <th>Class / Stream</th>
                                <th>Hostel</th>
                                <th>Room</th>
                                <th>Bed</th>
                                <th>Allocated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allocations as $allocation)
                                <tr class="{{ $allocation->status !== 'active' ? 'text-muted' : '' }}">
                                    <td>{{ optional($allocation->student)->admission_no ?? 'N/A' }}</td>
                                    <td>
                                        <strong>{{ optional($allocation->student)->first_name ?? 'Unknown' }} {{ optional($allocation->student)->last_name ?? '' }}</strong>
                                    </td>
                                    <td>{{ optional($allocation->student)->gender ? ucfirst($allocation->student->gender) : 'N/A' }}</td>
                                    <td>{{ $allocation->class_info }}</td>
                                    <td>{{ optional($allocation->hostel)->name ?? 'N/A' }}</td>
                                    <td>{{ optional($allocation->room)->room_number ?? 'N/A' }}</td>
                                    <td>{{ $allocation->bed_number ?? '—' }}</td>
                                    <td>{{ optional($allocation->allocation_date)->format('d M, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-users-slash fa-2x text-muted mb-2 d-block"></i>
                                        <strong>No residents match these filters</strong>
                                        <p class="text-muted small mb-0">Widen the class, stream or hostel selection, or reset the filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($allocations->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-left text-muted small pt-2">
                        Showing {{ $allocations->firstItem() }}–{{ $allocations->lastItem() }} of {{ $allocations->total() }}
                    </div>
                    <div class="float-right">
                        @include('adminlte-templates::common.paginate', ['records' => $allocations])
                    </div>
                </div>
            @endif
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
