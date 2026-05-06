<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <div class="brand-container">
        <div class="brand-logo-hex">
            <img src="{{ asset('garikon-white.png') }}" alt="Logo" style="width: 32px; height: 32px; object-fit: contain;">
        </div>
        <div class="brand-info">
            <span class="brand-title">Garikon School</span>
            <span class="brand-subtitle">Academic Portal</span>
        </div>
    </div>

    <div class="sidebar-search-container">
        <div class="search-wrapper"> {{-- Removed data-widget to fix crash --}}
            <i class="fas fa-search"></i>
            <input class="search-input" type="search" placeholder="Quick find..." aria-label="Search">
        </div>
    </div>

    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @include('layouts.menu-tooltip-fix')
            </ul>
        </nav>
    </div>

    <div class="sidebar-user-panel">
        <div class="user-avatar-container">
            <img src="{{ asset('garikon-black.png') }}" alt="User Avatar">
            <span class="user-online-status"></span>
        </div>
        <div class="user-text-info">
            @auth
            <span class="user-display-name">{{ Auth::user()->name }}</span>
            <span class="user-display-role">{{ Auth::user()->roles->first()->name ?? 'Administrator' }}</span>
            @endauth
        </div>
        <a href="#" class="logout-action" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
</aside>