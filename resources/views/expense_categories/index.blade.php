@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">Expense Categories</h1>
            <p class="dash-sub">Manage and organize different categories of expenses</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <a class="btn-dash btn-primary-dash" href="{{ route('expenseCategories.create') }}">
                <i class="fas fa-plus me-1"></i> Add Category
            </a>
        </div>
    </div>

    @include('flash::message')

    {{-- ② CONTENT --}}
    <div class="dash-panel">
        <div class="dash-panel-header">
            <div class="d-flex align-items-center gap-2">
                <div class="icon-wrap bg-indigo-light text-indigo">
                    <i class="fas fa-tags"></i>
                </div>
                <h3 class="dash-panel-title">All Categories</h3>
            </div>
        </div>
        @include('expense_categories.table')
    </div>
</div>

<style>
/* ── Emil Kowalski Utility Suite ── */
:root {
    --blue: #3b82f6; --blue-light: #eff6ff;
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --slate: #64748b;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 1.5rem; }
.dash-heading { font-size: 1.375rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.813rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); overflow: hidden; display: flex; flex-direction: column; }
.dash-panel-header { padding: 1.25rem 1.5rem; background: #fff; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between; }
.dash-panel-title { font-size: .875rem; font-weight: 700; color: var(--text); margin: 0; }
.dash-panel-body { padding: 1.5rem; flex: 1; }

.icon-wrap { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .875rem; }
.bg-indigo-light { background: var(--indigo-light); }
.text-indigo { color: var(--indigo); }

/* Buttons */
.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .5rem 1rem; border-radius: 8px; font-size: .813rem; font-weight: 600; transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer; }
.btn-primary-dash { background: var(--indigo); color: #fff; border-color: var(--indigo); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.btn-primary-dash:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); color: #fff; }
.btn-ghost { background: transparent; color: var(--muted); border-color: transparent; }
.btn-ghost:hover { background: #f1f5f9; color: var(--text); }

/* Table Styling */
.table { margin-bottom: 0; }
.table thead th { background: #f8fafc; border-bottom: 1px solid var(--border); border-top: 0; font-size: .688rem; font-weight: 700; text-transform: uppercase; color: var(--slate); letter-spacing: 0.05em; padding: .75rem 1.5rem; }
.table tbody td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; border-top: 0; }
.table tbody tr:last-child td { border-bottom: 0; }
.table-hover tbody tr:hover { background-color: #f8fafc; }

/* Action Buttons */
.action-btn { width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; color: var(--muted); transition: all 150ms ease; border: 1px solid transparent; background: transparent; font-size: .75rem; padding: 0; }
.action-btn:hover { background: #f1f5f9; color: var(--text); border-color: #e2e8f0; }
.action-btn.btn-view:hover { background: var(--blue-light); color: var(--blue); border-color: #bfdbfe; }
.action-btn.btn-edit:hover { background: var(--amber-light); color: var(--amber); border-color: #fde68a; }
.action-btn.btn-delete:hover { background: var(--rose-light); color: var(--rose); border-color: #fecdd3; }
</style>
@endsection
