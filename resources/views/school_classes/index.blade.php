@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>School Classes</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('school-classes.create') }}">
                        Add Class
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row">
            @forelse($schoolClasses as $schoolClass)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-body d-flex flex-column p-4">
                            <h4 class="card-title font-weight-bold text-dark mb-1" style="font-size: 1.25rem;">{{ $schoolClass->name }}</h4>

                            @if($schoolClass->numeric_value)
                                <p class="text-muted small font-weight-bold text-uppercase tracking-wide mb-3">Level {{ $schoolClass->numeric_value }}</p>
                            @endif

                            @if($schoolClass->description)
                                <p class="text-secondary" style="font-size: 0.9rem; line-height: 1.4;">
                                    {{ \Illuminate\Support\Str::limit($schoolClass->description, 100) }}
                                </p>
                            @else
                                <p class="text-muted italic" style="font-size: 0.85rem;">No description provided.</p>
                            @endif

                            <div class="mb-4 mt-auto">
                                <span class="badge" style="background-color: #e0f2fe; color: #0284c7; padding: 0.4rem 0.6rem; border-radius: 6px; font-weight: 600;">
                                    <i class="fas fa-layer-group mr-1"></i> {{ $schoolClass->sections_count ?? 0 }} Sections
                                </span>
                                <span class="badge" style="background-color: #f1f5f9; color: #475569; padding: 0.4rem 0.6rem; border-radius: 6px; font-weight: 600; margin-left: 0.3rem;">
                                    <i class="fas fa-book mr-1"></i> {{ $schoolClass->class_subjects_count ?? 0 }} Subjects
                                </span>
                            </div>

                            @can('academics.settings.manage')
                            <div class="class-sections-block mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-uppercase font-weight-bold text-muted" style="font-size: 0.7rem; letter-spacing: 0.04em;">Sections</span>
                                    <button type="button" class="btn btn-xs btn-outline-info" onclick="toggleClassSections({{ $schoolClass->class_id }})" style="font-weight: 600;">
                                        <i class="fas fa-folder-plus mr-1"></i> Manage
                                    </button>
                                </div>

                                <div id="classSections-{{ $schoolClass->class_id }}" class="class-sections-list rounded" style="background-color: #f8fafc; padding: 0.75rem; border: 1px solid #f1f5f9;">
                                    @forelse($schoolClass->sections as $section)
                                        <div class="d-flex justify-content-between align-items-center py-1">
                                            <span class="text-dark" style="font-size: 0.85rem;">
                                                <i class="fas fa-tag text-muted mr-1" style="font-size: 0.7rem;"></i>
                                                <strong>{{ $section->name }}</strong>
                                                @if($section->capacity)
                                                    <small class="text-muted">(cap {{ $section->capacity }})</small>
                                                @endif
                                            </span>
                                            <span class="d-inline-flex" style="gap: 4px;">
                                                <a href="{{ route('sections.edit', $section->section_id) }}" class="btn btn-xs btn-light" title="Edit" style="padding: 0.15rem 0.4rem; border-color: #e2e8f0;">
                                                    <i class="fas fa-edit text-primary" style="font-size: 0.7rem;"></i>
                                                </a>
                                                {!! Form::open(['route' => ['sections.destroy', $section->section_id], 'method' => 'delete', 'class' => 'm-0']) !!}
                                                    <button type="submit" class="btn btn-xs btn-light" title="Delete" onclick="return confirm('Delete this section?')" style="padding: 0.15rem 0.4rem; border-color: #e2e8f0;">
                                                        <i class="fas fa-trash-alt text-danger" style="font-size: 0.7rem;"></i>
                                                    </button>
                                                {!! Form::close() !!}
                                            </span>
                                        </div>
                                    @empty
                                        <p class="text-muted mb-1" style="font-size: 0.8rem;">No sections yet. Add one below.</p>
                                    @endforelse

                                    <hr style="border-color: #e2e8f0; margin: 0.5rem 0;">
                                    {!! Form::open(['route' => 'sections.store', 'class' => 'mt-1']) !!}
                                        <input type="hidden" name="class_id" value="{{ $schoolClass->class_id }}">
                                        <div class="d-flex mb-1" style="gap: 6px;">
                                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Name (e.g. A)" required style="border-color: #e2e8f0;">
                                            <input type="number" name="capacity" class="form-control form-control-sm" placeholder="Cap" min="1" style="border-color: #e2e8f0; max-width: 76px;">
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-block text-white" style="background-color: #0284c7; font-weight: 600;">
                                            <i class="fas fa-plus mr-1"></i> Add Section
                                        </button>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                            @endcan

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top" style="border-top-color: #f1f5f9 !important;">
                                <div class="d-flex" style="gap: 0.5rem;">
                                    <a href="{{ route('school-classes.show', $schoolClass->class_id) }}"
                                       class="btn btn-sm" style="background-color: #f1f5f9; color: #475569; border-radius: 6px; font-weight: 600;">
                                        View
                                    </a>
                                    <a href="{{ route('school-classes.edit', $schoolClass->class_id) }}"
                                       class="btn btn-sm" style="background-color: #eff6ff; color: #2563eb; border-radius: 6px; font-weight: 600;">
                                        Edit
                                    </a>
                                </div>
                                <div>
                                    {!! Form::open(['route' => ['school-classes.destroy', $schoolClass->class_id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                    {!! Form::button('<i class="fas fa-trash-alt"></i>', [
                                        'type' => 'submit',
                                        'class' => 'btn btn-sm',
                                        'style' => 'background-color: #fef2f2; color: #ef4444; border-radius: 6px;',
                                        'onclick' => "return confirm('Are you sure you want to delete this class?')"
                                    ]) !!}
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center text-muted">
                            No classes have been created yet.
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if($schoolClasses instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-3 d-flex justify-content-end">
                @include('adminlte-templates::common.paginate', ['records' => $schoolClasses])
            </div>
        @endif
    </div>
@endsection

@push('page_scripts')
<script>
    function toggleClassSections(classId) {
        var el = document.getElementById('classSections-' + classId);
        if (el) {
            var hidden = el.style.display === 'none';
            el.style.display = hidden ? 'block' : 'none';
        }
    }
</script>
@endpush
