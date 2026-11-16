{{--
    Sidebar navigation, rendered from config/menu.php through MenuService
    (permission- and module-filtered). Groups are real <button>s with
    aria-expanded; public/js/app-shell.js opens and closes them. No AdminLTE
    treeview: it is not loaded for the menu any more, so nothing else can
    intercept the clicks.
--}}
@php
    $menu = app(\App\Services\MenuService::class)->getVisibleMenu();
@endphp

@foreach ($menu as $item)
    @if (isset($item['header']))
        <li class="app-nav-header">{{ $item['header'] }}</li>
        @continue
    @endif

    @if (!empty($item['children']))
        @php
            $isOpen = \App\Services\MenuService::isActiveAny((array) $item['active']);
            $groupId = 'nav-group-'.($item['key'] ?? $loop->index);
        @endphp
        <li class="app-nav-group {{ $isOpen ? 'is-open' : '' }}">
            <button type="button"
                    class="app-nav-link app-nav-toggle {{ $isOpen ? 'is-current' : '' }}"
                    aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                    aria-controls="{{ $groupId }}">
                <i class="app-nav-icon {{ $item['icon'] }}" aria-hidden="true"></i>
                <span class="app-nav-label">{{ $item['label'] }}</span>
                <i class="app-nav-chevron fas fa-chevron-right" aria-hidden="true"></i>
            </button>
            <ul class="app-nav-sub" id="{{ $groupId }}">
                @foreach ($item['children'] as $child)
                    @if (isset($child['header']))
                        <li class="app-nav-subheader">{{ $child['header'] }}</li>
                        @continue
                    @endif
                    @php
                        $isChildActive = \App\Services\MenuService::isActiveAny((array) $child['active']);
                    @endphp
                    <li>
                        <a href="{{ route($child['route']) }}"
                           class="app-nav-link app-nav-sublink {{ $isChildActive ? 'is-active' : '' }}"
                           @if ($isChildActive) aria-current="page" @endif>
                            <span class="app-nav-label">{{ $child['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </li>
    @else
        @php
            $isLeafActive = \App\Services\MenuService::isActiveAny((array) $item['active']);
        @endphp
        <li>
            <a href="{{ route($item['route']) }}"
               class="app-nav-link {{ $isLeafActive ? 'is-active' : '' }}"
               @if ($isLeafActive) aria-current="page" @endif>
                <i class="app-nav-icon {{ $item['icon'] }}" aria-hidden="true"></i>
                <span class="app-nav-label">{{ $item['label'] }}</span>
            </a>
        </li>
    @endif
@endforeach
