<aside class="main-sidebar" aria-label="Primary sidebar">
    {{-- ── Brand ── --}}
    <div class="brand-container">
        <div class="brand-logo-mark">
            <img src="{{ asset('garikon-white.png') }}" alt="Garikon logo">
        </div>
        <div class="brand-info">
            <span class="brand-title">Garikon School</span>
            <span class="brand-subtitle">Academic Portal</span>
        </div>
    </div>

    {{-- ── Search ── --}}
    <div class="sidebar-search-container">
        <div class="search-wrapper">
            <i class="fas fa-search" aria-hidden="true"></i>
            <input
                class="search-input"
                type="search"
                placeholder="Quick find…"
                aria-label="Search navigation"
                autocomplete="off"
                spellcheck="false"
            >
        </div>
    </div>

    {{-- ── Navigation ── --}}
    <div class="sidebar">
        <nav aria-label="Main navigation">
            <ul class="nav nav-pills nav-sidebar flex-column"
                data-widget="treeview"
                role="menu"
                data-accordion="false">
                @include('layouts.menu-tooltip-fix')
            </ul>
        </nav>
    </div>

    {{-- ── User Panel ── --}}
    <div class="sidebar-user-panel">
        @auth
        @php
            $userName   = Auth::user()->name;
            $initials   = strtoupper(substr($userName, 0, 1));
            $secondWord = explode(' ', $userName);
            if (count($secondWord) > 1) {
                $initials .= strtoupper(substr($secondWord[1], 0, 1));
            }
            $roleName = Auth::user()->roles->first()?->name ?? 'Administrator';
        @endphp
        <div class="user-avatar-container">
            <div class="user-initials" aria-hidden="true">{{ $initials }}</div>
            <span class="user-online-status" aria-hidden="true"></span>
        </div>
        <div class="user-text-info">
            <span class="user-display-name">{{ $userName }}</span>
            <span class="user-display-role">{{ $roleName }}</span>
        </div>
        @endauth
        <a href="#"
           class="logout-action"
           aria-label="Sign out"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
        </a>
    </div>
</aside>