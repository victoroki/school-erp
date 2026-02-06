/**
 * SIDEBAR TREEVIEW & RESPONSIVE HANDLER - BULLETPROOF VERSION
 */

// Function to initialize everything once jQuery is available
function initSidebarHandler() {
    (function ($) {
        'use strict';

        console.log('Sidebar Handler: Initializing with jQuery v' + $.fn.jquery);

        // 1. Manual Toggle Logic (Primary fix for non-expanding menus)
        // We use document delegation to be safe
        $(document).off('click', '.has-treeview > a').on('click', '.has-treeview > a', function (e) {
            var $link = $(this);
            var $parent = $link.parent('.has-treeview');
            var $submenu = $parent.find('> .nav-treeview');

            if ($submenu.length > 0) {
                e.preventDefault();
                e.stopPropagation();

                // Accordion behavior: Close other open menus at the same level
                $parent.siblings('.menu-open').each(function () {
                    $(this).removeClass('menu-open');
                    $(this).find('> .nav-treeview').slideUp(250);
                });

                // Toggle current menu
                if ($parent.hasClass('menu-open')) {
                    $submenu.slideUp(250, function () {
                        $parent.removeClass('menu-open');
                    });
                } else {
                    $parent.addClass('menu-is-opening');
                    $submenu.slideDown(250, function () {
                        $parent.addClass('menu-open');
                        $parent.removeClass('menu-is-opening');
                    });
                }
            }
        });

        // 2. Initial state: Open active menus
        $('.nav-sidebar .nav-link.active').parents('.has-treeview').addClass('menu-open').find('> .nav-treeview').show();

        // 3. Burger Menu Logic for Mobile
        $(document).off('click', '[data-widget="pushmenu"]').on('click', '[data-widget="pushmenu"]', function (e) {
            e.preventDefault();
            if ($(window).width() <= 991) {
                $('body').toggleClass('sidebar-open');

                // Manage overlay
                if ($('body').hasClass('sidebar-open')) {
                    if ($('.sidebar-overlay').length === 0) {
                        $('<div class="sidebar-overlay"></div>').appendTo('body');
                    }
                } else {
                    $('.sidebar-overlay').remove();
                }
            } else {
                $('body').toggleClass('sidebar-collapse');
            }
        });

        // Close sidebar on mobile when clicking overlay or a normal link
        $(document).on('click', '.sidebar-overlay', function () {
            $('body').removeClass('sidebar-open');
            $(this).remove();
        });

        $(document).on('click', '.nav-sidebar .nav-link:not(.has-treeview > a)', function () {
            if ($(window).width() <= 991) {
                $('body').removeClass('sidebar-open');
                $('.sidebar-overlay').remove();
            }
        });

        // 4. Force fix for the layout gap on window resize
        $(window).on('resize', function () {
            if ($(window).width() > 991) {
                $('body').removeClass('sidebar-open');
                $('.sidebar-overlay').remove();
            }
        });

    })(window.jQuery);
}

// Check if jQuery is already there, otherwise wait for it
if (window.jQuery) {
    initSidebarHandler();
} else {
    var checkCount = 0;
    var interval = setInterval(function () {
        checkCount++;
        if (window.jQuery) {
            clearInterval(interval);
            initSidebarHandler();
        }
        if (checkCount > 100) clearInterval(interval); // Stop after 5 seconds
    }, 50);
}