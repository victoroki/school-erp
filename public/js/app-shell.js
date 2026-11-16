/**
 * App shell behaviour shared by every staff page.
 *
 * Plain script, loaded with `defer` after jQuery, Bootstrap and AdminLTE
 * (classic scripts): by the time this runs they are on window and the DOM
 * is parsed. The sidebar is driven here, not by AdminLTE's treeview.
 */
(function () {
    'use strict';

    var $ = window.jQuery;

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

    /* ── Sidebar: show / hide ─────────────────────────────────────── */

    var body = document.body;
    var sidebar = document.getElementById('app-sidebar');
    var desktop = window.matchMedia('(min-width: 992px)');

    function setHidden(hidden) {
        body.classList.toggle('sidebar-hidden', hidden);
        try { localStorage.setItem('sidebar-hidden', hidden ? '1' : '0'); } catch (e) {}
    }

    function setOpen(open) {
        body.classList.toggle('sidebar-open', open);
    }

    document.querySelectorAll('[data-sidebar-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (desktop.matches) {
                setHidden(!body.classList.contains('sidebar-hidden'));
            } else {
                setOpen(!body.classList.contains('sidebar-open'));
            }
        });
    });

    document.querySelectorAll('[data-sidebar-close]').forEach(function (el) {
        el.addEventListener('click', function () { setOpen(false); });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && body.classList.contains('sidebar-open')) {
            setOpen(false);
        }
    });

    /* ── Sidebar: menu groups expand and collapse ─────────────────── */

    // One click handler for every group: no AdminLTE treeview, nothing else
    // can swallow the click. No height animation: this is used all day.
    if (sidebar) {
        sidebar.addEventListener('click', function (e) {
            var toggle = e.target.closest('.app-nav-toggle');
            if (!toggle) {
                return;
            }
            var group = toggle.parentElement;
            var open = !group.classList.contains('is-open');
            group.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    /* ── Sidebar quick find ───────────────────────────────────────── */

    var input = sidebar && sidebar.querySelector('.app-search-input');
    var nav = sidebar && sidebar.querySelector('.app-nav');
    var scroller = sidebar && sidebar.querySelector('.app-nav-scroll');

    if (input && nav) {
        var empty = document.createElement('li');
        empty.className = 'app-nav-empty';
        empty.setAttribute('role', 'status');
        empty.hidden = true;
        empty.textContent = 'No matching pages';
        nav.appendChild(empty);

        var openedBySearch = [];

        function labelOf(el) {
            var label = el.querySelector(':scope > .app-nav-link .app-nav-label');
            return label ? label.textContent.replace(/\s+/g, ' ').trim().toLowerCase() : '';
        }

        function reset() {
            nav.querySelectorAll('.is-search-hidden').forEach(function (el) {
                el.classList.remove('is-search-hidden');
            });
            openedBySearch.forEach(function (group) {
                group.classList.remove('is-open');
                group.querySelector(':scope > .app-nav-toggle').setAttribute('aria-expanded', 'false');
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

            Array.prototype.forEach.call(nav.children, function (li) {
                if (li === empty) {
                    return;
                }
                if (li.classList.contains('app-nav-header')) {
                    li.classList.add('is-search-hidden');
                    return;
                }

                var parentHit = labelOf(li).indexOf(term) !== -1;
                var children = li.querySelectorAll('.app-nav-sub > li');
                var childHit = false;

                children.forEach(function (child) {
                    if (child.classList.contains('app-nav-subheader')) {
                        child.classList.add('is-search-hidden');
                        return;
                    }
                    var hit = parentHit || labelOf(child).indexOf(term) !== -1;
                    child.classList.toggle('is-search-hidden', !hit);
                    childHit = childHit || hit;
                });

                var visible = parentHit || childHit;
                li.classList.toggle('is-search-hidden', !visible);

                if (visible && children.length && !li.classList.contains('is-open')) {
                    li.classList.add('is-open');
                    li.querySelector(':scope > .app-nav-toggle').setAttribute('aria-expanded', 'true');
                    openedBySearch.push(li);
                }
                any = any || visible;
            });

            empty.hidden = any;
            if (scroller) {
                scroller.scrollTop = 0;
            }
        }

        function firstMatch() {
            var links = nav.querySelectorAll('a.app-nav-link[href]');
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
            } else if (e.key === 'Enter' && input.value.trim()) {
                var link = firstMatch();
                if (link) {
                    e.preventDefault();
                    window.location.href = link.href;
                }
            }
        });

        // "/" or Ctrl/Cmd+K focuses the finder from anywhere that is not a field.
        document.addEventListener('keydown', function (e) {
            var t = e.target;
            var typing = t && (t.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName));
            var combo = (e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K');

            if (combo || (e.key === '/' && !typing)) {
                e.preventDefault();
                if (desktop.matches) {
                    if (body.classList.contains('sidebar-hidden')) setHidden(false);
                } else {
                    setOpen(true);
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

    var active = scroller && scroller.querySelector('.app-nav-link.is-active');
    if (active) {
        var top = active.getBoundingClientRect().top - scroller.getBoundingClientRect().top;
        if (top > scroller.clientHeight - 48 || top < 0) {
            scroller.scrollTop += top - scroller.clientHeight / 3;
        }
    }
})();
