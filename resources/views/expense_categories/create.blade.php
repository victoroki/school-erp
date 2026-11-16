@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('expenseCategories.index') }}" class="btn-dash btn-ghost px-3 py-2">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="dash-heading">New Expense Category</h1>
                    <p class="dash-sub">Create a new category to organize school expenses</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            @include('adminlte-templates::common.errors')

            <div class="dash-panel">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-wrap bg-indigo-light text-indigo">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3 class="dash-panel-title">Category Details</h3>
                    </div>
                </div>

                {!! Form::open(['route' => 'expenseCategories.store']) !!}
                <div class="dash-panel-body">
                    <div class="row m-0">
                        @include('expense_categories.fields')
                    </div>
                </div>

                <div class="dash-panel-footer p-3 bg-slate-light border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('expenseCategories.index') }}" class="btn-dash btn-ghost"> Cancel </a>
                    <button type="submit" class="btn-dash btn-indigo-dash px-4">
                        <i class="fas fa-save me-2"></i> Save Category
                    </button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<style>
/* ── Emil Kowalski Utility Suite ── */
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 1.5rem; }
.dash-heading { font-size: 1.375rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.813rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; }
.dash-panel-header { padding: 1.25rem 1.5rem; background: #fff; border-bottom: 1px solid #f8fafc; }
.dash-panel-title { font-size: .875rem; font-weight: 700; color: var(--text); margin: 0; }
.dash-panel-body { padding: 1.5rem; }

.icon-wrap { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .875rem; }
.bg-indigo-light { background: var(--indigo-light); }
.text-indigo { color: var(--indigo); }
.bg-slate-light { background: #f8fafc; }

/* Buttons */
.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .5rem 1rem; border-radius: 8px; font-size: .813rem; font-weight: 600; transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer; }
.btn-indigo-dash { background: var(--indigo); color: #fff; }
.btn-indigo-dash:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); color: #fff; }
.btn-ghost { background: transparent; color: var(--muted); border-color: var(--border); }
.btn-ghost:hover { background: #f1f5f9; color: var(--text); border-color: #cbd5e1; }
</style>
@endsection
