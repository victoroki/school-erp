@extends('layouts.app')

@section('content')
<div class="dash-wrap pb-5">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-5">
        <div class="col-md-7">
            <h1 class="dash-heading">Academic Structure</h1>
            <p class="dash-sub text-muted">Organization of classes, sections, and teaching resources</p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <div class="d-flex gap-3 justify-content-md-end align-items-center">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="classSectionSearch" placeholder="Filter architecture...">
                </div>
                <a class="btn-dash btn-primary-dash" href="{{ route('class-sections.create') }}">
                    <i class="fas fa-plus me-2"></i> New Section
                </a>
            </div>
        </div>
    </div>

    @include('flash::message')

    {{-- ② GRID CONTENT --}}
    <div class="dash-content">
        @include('class_sections.table')
    </div>
</div>

<style>
/* ── Emil Kowalski Utility Suite ── */
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --slate: #64748b; --slate-light: #f1f5f9;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 2.5rem; background: #fafafa; min-height: 100vh; }
.dash-heading { font-size: 1.75rem; font-weight: 800; color: var(--text); letter-spacing: -0.04em; margin-bottom: 0.25rem; }
.dash-sub { font-size: 0.938rem; font-weight: 500; }

/* Search Box */
.search-box { position: relative; width: 260px; }
.search-box i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--slate); font-size: 0.813rem; }
.search-box input { width: 100%; padding: 0.625rem 1rem 0.625rem 2.5rem; border-radius: 12px; border: 1px solid var(--border); font-size: 0.813rem; transition: all 200ms var(--ease-out); background: #fff; }
.search-box input:focus { border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); outline: none; }

/* Buttons */
.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .75rem 1.5rem; border-radius: 12px; font-size: .813rem; font-weight: 700; transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer; }
.btn-primary-dash { background: var(--indigo); color: #fff; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
.btn-primary-dash:hover { background: #4338ca; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.3); color: #fff; }

.gap-3 { gap: 1rem; }
</style>
@endsection

@push('page_scripts')
<script>
    $(document).ready(function() {
        $('#classSectionSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('.class-group').each(function() {
                var group = $(this);
                var groupName = group.data('group-name');
                if (groupName.indexOf(value) > -1) {
                    group.show();
                } else {
                    group.hide();
                }
            });
        });
    });
</script>
@endpush
