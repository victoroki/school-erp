@props(['icon', 'text', 'route' => null, 'isActive' => false, 'hasSubmenu' => false, 'isOpen' => false, 'submenuItems' => []])

<li class="nav-item {{ $hasSubmenu ? 'has-treeview' : '' }} {{ $isOpen ? 'menu-open' : '' }}">
    <a href="{{ $route ?? '#' }}" 
       class="nav-link {{ $isActive ? 'active' : '' }} {{ $hasSubmenu ? '' : '' }}">
        <i class="nav-icon {{ $icon }}"></i>
        <p>
            {{ $text }}
            @if($hasSubmenu)
                <i class="right fas fa-angle-left"></i>
            @endif
        </p>
    </a>
    
    @if($hasSubmenu && count($submenuItems) > 0)
        <ul class="nav nav-treeview">
            @foreach($submenuItems as $item)
                <x-sidebar-subitem 
                    :icon="$item['icon']" 
                    :text="$item['text']" 
                    :route="$item['route']" 
                    :isActive="$item['isActive'] ?? false" />
            @endforeach
        </ul>
    @endif
</li>