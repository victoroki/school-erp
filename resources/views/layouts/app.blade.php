@php
    /*
     * Tab title. A view can set @section('title'); otherwise it is derived
     * from the route name ("fee-management.collect-payment" becomes
     * "Collect Payment · Fee Management"), so every tab and history entry
     * says where it is instead of all reading "Garikon School".
     */
    $pageTitle = trim($__env->yieldContent('title'));
    if ($pageTitle === '' && ($routeName = request()->route()?->getName())) {
        $parts = array_values(array_filter(explode('.', $routeName)));
        $humanize = fn ($s) => \Illuminate\Support\Str::of($s)->snake()->replace(['-', '_'], ' ')->squish()->title()->toString();
        $action = count($parts) > 1 ? array_pop($parts) : null;
        $section = $humanize(implode(' ', $parts ?: [$action]));
        $pageTitle = match ($action) {
            null, 'index', 'dashboard' => $section,
            'create' => 'New · '.$section,
            'edit' => 'Edit · '.$section,
            'show' => $section.' details',
            default => $humanize($action).' · '.$section,
        };
    }

    $authUser = Auth::user();
    $authRole = $authUser?->roles->first()?->role_name;
    $initials = $authUser
        ? collect(preg_split('/\s+/', trim($authUser->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('')
        : '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#ffffff">
    <title>{{ $pageTitle ? $pageTitle.' | ' : '' }}{{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css"
          integrity="sha512-1PKOgIY59xJ8Co8+NE6FZ+LOAZKjy+KY8iq0G4B3CyeY6wYHN3yt9PW0XpSriVlkMXe40PTKnXrLnZ9+fkDaog=="
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">

    @vite('resources/sass/app.scss')
    <link rel="stylesheet" href="{{ asset('css/sidebar-fixed-final.css') }}?v={{ filemtime(public_path('css/sidebar-fixed-final.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/app-theme.css') }}?v={{ filemtime(public_path('css/app-theme.css')) }}">

    @stack('third_party_stylesheets')
    @stack('page_css')

    {{-- jQuery is a blocking head script on purpose: some views run inline
         `$(...)` from inside their content, before the end of <body>. Their
         `$(fn)` / `.ready()` callbacks queue until DOMContentLoaded, by which
         time Bootstrap and AdminLTE below have loaded. See vite.config.js. --}}
    <script src="{{ asset('build/libs/jquery.min.js') }}?v=3.7.1"></script>
</head>

<body class="app-body">
    {{-- Restore the collapsed sidebar before first paint, so it never flashes open. --}}
    <script>try { if (localStorage.getItem('sidebar-hidden') === '1') document.body.classList.add('sidebar-hidden'); } catch (e) {}</script>
    <a class="app-skip" href="#main-content">Skip to content</a>

    <div class="app-shell">
        @include('layouts.sidebar')
        <div class="app-backdrop" data-sidebar-close aria-hidden="true"></div>

        <div class="app-main">
            <header class="app-topbar">
                <button type="button" class="app-icon-btn" data-sidebar-toggle aria-controls="app-sidebar" aria-label="Show or hide navigation">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
                <span class="app-topbar-title">{{ $pageTitle ?: config('app.name') }}</span>

                @auth
                <div class="dropdown app-account">
                    <button type="button" class="app-account-btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Account menu for {{ $authUser->name }}">
                        <span class="app-avatar" aria-hidden="true">{{ $initials }}</span>
                        <span class="app-account-text d-none d-md-flex">
                            <span class="app-account-name">{{ $authUser->name }}</span>
                            @if($authRole)<span class="app-account-role">{{ $authRole }}</span>@endif
                        </span>
                        <i class="fas fa-chevron-down app-account-caret d-none d-md-inline" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right app-account-menu">
                        <div class="app-account-identity">
                            <strong>{{ $authUser->name }}</strong>
                            <span>{{ $authUser->email }}</span>
                        </div>
                        <div class="dropdown-divider"></div>
                        <button type="submit" form="logout-form" class="dropdown-item">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>Sign out
                        </button>
                    </div>
                </div>
                @endauth
            </header>

            <main class="content-wrapper" id="main-content" tabindex="-1">
                @yield('content')
            </main>

            <footer class="main-footer app-footer">
                <span>&copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.</span>
                <span>Version 1.0.0</span>
            </footer>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    {{-- Classic (blocking) scripts on purpose: they must exist before the
         inline page scripts below run. See vite.config.js. --}}
    <script src="{{ asset('build/libs/bootstrap.bundle.min.js') }}?v=4.6.2"></script>
    <script src="{{ asset('build/libs/adminlte.min.js') }}?v=3.1.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/app-shell.js') }}?v={{ filemtime(public_path('js/app-shell.js')) }}" defer></script>
    @stack('third_party_scripts')
    @stack('page_scripts')
</body>
</html>
