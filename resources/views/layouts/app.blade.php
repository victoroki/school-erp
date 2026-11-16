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
    <meta name="theme-color" content="#1b1d2b">
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
    <script src="{{ asset('build/vendor/jquery.min.js') }}?v=3.7.1"></script>
</head>

<body class="hold-transition sidebar-mini">
    <a class="sr-only sr-only-focusable" href="#main-content">Skip to content</a>
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light" aria-label="Top bar">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" data-enable-remember="true" href="#" role="button" aria-label="Toggle navigation">
                        <i class="fas fa-bars" aria-hidden="true"></i>
                    </a>
                </li>
            </ul>

            @auth
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Account menu">
                        <span class="header-avatar" aria-hidden="true">{{ $initials }}</span>
                        <span class="header-user-name d-none d-md-inline">{{ $authUser->name }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right header-user-menu">
                        <div class="menu-identity">
                            <strong>{{ $authUser->name }}</strong>
                            <span>{{ $authUser->email }}</span>
                            @if($authRole)
                                <span class="badge badge-primary role-chip">{{ $authRole }}</span>
                            @endif
                        </div>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt" aria-hidden="true"></i>Sign out
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
            @endauth
        </nav>

        @include('layouts.sidebar')

        <main class="content-wrapper" id="main-content" tabindex="-1">
            @yield('content')
        </main>

        <footer class="main-footer">
            <span class="footer-copy">&copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.</span>
            <span class="footer-version"><b>Version</b> 1.0.0</span>
        </footer>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    {{-- Classic (blocking) scripts on purpose: they must exist before the
         inline page scripts below run. See vite.config.js. --}}
    <script src="{{ asset('build/vendor/bootstrap.bundle.min.js') }}?v=4.6.2"></script>
    <script src="{{ asset('build/vendor/adminlte.min.js') }}?v=3.1.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/app-shell.js') }}?v={{ filemtime(public_path('js/app-shell.js')) }}" defer></script>
    @stack('third_party_scripts')
    @stack('page_scripts')
</body>
</html>
