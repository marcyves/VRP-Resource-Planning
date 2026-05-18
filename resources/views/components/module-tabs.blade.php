@props(['tabs'])

<nav class="module-tabs" aria-label="{{ __('messages.module_navigation') }}">
    @foreach($tabs as $tab)
        <a href="{{ $tab['href'] }}" class="module-tab {{ ($tab['active'] ?? false) ? 'active' : '' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
