@extends('layouts.app')

@section('content')
<div class="cs-page">

    {{-- HEADER --}}
    <div class="cs-page-head">
        <div>
            <div class="cs-title-row">
                <h1 class="dash-heading">Class Subjects</h1>
                @if($currentYear)
                    {{-- The page is scoped to this year, so state it instead of
                         offering a filter that could widen the scope. --}}
                    <span class="cs-year-chip">
                        <i class="far fa-calendar me-1" aria-hidden="true"></i>{{ $currentYear->name }}
                    </span>
                @endif
            </div>
            <p class="dash-sub">
                @if($currentYear)
                    Curriculum for the current academic year, one card per class.
                @else
                    Curriculum assignments, grouped by class.
                @endif
            </p>
        </div>
        @if($currentYear)
            <a class="btn-dash btn-primary-dash" href="{{ route('class-subjects.create') }}">
                <i class="fas fa-plus me-1"></i> Assign Subjects
            </a>
        @endif
    </div>

    @include('flash::message')

    @if(! $currentYear)
        {{-- Without a current year there is no page to show. Say so, instead of
             quietly listing every year at once. --}}
        <div class="cs-empty">
            <div class="cs-empty-icon"><i class="far fa-calendar"></i></div>
            <h2 class="cs-empty-title">No academic year is marked as current</h2>
            <p class="cs-empty-text">
                This page manages the curriculum for the current academic year. Mark a year
                as current to start assigning subjects to classes.
            </p>
            @can('academics.settings.manage')
                <a href="{{ route('academic-years.index') }}" class="btn-dash btn-primary-dash mt-3">
                    <i class="far fa-calendar me-1"></i> Go to Academic Years
                </a>
            @endcan
        </div>
    @else
        {{-- STATS --}}
        <div class="cs-stats" role="list">
            <div class="cs-stat" role="listitem">
                <span class="cs-stat-value">{{ $classSubjects->count() }}</span>
                <span class="cs-stat-label">Classes</span>
            </div>
            <div class="cs-stat" role="listitem">
                <span class="cs-stat-value">{{ $totalAssignments ?? 0 }}</span>
                <span class="cs-stat-label">Subjects assigned</span>
            </div>
            <div class="cs-stat" role="listitem">
                <span class="cs-stat-value">{{ $totalPeriods ?? 0 }}</span>
                <span class="cs-stat-label">Periods / week</span>
            </div>
            <div class="cs-stat" role="listitem">
                <span class="cs-stat-value">{{ $currentYear->name }}</span>
                <span class="cs-stat-label">Academic year</span>
            </div>
        </div>

        @if($classSubjects->isNotEmpty())
            {{-- CONTROL BAR --}}
            <div class="cs-toolbar">
                <div class="cs-search">
                    <i class="fas fa-search cs-search-icon" aria-hidden="true"></i>
                    <input type="search" id="classSubjectSearch" class="cs-search-input"
                           placeholder="Search classes or subjects&hellip;"
                           aria-label="Search classes or subjects"
                           aria-describedby="classSubjectResultCount" autocomplete="off">
                    <button type="button" id="classSubjectSearchClear" class="cs-search-clear" hidden
                            aria-label="Clear search">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <div class="cs-toggle-group" role="group" aria-label="Expand or collapse all cards">
                    <button type="button" id="btnExpandAll" class="btn-dash btn-ghost" aria-pressed="false">
                        <i class="fas fa-expand-arrows-alt me-1"></i> Expand
                    </button>
                    <button type="button" id="btnCollapseAll" class="btn-dash btn-ghost" aria-pressed="true">
                        <i class="fas fa-compress-arrows-alt me-1"></i> Collapse
                    </button>
                </div>
            </div>

            {{-- LIVE RESULT COUNT --}}
            <p id="classSubjectResultCount" class="cs-result-count" role="status" aria-live="polite"></p>
        @endif

        {{-- GRID --}}
        @include('class_subjects.table')

        @if($classSubjects->isNotEmpty())
            <div id="classSubjectNoResults" class="cs-empty" hidden>
                <div class="cs-empty-icon"><i class="fas fa-search"></i></div>
                <h2 class="cs-empty-title">Nothing matches that search</h2>
                <p class="cs-empty-text">Try a different class or subject name, or reset the filters.</p>
                <button type="button" id="classSubjectReset" class="btn-dash btn-ghost mt-3">
                    <i class="fas fa-rotate-left me-1"></i> Reset filters
                </button>
            </div>
        @endif
    @endif
</div>

<style>
/* ── Class Subjects ── */
.cs-page { padding: 1.25rem 1rem 2.5rem; }

.cs-page-head {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem;
}
.cs-title-row { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.dash-heading { font-size: 1.375rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; margin: 0 0 .125rem; }
.dash-sub { font-size: .813rem; color: #64748b; font-weight: 500; margin: 0; }
.cs-year-chip {
    display: inline-flex; align-items: center;
    background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;
    border-radius: 999px; padding: .125rem .5625rem;
    font-size: .6875rem; font-weight: 700; line-height: 1.6;
}

/* Stats */
.cs-stats {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: .75rem; margin-bottom: 1.25rem;
}
.cs-stat {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
    padding: .875rem 1rem; box-shadow: 0 1px 2px rgba(15, 23, 42, .03);
}
.cs-stat-value {
    display: block; font-size: 1.375rem; font-weight: 800; color: #0f172a;
    letter-spacing: -0.02em; line-height: 1.2;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.cs-stat-value:empty, .cs-stat.is-empty .cs-stat-value { font-size: 1rem; font-weight: 700; color: #94a3b8; }
.cs-stat-label {
    display: block; font-size: .6875rem; font-weight: 700; letter-spacing: .06em;
    text-transform: uppercase; color: #94a3b8; margin-top: .25rem;
}

/* Toolbar */
.cs-toolbar {
    display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
    background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
    padding: .875rem 1rem; margin-bottom: .75rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .03);
}
.cs-search { position: relative; flex: 1 1 16rem; min-width: 12rem; }
.cs-search-icon {
    position: absolute; left: .875rem; top: 50%; transform: translateY(-50%);
    color: #94a3b8; font-size: .8125rem; pointer-events: none;
}
.cs-search-input {
    width: 100%; padding: .5625rem 2.5rem .5625rem 2.375rem;
    border-radius: 10px; border: 1px solid #e2e8f0; background: #f8fafc;
    font-size: .8125rem; color: #0f172a;
    transition: border-color 150ms cubic-bezier(.23, 1, .32, 1), background-color 150ms cubic-bezier(.23, 1, .32, 1), box-shadow 150ms cubic-bezier(.23, 1, .32, 1);
    -webkit-appearance: none; appearance: none;
}
.cs-search-input::-webkit-search-cancel-button { display: none; }
.cs-search-input:focus {
    background: #fff; border-color: #6366f1; outline: none;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, .12);
}
.cs-search-clear {
    position: absolute; right: .5rem; top: 50%; transform: translateY(-50%);
    width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center;
    border: 0; border-radius: 6px; background: transparent; color: #94a3b8;
    font-size: .8125rem; cursor: pointer; padding: 0;
    transition: background-color 150ms ease, color 150ms ease, transform 150ms cubic-bezier(.23, 1, .32, 1);
}
.cs-search-clear:hover { background: #f1f5f9; color: #0f172a; }
.cs-search-clear:active { transform: scale(0.9); }

.cs-toggle-group { display: flex; gap: .375rem; margin-left: auto; }

/* Buttons */
.btn-dash {
    display: inline-flex; align-items: center; justify-content: center;
    padding: .5rem .875rem; border-radius: 8px; font-size: .8125rem; font-weight: 600;
    text-decoration: none !important; cursor: pointer; border: 1px solid transparent;
    transition: background-color 150ms cubic-bezier(.23, 1, .32, 1), color 150ms ease,
                border-color 150ms ease, box-shadow 150ms cubic-bezier(.23, 1, .32, 1),
                transform 150ms cubic-bezier(.23, 1, .32, 1);
}
.btn-dash:active { transform: scale(0.97); }
.btn-primary-dash { background: #4f46e5; color: #fff; border-color: #4f46e5; }
.btn-primary-dash:hover { background: #4338ca; box-shadow: 0 4px 12px rgba(79, 70, 229, .22); }
.btn-ghost { background: #fff; color: #64748b; border-color: #e2e8f0; }
.btn-ghost:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }
.btn-ghost[aria-pressed="true"] { background: #eef2ff; color: #4338ca; border-color: #c7d2fe; }

/* Bootstrap's grid gives .col-* no display rule, but AdminLTE utilities can —
   make the filter's `hidden` attribute authoritative. */
.class-group[hidden] { display: none !important; }

.cs-result-count {
    font-size: .75rem; font-weight: 600; color: #94a3b8;
    margin: 0 0 .875rem; padding-left: .125rem; min-height: 1.125rem;
}

/* Cards */
.cs-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .03);
    transition: box-shadow 200ms cubic-bezier(.23, 1, .32, 1), border-color 200ms cubic-bezier(.23, 1, .32, 1);
}
@media (hover: hover) and (pointer: fine) {
    .cs-card:hover { box-shadow: 0 4px 14px rgba(15, 23, 42, .07); border-color: #cbd5e1; }
}
.cs-disclosure > summary { list-style: none; cursor: pointer; }
.cs-disclosure > summary::-webkit-details-marker { display: none; }
.cs-disclosure > summary::marker { content: ''; }

.cs-header {
    display: flex; align-items: center; gap: .5rem;
    padding: .625rem .75rem .625rem .5rem; border-bottom: 1px solid #f1f5f9;
    transition: background-color 150ms ease;
}
.cs-header:hover { background: #f8fafc; }
.cs-header:focus-visible { outline: 2px solid #6366f1; outline-offset: -2px; }
.cs-disclosure[open] > .cs-header { border-bottom-color: #f1f5f9; }
.cs-chevron {
    flex: none; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center;
    color: #94a3b8; font-size: .6875rem;
    transform: rotate(-90deg);
    transition: transform 200ms cubic-bezier(.23, 1, .32, 1);
}
.cs-disclosure[open] .cs-chevron { transform: rotate(0deg); }
.cs-title {
    flex: 1; min-width: 0;
    font-size: .875rem; font-weight: 700; color: #0f172a;
    line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.cs-header-actions { display: flex; align-items: center; gap: .375rem; flex: none; }

.cs-badge {
    display: inline-flex; align-items: center; border-radius: 6px;
    font-size: .625rem; font-weight: 800; padding: .15rem .4rem; line-height: 1.5;
    white-space: nowrap;
}
.cs-badge-count { background: #eef2ff; color: #4338ca; }
.cs-badge-warn { background: #fffbeb; color: #b45309; }
.cs-badge-archived { background: #f1f5f9; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }

/* Body — no nested scrollbar; the page scrolls, not the card. */
.cs-body { border-top: 0; }
.cs-list { list-style: none; margin: 0; padding: .375rem; }
.cs-hint {
    list-style: none; padding: .75rem .5rem; text-align: center;
    font-size: .75rem; font-weight: 600; color: #94a3b8;
}
.cs-hint-error { color: #b45309; }
.cs-retry {
    border: 0; background: none; padding: 0; font: inherit; font-weight: 800;
    color: #4338ca; text-decoration: underline; cursor: pointer;
}
.cs-item {
    display: flex; align-items: center; justify-content: space-between; gap: .5rem;
    padding: .5rem .5rem .5rem .625rem; border-radius: 8px;
    transition: background-color 150ms ease;
}
@media (hover: hover) and (pointer: fine) {
    .cs-item:hover { background: #f8fafc; }
}
.cs-item.is-archived .cs-name { color: #94a3b8; text-decoration: line-through; text-decoration-color: #cbd5e1; }
.cs-item-main { min-width: 0; }
.cs-name {
    display: block; font-size: .8125rem; font-weight: 600; color: #1e293b; line-height: 1.35;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.cs-item-meta { display: flex; align-items: center; gap: .375rem; margin-top: .1875rem; }
.cs-periods { font-size: .6875rem; font-weight: 600; color: #94a3b8; }
.cs-item-actions { display: flex; align-items: center; gap: .125rem; flex: none; }

/* 30px targets instead of 24px — still compact, but reachable on touch. */
.action-btn {
    width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;
    border-radius: 7px; border: 1px solid transparent; background: transparent;
    color: #64748b; font-size: .75rem; padding: 0; cursor: pointer;
    transition: background-color 150ms ease, color 150ms ease, border-color 150ms ease, transform 150ms cubic-bezier(.23, 1, .32, 1);
}
.action-btn:hover { background: #f1f5f9; color: #0f172a; border-color: #e2e8f0; }
.action-btn:active { transform: scale(0.9); }
.action-btn:focus-visible { outline: 2px solid #6366f1; outline-offset: 1px; }
.btn-delete:hover { background: #fef2f2; color: #dc2626; border-color: #fecaca; }

.cs-footer {
    display: flex; align-items: center; justify-content: space-between; gap: .5rem;
    padding: .5rem .875rem; border-top: 1px solid #f1f5f9; background: #fcfcfd;
    font-size: .6875rem; font-weight: 600; color: #94a3b8;
}

.cs-sr-only, .sr-only {
    position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
    overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;
}

/* Empty states */
.cs-empty {
    display: flex; flex-direction: column; align-items: center; text-align: center;
    padding: 3rem 1.5rem; background: #fff; border: 1px dashed #cbd5e1; border-radius: 14px;
}
.cs-empty-icon {
    width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;
    background: #eef2ff; color: #4f46e5; border-radius: 14px; font-size: 1.25rem; margin-bottom: 1rem;
}
.cs-empty-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0 0 .375rem; }
.cs-empty-text { font-size: .8125rem; color: #64748b; margin: 0; max-width: 44ch; }

@media (prefers-reduced-motion: reduce) {
    .cs-page *, .cs-page *::before, .cs-page *::after {
        transition-duration: 1ms !important;
        animation-duration: 1ms !important;
    }
}
</style>
@endsection

@push('page_scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var grid = document.getElementById('class-subjects-grid');

    // Nothing to filter or expand when there are no classes to show.
    if (!grid) { return; }

    var search    = document.getElementById('classSubjectSearch');
    var clearBtn  = document.getElementById('classSubjectSearchClear');
    var status    = document.getElementById('classSubjectResultCount');
    var noResults = document.getElementById('classSubjectNoResults');
    var resetBtn  = document.getElementById('classSubjectReset');
    var expandAll = document.getElementById('btnExpandAll');
    var collapseAll = document.getElementById('btnCollapseAll');
    var deleteForm = document.getElementById('classSubjectDeleteForm');
    var clearForm  = document.getElementById('classSubjectClearForm');
    var endpoint  = @json(route('class-subjects.curriculum'));

    var groups  = Array.prototype.slice.call(grid.querySelectorAll('[data-group]'));
    var details = groups.map(function (group) { return group.querySelector('[data-card]'); });

    // One request feeds every card: the year's assignments keyed by class, which
    // is a fraction of the markup it replaces, and it is fetched at most once
    // however many cards get opened.
    var curriculum = null;
    var inflight = null;

    function loadCurriculum() {
        if (curriculum) { return Promise.resolve(curriculum); }
        if (inflight) { return inflight; }

        inflight = fetch(endpoint, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (response) {
                if (!response.ok) { throw new Error('Request failed: ' + response.status); }
                return response.json();
            })
            .then(function (payload) {
                curriculum = payload.classes || {};
                return curriculum;
            })
            .catch(function (error) {
                // Drop the cache so a retry actually asks again.
                curriculum = null;
                throw error;
            })
            .finally(function () { inflight = null; });

        return inflight;
    }

    function hydrate(group) {
        if (!group || group.dataset.hydrated === 'true') { return; }

        loadCurriculum()
            .then(function () {
                group.dataset.hydrated = 'true';
                renderList(group);
            })
            .catch(function () {
                renderError(group);
            });
    }

    function renderList(group) {
        var list = group.querySelector('[data-list]');
        if (!list) { return; }

        list.textContent = '';

        var items = (curriculum && curriculum[group.dataset.classId]) || [];

        if (items.length === 0) {
            list.appendChild(hintRow('No subjects assigned to this class yet.'));
            return;
        }

        items.forEach(function (item) { list.appendChild(subjectRow(item, group)); });
    }

    function renderError(group) {
        var list = group.querySelector('[data-list]');
        if (!list) { return; }

        var row = hintRow('Subjects could not be loaded. ');
        row.className = 'cs-hint cs-hint-error';

        var retry = document.createElement('button');
        retry.type = 'button';
        retry.className = 'cs-retry';
        retry.textContent = 'Retry';
        retry.addEventListener('click', function () { hydrate(group); });

        row.appendChild(retry);
        list.textContent = '';
        list.appendChild(row);
    }

    function hintRow(text) {
        var row = document.createElement('li');
        row.className = 'cs-hint';
        row.textContent = text;
        return row;
    }

    // Built with createElement rather than an HTML string: subject and class
    // names come from the database and must never be parsed as markup.
    function subjectRow(item, group) {
        var row = document.createElement('li');
        row.className = 'cs-item' + (item.archived ? ' is-archived' : '');

        var main = document.createElement('div');
        main.className = 'cs-item-main';

        var name = document.createElement('span');
        name.className = 'cs-name';
        name.textContent = item.name;

        var meta = document.createElement('span');
        meta.className = 'cs-item-meta';

        var periods = document.createElement('span');
        periods.className = 'cs-periods';
        periods.title = 'Periods per week';
        periods.textContent = item.periods + '×/wk';
        meta.appendChild(periods);

        if (item.archived) {
            var badge = document.createElement('span');
            badge.className = 'cs-badge cs-badge-archived';
            badge.title = 'This subject is archived and can no longer be assigned to new classes.';
            badge.textContent = 'Archived';
            meta.appendChild(badge);
        }

        main.appendChild(name);
        main.appendChild(meta);

        var actions = document.createElement('div');
        actions.className = 'cs-item-actions';
        actions.appendChild(iconLink('far fa-eye', 'View details for ' + item.name, item.show_url));
        actions.appendChild(iconLink('far fa-edit', 'Edit ' + item.name + ' in this class', item.edit_url));

        var remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'action-btn btn-delete';
        remove.setAttribute('aria-label', 'Remove ' + item.name + ' from this class');
        remove.title = 'Remove from class';
        remove.appendChild(icon('far fa-trash-alt'));
        remove.addEventListener('click', function () {
            destroyAssignment(item.delete_url, 'Remove ' + item.name + ' from this class?');
        });
        actions.appendChild(remove);

        row.appendChild(main);
        row.appendChild(actions);

        return row;
    }

    function iconLink(iconClass, label, url) {
        var link = document.createElement('a');
        link.className = 'action-btn';
        link.href = url;
        link.setAttribute('aria-label', label);
        link.title = label;
        link.appendChild(icon(iconClass));
        return link;
    }

    function icon(iconClass) {
        var glyph = document.createElement('i');
        glyph.className = iconClass;
        return glyph;
    }

    // The page ships two forms; every delete and clear button routes through
    // them by setting the target just before submitting.
    function destroyAssignment(url, message) {
        if (!deleteForm || !window.confirm(message)) { return; }
        deleteForm.action = url;
        deleteForm.submit();
    }

    function clearClass(button) {
        if (!clearForm || !window.confirm(button.dataset.confirm)) { return; }

        clearForm.querySelector('[name="class_id"]').value = button.dataset.classId;
        clearForm.querySelector('[name="academic_year_id"]').value = button.dataset.yearId;
        clearForm.submit();
    }

    function syncExpandButtons() {
        var open = details.filter(function (card) { return card.open; }).length;
        var everyCard = details.length > 0;

        if (expandAll)   { expandAll.setAttribute('aria-pressed', open === details.length && everyCard ? 'true' : 'false'); }
        if (collapseAll) { collapseAll.setAttribute('aria-pressed', open === 0 ? 'true' : 'false'); }
    }

    function applyFilters() {
        var term = search ? search.value.trim().toLowerCase() : '';
        var shown = 0;
        var shownSubjects = 0;

        if (clearBtn) { clearBtn.hidden = term === ''; }

        groups.forEach(function (group) {
            var visible = term === '' || group.dataset.search.indexOf(term) > -1;

            group.hidden = !visible;
            if (!visible) { return; }

            shown++;
            shownSubjects += Number(group.dataset.subjectCount || 0);

            // A collapsed card hides its subjects, so a hit inside it would be
            // invisible. Open and load whatever the search matched.
            if (term !== '') {
                var card = group.querySelector('[data-card]');
                if (!card.open) { card.open = true; }
                hydrate(group);
            }
        });

        if (noResults) { noResults.hidden = shown !== 0; }
        if (status) {
            status.textContent = shown === groups.length
                ? ''
                : shown + (shown === 1 ? ' class shown' : ' classes shown') + ' · '
                  + shownSubjects + (shownSubjects === 1 ? ' subject' : ' subjects');
        }
        syncExpandButtons();
    }

    function resetFilters() {
        search.value = '';
        applyFilters();
        search.focus();
    }

    grid.addEventListener('click', function (event) {
        // Destructive buttons live inside the summary, so their click has to be
        // stopped before the card toggles open or shut.
        var clear = event.target.closest('[data-clear]');
        if (clear) {
            event.preventDefault();
            clearClass(clear);
            return;
        }

        var summary = event.target.closest('summary');
        if (!summary) { return; }

        // The browser flips `open` after this handler returns.
        window.setTimeout(function () {
            if (summary.parentElement.open) {
                hydrate(summary.parentElement.closest('[data-group]'));
            }
            syncExpandButtons();
        }, 0);
    });

    if (search)   { search.addEventListener('input', applyFilters); }
    if (clearBtn) { clearBtn.addEventListener('click', resetFilters); }
    if (resetBtn) { resetBtn.addEventListener('click', resetFilters); }

    if (expandAll) {
        expandAll.addEventListener('click', function () {
            details.forEach(function (card) { card.open = true; });
            groups.forEach(function (group) { hydrate(group); });
            syncExpandButtons();
        });
    }

    if (collapseAll) {
        collapseAll.addEventListener('click', function () {
            details.forEach(function (card) { card.open = false; });
            syncExpandButtons();
        });
    }

    // "/" focuses search, Escape clears it — muscle memory for dense admin UIs.
    document.addEventListener('keydown', function (event) {
        if (!search) { return; }

        if (event.key === '/' && document.activeElement !== search && !/^(INPUT|SELECT|TEXTAREA)$/.test(document.activeElement.tagName)) {
            event.preventDefault();
            search.focus();
        } else if (event.key === 'Escape' && document.activeElement === search) {
            event.preventDefault();
            resetFilters();
        }
    });

    applyFilters();
});
</script>
@endpush
