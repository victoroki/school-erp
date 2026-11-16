@php
    $portalUser = Auth::user();
    $portalInitials = $portalUser
        ? collect(preg_split('/\s+/', trim($portalUser->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('')
        : '';
    $portalRole = $portalUser?->roles->first()?->role_name;
    $tabs = [
        ['portal.fees', 'Fees', 'fas fa-receipt', 'portal/fees*'],
        ['portal.report-cards', 'Report cards', 'fas fa-book-open', 'portal/report-cards*'],
        ['portal.attendance', 'Attendance', 'fas fa-calendar-check', 'portal/attendance*'],
        ['portal.profile', 'Profile', 'fas fa-user', 'portal/profile*'],
    ];
@endphp
<x-laravel-ui-adminlte::adminlte-layout>
    @push('third_party_stylesheets')
        <link rel="stylesheet" href="{{ asset('css/sidebar-fixed-final.css') }}?v={{ filemtime(public_path('css/sidebar-fixed-final.css')) }}">
        <style>
            .portal-top { position: sticky; top: 0; z-index: 1030; background: #fff; border-bottom: 1px solid var(--shell-border); }
            .portal-bar { max-width: 1120px; margin: 0 auto; display: flex; align-items: center; gap: 16px; height: 60px; padding: 0 20px; }
            .portal-brand { display: flex; align-items: center; gap: 10px; color: var(--shell-ink); font-weight: 700; font-size: 15px; text-decoration: none; white-space: nowrap; }
            .portal-brand:hover { color: var(--shell-ink); text-decoration: none; }
            .portal-brand img { width: 28px; height: 28px; border-radius: 7px; }
            .portal-tabs { display: flex; gap: 2px; margin: 0; padding: 0; list-style: none; overflow-x: auto; scrollbar-width: none; }
            .portal-tabs::-webkit-scrollbar { display: none; }
            .portal-tab { display: inline-flex; align-items: center; gap: 8px; height: 60px; padding: 0 12px; color: var(--shell-muted); font-size: 14px; font-weight: 500; border-bottom: 2px solid transparent; text-decoration: none; white-space: nowrap; }
            .portal-tab:hover { color: var(--shell-ink); text-decoration: none; }
            .portal-tab.is-active { color: var(--accent); border-bottom-color: var(--accent); font-weight: 600; }
            .portal-tab i { font-size: 13px; }
            .portal-main { max-width: 1120px; margin: 0 auto; padding: 24px 20px 48px; }
            .portal-main .card { border-radius: 12px; }
            .portal-foot { max-width: 1120px; margin: 0 auto; padding: 16px 20px 28px; font-size: 12px; color: var(--shell-faint); }
            @media (max-width: 767.98px) {
                .portal-bar { height: auto; flex-wrap: wrap; padding: 10px 16px 0; gap: 8px; }
                .portal-tabs { order: 3; width: 100%; }
                .portal-tab { height: 46px; }
                .portal-main { padding: 18px 16px 40px; }
            }
        </style>
    @endpush

    <body class="app-body">
        <a class="app-skip" href="#main-content">Skip to content</a>

        <header class="portal-top">
            <div class="portal-bar">
                <a class="portal-brand" href="{{ route('portal.fees') }}">
                    <img src="{{ asset('garikon-school.svg') }}" alt="">
                    <span>{{ config('app.name') }}</span>
                </a>

                <nav aria-label="Portal">
                    <ul class="portal-tabs">
                        @foreach($tabs as [$route, $label, $icon, $pattern])
                            @if(Route::has($route))
                                @php $active = request()->is($pattern); @endphp
                                <li>
                                    <a href="{{ route($route) }}" class="portal-tab {{ $active ? 'is-active' : '' }}" @if($active) aria-current="page" @endif>
                                        <i class="{{ $icon }}" aria-hidden="true"></i>{{ $label }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </nav>

                @auth
                <div class="dropdown app-account">
                    <button type="button" class="app-account-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Account menu for {{ $portalUser->name }}">
                        <span class="app-avatar" aria-hidden="true">{{ $portalInitials }}</span>
                        <span class="app-account-text d-none d-md-flex">
                            <span class="app-account-name">{{ $portalUser->name }}</span>
                            @if($portalRole)<span class="app-account-role">{{ $portalRole }}</span>@endif
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right app-account-menu">
                        <div class="app-account-identity">
                            <strong>{{ $portalUser->name }}</strong>
                            <span>{{ $portalUser->email }}</span>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('portal.profile') }}"><i class="fas fa-user" aria-hidden="true"></i>Profile</a>
                        <button type="submit" form="logout-form" class="dropdown-item"><i class="fas fa-sign-out-alt" aria-hidden="true"></i>Sign out</button>
                    </div>
                </div>
                @endauth
            </div>
        </header>

        <main class="portal-main" id="main-content" tabindex="-1">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="status">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Dismiss">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Dismiss">&times;</button>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="portal-foot main-footer">&copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.</footer>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
    </body>
</x-laravel-ui-adminlte::adminlte-layout>
