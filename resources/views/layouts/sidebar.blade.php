<aside class="app-sidebar" id="app-sidebar" aria-label="Main navigation">
    <div class="app-brand">
        <img class="app-brand-mark" src="{{ asset('garikon-school.svg') }}" alt="" width="28" height="28">
        <div class="app-brand-text">
            <span class="app-brand-name">{{ config('app.name') }}</span>
            <span class="app-brand-sub">School management</span>
        </div>
    </div>

    <div class="app-search">
        <i class="fas fa-search" aria-hidden="true"></i>
        <input class="app-search-input"
               type="search"
               placeholder="Find a page"
               aria-label="Find a page (press / to focus)"
               aria-keyshortcuts="/ Control+K"
               autocomplete="off"
               spellcheck="false">
        <kbd class="app-search-kbd" aria-hidden="true">/</kbd>
    </div>

    <nav class="app-nav-scroll">
        <ul class="app-nav">
            @include('layouts.menu-tooltip-fix')
        </ul>
    </nav>
</aside>
