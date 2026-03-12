/**
 * SIDEBAR TREEVIEW & RESPONSIVE HANDLER
 * - Desktop: hamburger toggles icon-only collapsed mode
 * - Mobile: hamburger slides sidebar in/out as overlay
 */

function initSidebarHandler() {
    (function ($) {
        'use strict';

        // ─── 1. Copy data-tooltip from nav-link to parent nav-item for CSS tooltips ───
        // The menu HTML already has data-tooltip on .nav-link; we copy it to .nav-item
        // so our CSS ::after pseudo-element can display it in collapsed mode.
        function attachTooltips() {
            $('.nav-sidebar .nav-item').each(function () {
                // Prefer the data-tooltip already on the link, fallback to <p> text
                var $link = $(this).find('> .nav-link');
                var label = $link.attr('data-tooltip') || $link.find('> p').text().trim();
                if (label) {
                    $(this).attr('data-tooltip', label);
                }
            });
        }
        attachTooltips();

        // ─── 2. Treeview Toggle (accordion expand/collapse) ───────────────────────
        $(document).off('click.treeview', '.has-treeview > a')
            .on('click.treeview', '.has-treeview > a', function (e) {
                var $link = $(this);
                var $parent = $link.parent('.has-treeview');
                var $submenu = $parent.find('> .nav-treeview');

                // Only intercept if not in collapsed mode on desktop
                if ($submenu.length > 0 && !($('body').hasClass('sidebar-collapse') && $(window).width() > 991)) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Accordion: close siblings
                    $parent.siblings('.menu-open').each(function () {
                        $(this).removeClass('menu-open');
                        $(this).find('> .nav-treeview').slideUp(220);
                    });

                    // Toggle current
                    if ($parent.hasClass('menu-open')) {
                        $submenu.slideUp(220, function () {
                            $parent.removeClass('menu-open');
                        });
                    } else {
                        $parent.addClass('menu-is-opening');
                        $submenu.slideDown(220, function () {
                            $parent.addClass('menu-open').removeClass('menu-is-opening');
                        });
                    }
                }
            });

        // ─── 3. Open active menu on page load ────────────────────────────────────
        $('.nav-sidebar .nav-link.active')
            .parents('.has-treeview')
            .addClass('menu-open')
            .find('> .nav-treeview')
            .show();

        // ─── 4. Hamburger / PushMenu Logic ───────────────────────────────────────
        // Disable AdminLTE's own pushmenu to prevent double-firing
        $(document).off('click', '[data-widget="pushmenu"]');

        $(document).on('click.pushmenu', '[data-widget="pushmenu"]', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var isMobile = $(window).width() <= 991;

            if (isMobile) {
                // Mobile: slide sidebar in/out as overlay - instant toggle
                var isOpen = $('body').hasClass('sidebar-open');

                if (isOpen) {
                    $('body').removeClass('sidebar-open');
                    $('.sidebar-overlay').remove();
                } else {
                    $('body').addClass('sidebar-open');
                    if ($('.sidebar-overlay').length === 0) {
                        $('<div class="sidebar-overlay"></div>').appendTo('body');
                    }
                }
            } else {
                // Desktop: toggle icon-only collapsed mode - instant
                $('body').toggleClass('sidebar-collapse');

                // Persist preference in localStorage
                try {
                    localStorage.setItem('sidebarCollapsed', $('body').hasClass('sidebar-collapse') ? '1' : '0');
                } catch (err) { }
            }
        });

        // ─── 5. Close sidebar on mobile when clicking overlay ────────────────────
        $(document).on('click.overlay', '.sidebar-overlay', function () {
            $('body').removeClass('sidebar-open');
            $(this).remove();
        });

        // ─── 6. Close sidebar on mobile when clicking a leaf nav link ────────────
        $(document).on('click.mobilenav', '.nav-sidebar .nav-link:not(.has-treeview > a)', function () {
            if ($(window).width() <= 991) {
                $('body').removeClass('sidebar-open');
                $('.sidebar-overlay').remove();
            }
        });

        // ─── 7. Restore collapsed state on desktop from localStorage ─────────────
        try {
            if ($(window).width() > 991 && localStorage.getItem('sidebarCollapsed') === '1') {
                $('body').addClass('sidebar-collapse');
            }
        } catch (err) { }

        // ─── 8. Handle window resize ──────────────────────────────────────────────
        $(window).on('resize.sidebar', function () {
            if ($(window).width() > 991) {
                // Switched to desktop: clean up mobile overlay state
                $('body').removeClass('sidebar-open');
                $('.sidebar-overlay').remove();
            } else {
                // Switched to mobile: remove desktop collapse so sidebar shows full width
                // (we keep the class but override via CSS media query)
            }
        });

    })(window.jQuery);
}

// Bootstrap: wait for jQuery if not yet available
if (window.jQuery) {
    initSidebarHandler();
} else {
    var _sbCheckCount = 0;
    var _sbInterval = setInterval(function () {
        _sbCheckCount++;
        if (window.jQuery) {
            clearInterval(_sbInterval);
            initSidebarHandler();
        }
        if (_sbCheckCount > 100) clearInterval(_sbInterval);
    }, 50);
}