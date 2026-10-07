<x-laravel-ui-adminlte::adminlte-layout>
@push('page_css')
    <link rel="stylesheet" href="{{ asset('css/sidebar-fixed-final.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
@endpush

    <body class="hold-transition sidebar-mini">
        <div class="wrapper">
            <!-- Main Header -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <!-- Left navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                            <i class="fas fa-bars"></i>
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ asset('garikon-black.png') }}"
                                class="user-image img-circle elevation-2" alt="Garikon User">
                            @auth
                            <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                            @endauth
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <!-- User image -->
                            <li class="user-header bg-primary">
                                <img src="{{ asset('garikon-white.png') }}"
                                    class="img-circle elevation-2" alt="Garikon User">
                                <p>
                                    @auth
                                    {{ Auth::user()->name }}
                                    <small>Member since {{ Auth::user()->created_at->format('M. Y') }}</small>
                                    @endauth
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <a href="#" class="btn btn-default btn-flat">Profile</a>
                                <a href="#" class="btn btn-default btn-flat float-right"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Sign out
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <!-- Left side column. contains the logo and sidebar -->
            @include('layouts.sidebar')

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                @yield('content')
            </div>

            <!-- Main Footer -->
            <footer class="main-footer">
                <span class="footer-copy">&copy; 2025&ndash;2027 <a href="#">{{ config('app.name') }}</a>. All rights reserved.</span>
                <span class="footer-version"><b>Version</b> 1.0.0</span>
            </footer>
        </div>

        <!-- AdminLTE and Sidebar JavaScript - MUST be loaded after jQuery -->
        @push('page_scripts')
        {{-- Load Select2 JS with defer so it waits for jQuery in app.js --}}
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>

        {{-- AdminLTE initialization with polling strategy to ensure jQuery is ready --}}
        <script>
            // Wait for jQuery and AdminLTE to be fully loaded before initializing widgets
            function initializeAdminLTE() {
                if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.PushMenu !== 'undefined') {
                    // Initialize AdminLTE widgets including pushmenu (sidebar collapse)
                    window.jQuery('[data-widget="pushmenu"]').PushMenu();
                    window.jQuery('[data-widget="treeview"]').Treeview();
                } else {
                    // Poll every 50ms until dependencies are ready
                    setTimeout(initializeAdminLTE, 50);
                }
            }

            // Searchable selects.
            //
            // `page_scripts` is rendered straight after the Vite app.js bundle,
            // but that bundle is an ES module, so it only executes once the document
            // has finished parsing. This inline script runs *during* parsing, which
            // means `window.jQuery` does not exist yet and any inline
            // `$('.select2').select2()` call is dead code. Initialising here, after
            // DOMContentLoaded and only once jQuery *and* select2 are both present,
            // makes every `.select2` field in the ERP searchable.
            function initializeSelect2(attempt) {
                if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
                    // Give up quietly rather than poll forever if the CDN is blocked.
                    if (attempt < 100) {
                        setTimeout(function () { initializeSelect2(attempt + 1); }, 50);
                    }
                    return;
                }

                var $ = window.jQuery;

                // `:not([data-select2])` keeps a view from initialising the same
                // field twice when it brings its own options.
                $('.select2:not([data-select2])').each(function () {
                    $(this).select2({ theme: 'bootstrap4', width: '100%' });
                });

                // Views that fill a Select2 after load (a cascading dropdown, say)
                // have to wait for the widget to exist before they can refresh it.
                // One event beats every view re-initialising the field itself.
                $(document).trigger('select2:ready');

                $('[data-toggle="tooltip"]').tooltip({ container: 'body' });
            }

            // Start initialization after DOM is ready
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(initializeAdminLTE, 100);
                initializeSelect2(0);
            });
        </script>
        @endpush
    </body>
</x-laravel-ui-adminlte::adminlte-layout>