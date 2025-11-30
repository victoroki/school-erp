@props(['icon', 'text', 'route', 'isActive' => false])

<li class="nav-item">
    <a href="{{ $route }}" class="nav-link {{ $isActive ? 'active' : '' }}">
        <i class="nav-icon {{ $icon }}"></i>
        <p>{{ $text }}</p>
    </a>
</li>