@php
    $menuService = app(\App\Services\MenuService::class);
    $menu = $menuService->getVisibleMenu();
@endphp

@foreach ($menu as $item)
    {{-- Section header --}}
    @if (isset($item['header']))
        <li class="nav-header">{{ $item['header'] }}</li>
        @continue
    @endif

    {{-- Parent item with children --}}
    @if (!empty($item['children']))
        @php
            $isOpen = \App\Services\MenuService::isActiveAny((array) $item['active']);
        @endphp
        <li class="nav-item has-treeview {{ $isOpen ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ $isOpen ? 'active' : '' }}" data-tooltip="{{ $item['label'] }}">
                <i class="nav-icon {{ $item['icon'] }} {{ $item['color'] ?? '' }}"></i>
                <p>
                    {{ $item['label'] }}
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                @foreach ($item['children'] as $child)
                    {{-- Sub-section header --}}
                    @if (isset($child['header']))
                        <li class="nav-header small text-uppercase {{ $child['color'] ?? '' }}">{{ $child['header'] }}</li>
                        @continue
                    @endif

                    @php
                        $isChildActive = \App\Services\MenuService::isActiveAny((array) $child['active']);
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route($child['route']) }}" class="nav-link {{ $isChildActive ? 'active' : '' }}" data-tooltip="{{ $child['label'] }}">
                            <i class="{{ $child['icon'] }} nav-icon {{ $child['color'] ?? '' }}"></i>
                            <p>{{ $child['label'] }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        </li>

    {{-- Leaf item (no children) --}}
    @else
        @php
            $isLeafActive = \App\Services\MenuService::isActiveAny((array) $item['active']);
        @endphp
        <li class="nav-item">
            <a href="{{ route($item['route']) }}" class="nav-link {{ $isLeafActive ? 'active' : '' }}" data-tooltip="{{ $item['label'] }}">
                <i class="nav-icon {{ $item['icon'] }} {{ $item['color'] ?? '' }}"></i>
                <p>{{ $item['label'] }}</p>
            </a>
        </li>
    @endif
@endforeach
