/**
 * App shell behaviour shared by every staff page.
 *
 * Plain script, loaded with `defer` after the Vite bundle (an ES module, so
 * also deferred): by the time this runs, jQuery, Bootstrap and AdminLTE are
 * on window and the DOM is parsed. No polling needed.
 */
(function () {
    'use strict';

    var $ = window.jQuery;

    /* ── AdminLTE widgets ─────────────────────────────────────────── */

    if ($ && $.fn.PushMenu) {
        $('[data-widget="pushmenu"]').PushMenu();
        $('[data-widget="treeview"]').Treeview();
    }

    /* ── Searchable selects ───────────────────────────────────────── */

    function initSelect2() {
        if (!$ || !$.fn.select2) {
            return false;
        }

        // `:not([data-select2])` keeps a view that brings its own options
        // from being initialised twice.
        $('.select2:not([data-select2])').each(function () {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({ theme: 'bootstrap4', width: '100%' });
            }
        });

        // Views that fill a Select2 after load (cascading dropdowns) wait for
        // this event instead of each re-initialising the field.
        $(document).trigger('select2:ready');

        return true;
    }

    // select2.min.js is a deferred script listed after this one in some
    // layouts and before it in others; cover both orders.
    if (!initSelect2()) {
        window.addEventListener('load', initSelect2, { once: true });
    }

    if ($ && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip({ container: 'body' });
    }

    /* ── Sidebar quick find ───────────────────────────────────────── */

    var sidebar = document.querySelector('.main-sidebar');
    var input = sidebar && sidebar.querySelector('.search-input');
    var nav = sidebar && sidebar.querySelector('.nav-sidebar');

    if (input && nav) {
        var empty = document.createElement('li');
        empty.className = 'sidebar-search-empty';
        empty.setAttribute('role', 'status');
        empty.hidden = true;
        empty.textContent = 'No matching pages';
        nav.appendChild(empty);

        var openedBySearch = [];

        function text(el) {
            var p = el.querySelector(':scope > .nav-link p');
            return p ? p.textContent.replace(/\s+/g, ' ').trim().toLowerCase() : '';
        }

        function reset() {
            nav.querySelectorAll('.is-search-hidden, .search-hit').forEach(function (el) {
                el.classList.remove('is-search-hidden', 'search-hit');
            });
            openedBySearch.forEach(function (li) {
                li.classList.remove('menu-open');
            });
            openedBySearch = [];
            empty.hidden = true;
        }

        function filter(term) {
            reset();
            if (!term) {
                return;
            }

            var any = false;
            var topLevel = nav.querySelectorAll(':scope > li');

            topLevel.forEach(function (li) {
                if (li.classList.contains('nav-header')) {
                    li.classList.add('is-search-hidden');
                    return;
                }
                if (li === empty) {
                    return;
                }

                var parentMatch = text(li).indexOf(term) !== -1;
                var children = li.querySelectorAll('.nav-treeview > li');
                var childMatch = false;

                children.forEach(function (child) {
                    if (child.classList.contains('nav-header')) {
                        child.classList.add('is-search-hidden');
                        return;
                    }
                    var hit = parentMatch || text(child).indexOf(term) !== -1;
                    child.classList.toggle('is-search-hidden', !hit);
                    child.classList.toggle('search-hit', hit && !parentMatch);
                    childMatch = childMatch || hit;
                });

                var visible = parentMatch || childMatch;
                li.classList.toggle('is-search-hidden', !visible);

                if (visible && children.length && !li.classList.contains('menu-open')) {
                    li.classList.add('menu-open');
                    openedBySearch.push(li);
                }
                any = any || visible;
            });

            empty.hidden = any;

            // The menu may be scrolled down to the current page; results start
            // at the top, so bring them into view.
            var list = sidebar.querySelector('.sidebar');
            if (list) {
                list.scrollTop = 0;
            }
        }

        function firstMatch() {
            var links = nav.querySelectorAll('li:not(.is-search-hidden) > .nav-link[href]:not([href="#"])');
            for (var i = 0; i < links.length; i++) {
                if (!links[i].closest('.is-search-hidden')) {
                    return links[i];
                }
            }
            return null;
        }

        input.addEventListener('input', function () {
            filter(input.value.toLowerCase().trim());
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                input.value = '';
                reset();
                input.blur();
            } else if (e.key === 'Enter') {
                var link = firstMatch();
                if (link && input.value.trim()) {
                    e.preventDefault();
                    window.location.href = link.href;
                }
            }
        });

        // "/" or Ctrl/Cmd+K focuses the finder from anywhere that isn't a field.
        document.addEventListener('keydown', function (e) {
            var target = e.target;
            var typing = target && (target.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(target.tagName));
            var combo = (e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K');

            if (combo || (e.key === '/' && !typing)) {
                e.preventDefault();
                if (document.body.classList.contains('sidebar-collapse') && $ && $.fn.PushMenu) {
                    $('[data-widget="pushmenu"]').PushMenu('expand');
                }
                input.focus();
                input.select();
            }
        });
    }

    /* ── Empty tables say so ──────────────────────────────────────── */

    // Generated list views render only a header row when there is no data.
    // Give them a real empty state that points at the page's add action.
    document.querySelectorAll('.content-wrapper table.table').forEach(function (table) {
        var body = table.tBodies[0];
        // Skip tables a script owns: line-item editors inside forms, bodies
        // with an id (filled by fetch), DataTables.
        if (!body || body.rows.length || body.id || table.closest('form') ||
            table.classList.contains('dataTable') || table.closest('[data-no-empty-state]')) {
            return;
        }

        var head = table.tHead && table.tHead.rows[0];
        var columns = head ? head.cells.length : 1;
        var page = table.closest('.content-wrapper');
        var add = page && page.querySelector('.content-header a.btn-primary, .content-header a.btn[href$="/create"]');

        var row = body.insertRow();
        row.className = 'table-empty-row';
        var cell = row.insertCell();
        cell.colSpan = columns;

        var title = document.createElement('strong');
        title.textContent = 'Nothing here yet';
        cell.appendChild(title);

        var hint = document.createElement('span');
        hint.textContent = add
            ? 'Records you add will be listed here.'
            : 'Records will be listed here once they exist.';
        cell.appendChild(hint);

        if (add) {
            var link = document.createElement('a');
            link.href = add.href;
            link.className = 'btn btn-sm btn-primary';
            link.textContent = add.textContent.trim() || 'Add new';
            cell.appendChild(link);
        }

        // If a script fills the table after all, step aside.
        if (window.MutationObserver) {
            new MutationObserver(function (changes, observer) {
                if (body.rows.length > 1 && row.parentNode) {
                    row.remove();
                    observer.disconnect();
                }
            }).observe(body, { childList: true });
        }
    });

    /* ── Keep the current page visible in a long menu ─────────────── */

    var scroller = sidebar && sidebar.querySelector('.sidebar');
    var active = scroller && (scroller.querySelector('.nav-treeview .nav-link.active') || scroller.querySelector('.nav-link.active'));

    if (active && scroller) {
        var top = active.getBoundingClientRect().top - scroller.getBoundingClientRect().top;
        if (top > scroller.clientHeight - 48 || top < 0) {
            scroller.scrollTop += top - scroller.clientHeight / 3;
        }
    }
})();
