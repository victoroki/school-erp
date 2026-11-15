<aside class="main-sidebar" aria-label="Primary sidebar">
    {{-- ── Brand ── --}}
    <div class="brand-container">
        <div class="brand-logo-mark">
            <img src="{{ asset('garikon-school.svg') }}" alt="Garikon School logo">
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
                aria-label="Search navigation (press / to focus)"
                aria-keyshortcuts="/ Control+K"
                autocomplete="off"
                spellcheck="false"
            >
            <kbd class="search-kbd" aria-hidden="true">/</kbd>
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
            // The column is role_name; reading ->name always fell back, so
            // every account (Owner, Bursar, Teacher) was labelled "Administrator".
            $roleName = Auth::user()->roles->first()?->role_name ?? 'Staff';
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
        <button type="submit"
                form="logout-form"
                class="logout-action border-0"
                aria-label="Sign out"
                title="Sign out">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
        </button>
    </div>
</aside>