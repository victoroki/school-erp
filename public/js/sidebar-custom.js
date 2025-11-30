/**
 * Enhanced Sidebar JavaScript
 * Handles sidebar collapse, search, tooltips, and state persistence
 */

(function() {
    'use strict';

    const SidebarManager = {
        // Configuration
        config: {
            storageKey: 'sidebar_collapsed',
            collapsedClass: 'sidebar-collapse',
            menuOpenClass: 'menu-open',
            activeClass: 'active'
        },

        // Initialize
        init: function() {
            this.loadSidebarState();
            this.setupToggleButton();
            this.setupTooltips();
            this.setupSearch();
            this.highlightActiveMenu();
            this.setupSmoothScroll();
            this.setupTreeviewToggle();
        },

        // Load sidebar state from localStorage
        loadSidebarState: function() {
            const isCollapsed = localStorage.getItem(this.config.storageKey) === 'true';
            if (isCollapsed) {
                document.body.classList.add(this.config.collapsedClass);
            }
        },

        // Save sidebar state to localStorage
        saveSidebarState: function(isCollapsed) {
            localStorage.setItem(this.config.storageKey, isCollapsed.toString());
        },

        // Setup toggle button functionality
        setupToggleButton: function() {
            const self = this;
            const toggleBtn = document.querySelector('.sidebar-toggle-btn');
            
            if (!toggleBtn) {
                console.warn('Sidebar toggle button not found');
                return;
            }

            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const body = document.body;
                const isCollapsing = !body.classList.contains(self.config.collapsedClass);
                
                // Toggle class
                body.classList.toggle(self.config.collapsedClass);
                
                // Save state
                self.saveSidebarState(isCollapsing);
                
                // Close all open menus when collapsing
                if (isCollapsing) {
                    self.closeAllMenus();
                }
            });
        },

        // Setup tooltips with data attributes
        setupTooltips: function() {
            const navLinks = document.querySelectorAll('.nav-sidebar .nav-link');
            
            navLinks.forEach(link => {
                const textElement = link.querySelector('p');
                if (textElement) {
                    const text = textElement.textContent.trim();
                    link.setAttribute('data-tooltip', text);
                    link.setAttribute('title', text); // Fallback for native tooltip
                }
            });
        },

        // Setup search functionality
        setupSearch: function() {
            const searchInput = document.querySelector('.form-control-sidebar');
            
            if (!searchInput) return;

            searchInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase().trim();
                const navItems = document.querySelectorAll('.nav-sidebar > .nav-item');
                
                navItems.forEach(item => {
                    const link = item.querySelector('.nav-link');
                    const text = link ? link.textContent.toLowerCase() : '';
                    const childLinks = item.querySelectorAll('.nav-treeview .nav-link');
                    
                    let hasMatch = text.includes(searchTerm);
                    
                    // Check child items
                    childLinks.forEach(childLink => {
                        const childText = childLink.textContent.toLowerCase();
                        if (childText.includes(searchTerm)) {
                            hasMatch = true;
                        }
                    });
                    
                    // Show/hide item
                    if (searchTerm === '' || hasMatch) {
                        item.style.display = '';
                        // Expand parent if child matches
                        if (hasMatch && searchTerm !== '') {
                            item.classList.add('menu-open');
                        }
                    } else {
                        item.style.display = 'none';
                    }
                });
            });

            // Clear search on escape
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    e.target.value = '';
                    e.target.dispatchEvent(new Event('input'));
                }
            });
        },

        // Highlight active menu based on current URL
        highlightActiveMenu: function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-sidebar .nav-link');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                
                if (href && href !== '#') {
                    // Remove active class first
                    link.classList.remove(this.config.activeClass);
                    
                    // Check if current path matches
                    if (currentPath === href || currentPath.startsWith(href + '/')) {
                        link.classList.add(this.config.activeClass);
                        
                        // Open parent menu if this is a child item
                        const parentTreeview = link.closest('.nav-treeview');
                        if (parentTreeview) {
                            const parentItem = parentTreeview.closest('.nav-item');
                            if (parentItem) {
                                parentItem.classList.add(this.config.menuOpenClass);
                            }
                        }
                    }
                }
            });
        },

        // Setup smooth scrolling for sidebar
        setupSmoothScroll: function() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.style.scrollBehavior = 'smooth';
            }
        },

        // Setup treeview toggle functionality
        setupTreeviewToggle: function() {
            const self = this;
            const treeviewLinks = document.querySelectorAll('.nav-sidebar > .nav-item.has-treeview > .nav-link');
            
            treeviewLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Only prevent default if sidebar is not collapsed
                    if (!document.body.classList.contains(self.config.collapsedClass)) {
                        e.preventDefault();
                        
                        const parentItem = this.closest('.nav-item');
                        const isOpen = parentItem.classList.contains(self.config.menuOpenClass);
                        
                        // Close other menus (accordion behavior)
                        const allTreeviewItems = document.querySelectorAll('.nav-sidebar > .nav-item.has-treeview');
                        allTreeviewItems.forEach(item => {
                            if (item !== parentItem) {
                                item.classList.remove(self.config.menuOpenClass);
                            }
                        });
                        
                        // Toggle current menu
                        parentItem.classList.toggle(self.config.menuOpenClass);
                        
                        // Scroll into view if needed
                        if (!isOpen) {
                            setTimeout(() => {
                                const treeview = parentItem.querySelector('.nav-treeview');
                                if (treeview) {
                                    treeview.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                }
                            }, 300);
                        }
                    }
                });
            });
        },

        // Close all open menus
        closeAllMenus: function() {
            const openMenus = document.querySelectorAll('.nav-sidebar .menu-open');
            openMenus.forEach(menu => {
                menu.classList.remove(this.config.menuOpenClass);
            });
        },

        // Handle mobile sidebar
        handleMobile: function() {
            if (window.innerWidth <= 991) {
                // Close sidebar overlay when clicking outside
                document.addEventListener('click', function(e) {
                    const sidebar = document.querySelector('.main-sidebar');
                    const toggleBtn = document.querySelector('.sidebar-toggle-btn');
                    
                    if (sidebar && !sidebar.contains(e.target) && e.target !== toggleBtn) {
                        document.body.classList.remove('sidebar-open');
                    }
                });
            }
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            SidebarManager.init();
            SidebarManager.handleMobile();
        });
    } else {
        SidebarManager.init();
        SidebarManager.handleMobile();
    }

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            SidebarManager.handleMobile();
        }, 250);
    });

    // Export for global access if needed
    window.SidebarManager = SidebarManager;

})();

// Additional utility functions

/**
 * Scroll to a specific menu item
 */
function scrollToMenuItem(menuSelector) {
    const menuItem = document.querySelector(menuSelector);
    if (menuItem) {
        menuItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Add highlight effect
        menuItem.style.transition = 'background-color 0.3s ease';
        menuItem.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
        
        setTimeout(() => {
            menuItem.style.backgroundColor = '';
        }, 1000);
    }
}

/**
 * Programmatically expand a menu
 */
function expandMenu(menuSelector) {
    const menuItem = document.querySelector(menuSelector);
    if (menuItem) {
        menuItem.classList.add('menu-open');
    }
}

/**
 * Programmatically collapse a menu
 */
function collapseMenu(menuSelector) {
    const menuItem = document.querySelector(menuSelector);
    if (menuItem) {
        menuItem.classList.remove('menu-open');
    }
}

/**
 * Get sidebar state
 */
function isSidebarCollapsed() {
    return document.body.classList.contains('sidebar-collapse');
}

/**
 * Toggle sidebar programmatically
 */
function toggleSidebar() {
    const toggleBtn = document.querySelector('.sidebar-toggle-btn');
    if (toggleBtn) {
        toggleBtn.click();
    }
}
