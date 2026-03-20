/**
 * SIDEBAR TREEVIEW & RESPONSIVE HANDLER
 * - Desktop : hamburger toggles icon-only collapsed mode (body.sidebar-collapse)
 * - Mobile  : hamburger slides sidebar in as an overlay drawer (body.sidebar-open)
 *             CSS handles the actual transform; JS just toggles the class.
 */

function initSidebarHandler() {
    (function ($) {
        'use strict';

        var MOBILE_BP = 991; // px — matches CSS breakpoint

        function isMobile() {
            return $(window).width() <= MOBILE_BP;
        }

        // ─── 1. Attach tooltip text to parent nav-item ──────────────────────────
        // CSS ::after uses data-tooltip on .nav-item for the collapsed tooltip.
        function attachTooltips() {
            $('.nav-sidebar .nav-item').each(function () {
                var $link = $(this).find('> .nav-link');
                var label = $link.attr('data-tooltip') || $link.find('> p').text().trim();
                if (label) {
                    $(this).attr('data-tooltip', label);
                }
            });
        }
        attachTooltips();

        // ─── 2. Treeview accordion (expand / collapse submenus) ────────────────
        $(document).off('click.treeview', '.has-treeview > a')
            .on('click.treeview', '.has-treeview > a', function (e) {
                var $link   = $(this);
                var $parent = $link.parent('.has-treeview');
                var $sub    = $parent.find('> .nav-treeview');

                // In desktop collapsed mode the flyout is CSS-only (hover).
                // Don't intercept clicks — let the link navigate normally.
                if (!isMobile() && $('body').hasClass('sidebar-collapse')) {
                    return;
                }

                if ($sub.length === 0) { return; }

                e.preventDefault();
                e.stopPropagation();

                // Accordion: close open siblings first
                $parent.siblings('.menu-open').each(function () {
                    $(this).removeClass('menu-open');
                    $(this).find('> .nav-treeview').slideUp(220);
                });

                // Toggle current item
                if ($parent.hasClass('menu-open')) {
                    $sub.slideUp(220, function () {
                        $parent.removeClass('menu-open');
                    });
                } else {
                    $parent.addClass('menu-is-opening');
                    $sub.slideDown(220, function () {
                        $parent.addClass('menu-open').removeClass('menu-is-opening');
                    });
                }
            });

        // ─── 3. Open active menu on page load ──────────────────────────────────
        $('.nav-sidebar .nav-link.active')
            .parents('.has-treeview')
            .addClass('menu-open')
            .find('> .nav-treeview')
            .show();

        // ─── 4. Hamburger / PushMenu ────────────────────────────────────────────
        // Disable AdminLTE's built-in handler so we have full control.
        $(document).off('click', '[data-widget="pushmenu"]');

        $(document).on('click.pushmenu', '[data-widget="pushmenu"]', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            if (isMobile()) {
                // ── Mobile: toggle body.sidebar-open; CSS does the transform ──
                var isOpen = $('body').hasClass('sidebar-open');

                if (isOpen) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            } else {
                // ── Desktop: toggle icon-only collapsed mode ──
                $('body').toggleClass('sidebar-collapse');

                // Persist preference
                try {
                    localStorage.setItem(
                        'sidebarCollapsed',
                        $('body').hasClass('sidebar-collapse') ? '1' : '0'
                    );
                } catch (_) {}
            }
        });

        // ─── helpers ────────────────────────────────────────────────────────────
        function openMobileSidebar() {
            // Inject overlay if it doesn't exist yet
            if ($('.sidebar-overlay').length === 0) {
                $('<div class="sidebar-overlay"></div>').appendTo('body');
            }
            $('body').addClass('sidebar-open');
        }

        function closeMobileSidebar() {
            $('body').removeClass('sidebar-open');
            // Give the CSS slide-out transition (0.3 s) time to finish
            setTimeout(function () {
                $('.sidebar-overlay').remove();
            }, 320);
        }

        // ─── 5. Close mobile sidebar when overlay is clicked ──────────────────
        $(document).on('click.overlay', '.sidebar-overlay', function () {
            closeMobileSidebar();
        });

        // ─── 6. Close mobile sidebar on Escape key ────────────────────────────
        $(document).on('keydown.sidebar', function (e) {
            if (e.key === 'Escape' && $('body').hasClass('sidebar-open')) {
                closeMobileSidebar();
            }
        });

        // ─── 7. Close mobile sidebar when a leaf nav-link is tapped ──────────
        // (Don't close when tapping a parent with a submenu — let accordion handle it)
        $(document).on('click.mobilenav', '.nav-sidebar .nav-link', function () {
            if (!isMobile()) { return; }
            var $parentItem = $(this).closest('.has-treeview');
            if ($parentItem.length === 0) {
                // It's a leaf link — close the drawer
                closeMobileSidebar();
            }
        });

        // ─── 8. Restore desktop collapsed state from localStorage ─────────────
        try {
            if (!isMobile() && localStorage.getItem('sidebarCollapsed') === '1') {
                $('body').addClass('sidebar-collapse');
            }
        } catch (_) {}

        // ─── 9. Handle window resize ──────────────────────────────────────────
        $(window).on('resize.sidebar', function () {
            if (!isMobile()) {
                // Switched to desktop — clean up mobile state
                $('body').removeClass('sidebar-open');
                $('.sidebar-overlay').remove();
            }
        });

    })(window.jQuery);
}

// Bootstrap: wait for jQuery if it hasn't loaded yet
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
        if (_sbCheckCount > 100) { clearInterval(_sbInterval); }
    }, 50);
}