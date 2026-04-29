@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">Permissions</h1>
            <p class="dash-sub">Manage system access levels and functional boundaries</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <a class="btn-dash btn-primary-dash" href="{{ route('permissions.create') }}">
                <i class="fas fa-plus me-1"></i> Add New Permission
            </a>
        </div>
    </div>

    @include('flash::message')

    {{-- ② CONTROL BAR --}}
    <div class="dash-panel mb-4">
        <div class="dash-panel-body py-3">
            <div class="row align-items-center">
                <div class="col-md-8 mb-3 mb-md-0">
                    <div class="search-wrap">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="permissionSearch" class="search-input" placeholder="Search by group or permission name...">
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="btn-group gap-1">
                        <button type="button" id="btnExpandAll" class="btn-dash btn-ghost py-1 x-small fw-bold">
                            <i class="fas fa-expand-arrows-alt me-1"></i> Expand All
                        </button>
                        <button type="button" id="btnCollapseAll" class="btn-dash btn-ghost py-1 x-small fw-bold">
                            <i class="fas fa-compress-arrows-alt me-1"></i> Collapse All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ③ PERMISSIONS GRID --}}
    @include('permissions.table')
</div>

<style>
/* ── Emil Kowalski Utility Suite ── */
:root {
    --blue: #3b82f6;
    --indigo: #4f46e5;
    --emerald: #10b981;
    --slate: #64748b;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 1rem; }
.dash-heading { font-size: 1.375rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.813rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
.dash-panel-body { padding: 1rem; }

/* Buttons */
.btn-dash { 
    display: inline-flex; align-items: center; justify-content: center; padding: .4rem .875rem; border-radius: 8px; font-size: .813rem; font-weight: 600; 
    transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer;
}
.btn-primary-dash { background: var(--indigo); color: #fff; border-color: var(--indigo); }
.btn-primary-dash:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
.btn-ghost { background: transparent; color: var(--muted); border-color: var(--border); }
.btn-ghost:hover { background: #f8fafc; color: var(--text); border-color: #cbd5e1; }

.search-wrap { position: relative; width: 100%; }
.search-icon { position: absolute; left: .875rem; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: .813rem; }
.search-input { 
    width: 100%; padding: .5rem .875rem .5rem 2.25rem; border-radius: 10px; border: 1px solid #e2e8f0; 
    background: #f8fafc; font-size: .813rem; transition: all 150ms var(--ease-out);
}
.search-input:focus { background: #fff; border-color: var(--blue); outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }

/* Permission Card Styling */
.pg-card { 
    background: #fff; border: 1px solid #f1f5f9; border-radius: 14px; overflow: hidden; 
    transition: all 200ms var(--ease-out); box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.pg-card:hover { border-color: #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }

.pg-header { 
    padding: .625rem .875rem; background: #fff; border-bottom: 1px solid #f8fafc; 
    display: flex; align-items: center; justify-content: space-between;
}
.pg-title { font-size: .813rem; font-weight: 700; color: #1e293b; text-transform: capitalize; display: flex; align-items: center; gap: .5rem; }
.pg-title i { color: var(--blue); opacity: .8; }

.pg-body { max-height: 400px; overflow-y: auto; padding: .375rem; }
.pg-body::-webkit-scrollbar { width: 4px; }
.pg-body::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

.pi-item { 
    display: flex; flex-direction: column; padding: .5rem .75rem; border-radius: 8px; 
    margin-bottom: .125rem; transition: all 150ms ease; cursor: default;
}
.pi-item:hover { background: #f8fafc; }
.pi-name { font-size: .75rem; font-weight: 600; color: #334155; line-height: 1.2; }
.pi-desc { font-size: .625rem; color: var(--muted); margin-top: .125rem; }

.badge-count { background: #eff6ff; color: #3b82f6; font-size: .625rem; font-weight: 800; padding: .15rem .45rem; border-radius: 6px; }

/* Collapsed state */
.pg-card.collapsed .pg-body { display: none; }
.pg-card.collapsed { height: auto !important; }

/* Alerts */
.dash-alert { display: flex; gap: 1rem; padding: 1.25rem; border-radius: 12px; background: #fff; border: 1px solid var(--border); margin-bottom: 1.5rem; align-items: center; }
.alert-info { border-left: 4px solid var(--blue); }
.da-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #eff6ff; color: var(--blue); border-radius: 10px; font-size: 1.25rem; }
.da-body { flex: 1; }
.da-title { font-size: .938rem; font-weight: 700; color: var(--text); margin: 0; }
.da-desc { font-size: .813rem; color: var(--muted); margin: .125rem 0 0; }
</style>
@endsection

@push('page_scripts')
<script>
    $(document).ready(function() {
        // Search Functionality
        $('#permissionSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();

            $('.permission-group').each(function() {
                var group = $(this);
                var groupName = group.data('group-name');
                var groupMatches = groupName.indexOf(value) > -1;
                var hasVisibleRows = false;

                var rows = group.find('.pi-item');
                
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

        // Expand/Collapse Toggle
        $(document).on('click', '.pg-toggle', function(e) {
            e.preventDefault();
            var card = $(this).closest('.pg-card');
            var icon = $(this).find('i');
            
            if (card.hasClass('collapsed')) {
                card.removeClass('collapsed');
                icon.removeClass('fa-plus').addClass('fa-minus');
            } else {
                card.addClass('collapsed');
                icon.removeClass('fa-minus').addClass('fa-plus');
            }
        });

        // Expand All
        $('#btnExpandAll').on('click', function() {
            $('.pg-card').removeClass('collapsed');
            $('.pg-toggle i').removeClass('fa-plus').addClass('fa-minus');
        });

        // Collapse All
        $('#btnCollapseAll').on('click', function() {
            $('.pg-card').addClass('collapsed');
            $('.pg-toggle i').removeClass('fa-minus').addClass('fa-plus');
        });
    });
</script>
@endpush
